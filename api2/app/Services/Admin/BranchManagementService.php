<?php



namespace App\Services\Admin;



use App\Models\Branch;

use App\Models\User;

use App\Services\AuditLogService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;



class BranchManagementService

{

    /** @var list<string> */

    private array $scopedTables = [

        'training_centers',

        'trainers',

        'trainees',

        'training_courses',

        'certificates',

        'trainer_registration_requests',

        'trainee_registration_requests',

        'course_registration_requests',

        'training_center_registration_requests',

        'financial_records',

    ];



    public function __construct(private AuditLogService $auditLog) {}



    public function create(User $actor, array $data, ?Request $request = null): Branch

    {

        $managerUserId = $data['manager_user_id'] ?? null;

        $governorateId = (int) $data['governorate_id'];



        if ($managerUserId) {

            $this->validateManagerForBranch($managerUserId, $governorateId, null);

        }



        $branch = Branch::query()->create([

            'governorate_id' => $governorateId,

            'name' => $data['name'],

            'code' => $data['code'],

            'is_active' => $data['is_active'] ?? true,

            'manager_user_id' => $managerUserId,

            'notes' => $data['notes'] ?? null,

        ]);



        if ($managerUserId) {

            $this->syncManagerUser($branch, (int) $managerUserId, $governorateId);

        }



        $this->auditLog->log(

            'branch_created',

            $actor,

            $branch,

            null,

            $branch->only(['name', 'code', 'governorate_id', 'is_active', 'manager_user_id']),

            null,

            $request,

            'branches',

            'إنشاء فرع جديد'

        );



        return $branch->load(['governorate:id,name_ar,code', 'manager:id,name,email']);

    }



    public function update(User $actor, Branch $branch, array $data, ?Request $request = null): Branch

    {

        $before = $branch->only(['name', 'code', 'governorate_id', 'is_active', 'manager_user_id', 'notes']);

        $governorateId = (int) ($data['governorate_id'] ?? $branch->governorate_id);



        if (array_key_exists('manager_user_id', $data) && $data['manager_user_id']) {

            $this->validateManagerForBranch((int) $data['manager_user_id'], $governorateId, $branch->id);

        }



        $branch->update(array_intersect_key($data, array_flip([

            'name', 'code', 'governorate_id', 'is_active', 'manager_user_id', 'notes',

        ])));



        if (array_key_exists('manager_user_id', $data)) {

            if ($data['manager_user_id']) {

                $this->syncManagerUser($branch->fresh(), (int) $data['manager_user_id'], $governorateId);

            }

        } elseif (array_key_exists('governorate_id', $data) && $branch->manager_user_id) {

            $this->syncManagerUser($branch->fresh(), (int) $branch->manager_user_id, $governorateId);

        }



        $this->auditLog->log(

            'branch_updated',

            $actor,

            $branch,

            $before,

            $branch->only(['name', 'code', 'governorate_id', 'is_active', 'manager_user_id', 'notes']),

            null,

            $request,

            'branches',

            'تعديل بيانات فرع'

        );



        return $branch->fresh()->load(['governorate:id,name_ar,code', 'manager:id,name,email']);

    }



    public function deactivate(User $actor, Branch $branch, ?Request $request = null): Branch

    {

        if (!$branch->is_active) {

            return $branch;

        }



        $before = ['is_active' => true];

        $branch->update(['is_active' => false]);



        $this->auditLog->log(

            'branch_disabled',

            $actor,

            $branch,

            $before,

            ['is_active' => false],

            null,

            $request,

            'branches',

            'تعطيل فرع'

        );



        return $branch->fresh();

    }



    public function activate(User $actor, Branch $branch, ?Request $request = null): Branch

    {

        if ($branch->is_active) {

            return $branch;

        }



        $before = ['is_active' => false];

        $branch->update(['is_active' => true]);



        $this->auditLog->log(

            'branch_enabled',

            $actor,

            $branch,

            $before,

            ['is_active' => true],

            null,

            $request,

            'branches',

            'تفعيل فرع'

        );



        return $branch->fresh();

    }



    public function deleteOrDeactivate(User $actor, Branch $branch, ?Request $request = null): Branch

    {

        if ($this->hasDependentData($branch)) {

            throw ValidationException::withMessages([

                'branch' => ['لا يمكن حذف فرع عليه بيانات. استخدم التعطيل بدلاً من الحذف.'],

            ]);

        }



        $before = $branch->toArray();

        $branch->delete();



        $this->auditLog->log(

            'branch_deleted',

            $actor,

            null,

            $before,

            null,

            ['branch_id' => $before['id'] ?? null],

            $request,

            'branches',

            'حذف فرع'

        );



        return $branch;

    }



    public function hasDependentData(Branch $branch): bool

    {

        if ($branch->users()->exists()) {

            return true;

        }



        foreach ($this->scopedTables as $table) {

            if (!DB::getSchemaBuilder()->hasTable($table) || !DB::getSchemaBuilder()->hasColumn($table, 'branch_id')) {

                continue;

            }



            if (DB::table($table)->where('branch_id', $branch->id)->exists()) {

                return true;

            }

        }



        return false;

    }



    public function validateManagerForBranch(int $managerUserId, int $governorateId, ?int $branchId): User

    {

        $manager = User::query()->find($managerUserId);



        if (!$manager) {

            throw ValidationException::withMessages(['manager_user_id' => ['المدير غير موجود.']]);

        }



        if (!$manager->hasRole('branch_manager')) {

            throw ValidationException::withMessages(['manager_user_id' => ['يجب اختيار مستخدم بدور مدير فرع.']]);

        }



        if (!$manager->is_active) {

            throw ValidationException::withMessages(['manager_user_id' => ['مدير الفرع يجب أن يكون مستخدماً فعالاً.']]);

        }



        if ($manager->governorate_id && (int) $manager->governorate_id !== $governorateId) {

            throw ValidationException::withMessages(['manager_user_id' => ['محافظة المدير لا تطابق محافظة الفرع.']]);

        }



        if ($manager->branch_id && $branchId && (int) $manager->branch_id !== $branchId) {

            // السماح بإعادة التعيين على فرع جديد

        }



        return $manager;

    }



    public function syncManagerUser(Branch $branch, int $managerUserId, int $governorateId): void

    {

        $manager = $this->validateManagerForBranch($managerUserId, $governorateId, $branch->id);



        $manager->update([

            'branch_id' => $branch->id,

            'governorate_id' => $governorateId,

        ]);



        if ((int) $branch->manager_user_id !== $managerUserId) {

            $branch->update(['manager_user_id' => $managerUserId]);

        }

    }

}

