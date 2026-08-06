<?php

namespace App\Services\Needs;

use App\Models\Branch;
use App\Models\Governorate;
use App\Models\Need;
use App\Models\NeedActionLog;
use App\Models\NeedSector;
use App\Models\User;
use App\Support\NeedTaxonomy;
use App\Services\StatusHistoryService;
use App\Support\StatusTransitionValidator;
use App\Support\NeedStatus;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NeedWorkflowService
{
    public function __construct(
        private NeedCodeGenerator $codeGenerator,
        private AuditLogService $auditLog,
        private StatusTransitionValidator $statusValidator,
        private StatusHistoryService $statusHistory,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, ?Request $request = null): Need
    {
        $this->assertOwnerTypeAllowed($user, $data);

        $scope = $this->resolveScope($user, $data);
        $sectorCodes = $this->pullSectorCodes($data);
        $data = $this->normalizeTaxonomy($data, $sectorCodes);

        $need = DB::transaction(function () use ($data, $scope, $user, $request, $sectorCodes): Need {
            $newNeed = Need::query()->create(array_merge($data, [
                'need_code' => $this->codeGenerator->next(),
                'governorate_id' => $scope['governorate_id'],
                'branch_id' => $scope['branch_id'],
                'status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
                'approval_status' => NeedStatus::PENDING_GOVERNORATE_REVIEW,
                'source_platform' => $data['source_platform'] ?? 'gis',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'is_mapped' => !empty($data['latitude']) && !empty($data['longitude']),
            ]));

            $this->syncSectors($newNeed, $sectorCodes);

            $this->statusHistory->record($newNeed, null, (string) $newNeed->status, (int) $user->id);
            $this->logAction($newNeed, 'need_created', null, (string) $newNeed->status, $user, null);
            $this->auditLog->log('need_created', $user, $newNeed, null, $newNeed->toArray(), null, $request, 'needs', 'إنشاء احتياج');

            return $newNeed;
        });

        return $need->fresh(['governorate', 'branch']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Need $need, User $user, array $data, ?Request $request = null): Need
    {
        if (!NeedStatus::isEditable($need->status) && !$user->hasPermissionTo('needs.update')) {
            throw ValidationException::withMessages(['status' => ['لا يمكن تعديل الاحتياج في حالته الحالية.']]);
        }

        $sectorCodes = $this->pullSectorCodes($data);
        $data = $this->normalizeTaxonomy($data, $sectorCodes);

        $before = $need->toArray();
        $need->update(array_merge(
            collect($data)->except(['need_code', 'created_by'])->filter(fn ($v) => $v !== null)->all(),
            ['updated_by' => $user->id, 'is_mapped' => !empty($data['latitude'] ?? $need->latitude) && !empty($data['longitude'] ?? $need->longitude)]
        ));

        // اختيار نوع منشأة غير الحاضنة يلغي النوع الفرعي المخزّن سابقاً
        if (($data['facility_type'] ?? null) && $data['facility_type'] !== 'business_incubator' && $need->facility_subtype) {
            $need->forceFill(['facility_subtype' => null])->save();
        }

        if ($sectorCodes !== null) {
            $this->syncSectors($need, $sectorCodes);
        }

        $this->auditLog->log('need_updated', $user, $need, $before, $need->fresh()->toArray(), null, $request, 'needs', 'تحديث احتياج');

        return $need->fresh(['governorate', 'branch']);
    }

    public function review(Need $need, User $user, ?string $note, ?Request $request = null): Need
    {
        $this->transition($need, $user, NeedStatus::PENDING_BRANCH_APPROVAL, 'need_reviewed', [
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'reviewer_note' => $note,
            'returned_by' => null,
            'returned_at' => null,
            'return_reason' => null,
        ], $note, $request);

        return $need->fresh();
    }

    public function returnForEdit(Need $need, User $user, string $reason, ?Request $request = null): Need
    {
        if ($reason === '') {
            throw ValidationException::withMessages(['return_reason' => ['سبب الإعادة مطلوب.']]);
        }

        $this->transition($need, $user, NeedStatus::RETURNED_FOR_EDIT, 'need_returned', [
            'returned_by' => $user->id,
            'returned_at' => now(),
            'return_reason' => $reason,
        ], $reason, $request);

        return $need->fresh();
    }

    public function approve(Need $need, User $user, ?string $note, ?Request $request = null): Need
    {
        $this->transition($need, $user, NeedStatus::APPROVED, 'need_approved', [
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_note' => $note,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ], $note, $request);

        return $need->fresh();
    }

    public function reject(Need $need, User $user, string $reason, ?Request $request = null): Need
    {
        if ($reason === '') {
            throw ValidationException::withMessages(['rejection_reason' => ['سبب الرفض مطلوب.']]);
        }

        $this->transition($need, $user, NeedStatus::REJECTED, 'need_rejected', [
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ], $reason, $request);

        return $need->fresh();
    }

    public function classify(Need $need, User $user, string $intervention, ?string $note, ?Request $request = null): Need
    {
        $allowed = ['دراسة', 'تدريب', 'استشارات', 'تمويل', 'حاضنة', 'بنية تحتية'];
        if (!in_array($intervention, $allowed, true)) {
            throw ValidationException::withMessages(['proposed_intervention' => ['التدخل المقترح غير صالح.']]);
        }

        if (!in_array($need->status, [NeedStatus::APPROVED, NeedStatus::CLASSIFIED], true)) {
            throw ValidationException::withMessages(['status' => ['لا يمكن التصنيف قبل موافقة مدير الفرع.']]);
        }

        $this->transition($need, $user, NeedStatus::CLASSIFIED, 'need_classified', [
            'classified_by' => $user->id,
            'classified_at' => now(),
            'proposed_intervention' => $intervention,
            'classification_note' => $note,
        ], $note, $request);

        return $need->fresh();
    }

    public function resolve(Need $need, User $user, ?string $note, ?Request $request = null): Need
    {
        $this->transition($need, $user, NeedStatus::RESOLVED, 'need_resolved', [
            'resolved_by' => $user->id,
            'resolved_at' => now(),
        ], $note, $request);

        return $need->fresh();
    }

    /**
     * يسحب أكواد القطاعات من الحمولة (ليست عموداً في جدول needs).
     *
     * @param  array<string, mixed>  $data
     * @return list<string>|null null = لم تُرسل القطاعات إطلاقاً
     */
    private function pullSectorCodes(array &$data): ?array
    {
        if (!array_key_exists('sectors', $data)) {
            return null;
        }

        $codes = array_values(array_unique(array_filter((array) $data['sectors'])));
        unset($data['sectors']);

        return $codes;
    }

    /**
     * قواعد اتساق التصنيف + تعبئة حقل sector النصي القديم للتوافق مع
     * الخريطة والتقارير القديمة (دون المساس بقيم sector المرسلة صراحة).
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $sectorCodes
     * @return array<string, mixed>
     */
    private function normalizeTaxonomy(array $data, ?array $sectorCodes): array
    {
        if (($data['facility_type'] ?? null) && $data['facility_type'] !== 'business_incubator') {
            $data['facility_subtype'] = null;
        }

        if (!empty($sectorCodes) && empty($data['sector'])) {
            $primary = in_array('all', $sectorCodes, true) ? 'all' : $sectorCodes[0];
            $data['sector'] = NeedTaxonomy::sectorLabel($primary);
        }

        return $data;
    }

    /**
     * @param  list<string>|null  $sectorCodes
     */
    private function syncSectors(Need $need, ?array $sectorCodes): void
    {
        if ($sectorCodes === null) {
            return;
        }

        $ids = NeedSector::query()->whereIn('code', $sectorCodes)->pluck('id')->all();
        $need->sectors()->sync($ids);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{governorate_id: int, branch_id: int}
     */
    private function resolveScope(User $user, array $data): array
    {
        if (!empty($data['branch_id']) && !empty($data['governorate_id'])) {
            $branch = Branch::query()->findOrFail((int) $data['branch_id']);
            if ((int) $branch->governorate_id !== (int) $data['governorate_id']) {
                throw ValidationException::withMessages(['branch_id' => ['الفرع لا يتبع المحافظة.']]);
            }

            return ['governorate_id' => (int) $data['governorate_id'], 'branch_id' => (int) $data['branch_id']];
        }

        if ($user->branch_id && $user->governorate_id) {
            return ['governorate_id' => (int) $user->governorate_id, 'branch_id' => (int) $user->branch_id];
        }

        throw ValidationException::withMessages(['governorate_id' => ['يجب تحديد المحافظة والفرع.']]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertOwnerTypeAllowed(User $user, array $data): void
    {
        $ownerType = $data['need_owner_type'] ?? 'citizen';

        if ($ownerType === 'state' && !$user->hasPermissionTo('needs.create_state')
            && !($user->hasRole('branch_manager') || \App\Support\AccessControlGuard::isNationalAdministrator($user))) {
            throw ValidationException::withMessages(['need_owner_type' => ['لا تملك صلاحية إنشاء احتياج دولة.']]);
        }

        if ($ownerType === 'state' && ($data['need_scope'] ?? '') === 'national'
            && !$user->hasPermissionTo('needs.view_all')
            && !\App\Support\AccessControlGuard::isNationalAdministrator($user)) {
            throw ValidationException::withMessages(['need_scope' => ['لا تملك صلاحية إنشاء احتياج وطني.']]);
        }
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function transition(Need $need, User $user, string $toStatus, string $action, array $fields, ?string $note, ?Request $request): void
    {
        DB::transaction(function () use ($need, $user, $toStatus, $action, $fields, $note, $request) {
            $lockedNeed = Need::query()
                ->whereKey($need->id)
                ->lockForUpdate()
                ->firstOrFail();

            $from = (string) $lockedNeed->status;
            $this->statusValidator->assertAllowed(Need::class, $from, $toStatus);

            $lockedNeed->update(array_merge($fields, [
                'status' => $toStatus,
                'approval_status' => $toStatus,
                'updated_by' => $user->id,
            ]));

            $this->statusHistory->record($lockedNeed, $from, $toStatus, (int) $user->id, $note);
            $this->logAction($lockedNeed, $action, $from, $toStatus, $user, $note);
            $this->auditLog->log($action, $user, $lockedNeed, ['status' => $from], ['status' => $toStatus], ['note' => $note], $request, 'needs', NeedStatus::label($toStatus));

            $need->setRawAttributes($lockedNeed->getAttributes(), true);
        });
    }

    private function logAction(Need $need, string $action, ?string $from, string $to, User $user, ?string $note): void
    {
        NeedActionLog::query()->create([
            'need_id' => $need->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'performed_by' => $user->id,
            'note' => $note,
        ]);
    }

    public static function resolveGovernorateIdByArabicName(?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return Governorate::query()->where('name_ar', $name)->value('id');
    }

    public static function resolveBranchIdForGovernorate(int $governorateId): ?int
    {
        return Branch::query()
            ->where('governorate_id', $governorateId)
            ->where('is_active', true)
            ->value('id')
            ?? Branch::query()->where('governorate_id', $governorateId)->value('id');
    }
}
