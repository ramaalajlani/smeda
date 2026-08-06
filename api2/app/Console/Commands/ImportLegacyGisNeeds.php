<?php



namespace App\Console\Commands;



use App\Models\Need;

use App\Models\User;

use App\Services\Needs\NeedCodeGenerator;

use App\Services\Needs\NeedWorkflowService;

use App\Support\NeedStatus;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;



class ImportLegacyGisNeeds extends Command

{

    protected $signature = 'needs:import-legacy-gis';



    protected $description = 'Import needs from legacy gis_survey_responses table';



    public function handle(NeedCodeGenerator $codeGenerator): int

    {

        if (!Schema::hasTable('gis_survey_responses')) {

            $this->error('جدول gis_survey_responses غير موجود.');



            return self::FAILURE;

        }



        $hasGisUsers = Schema::hasTable('gis_users');

        $hasArchivedColumn = Schema::hasColumn('gis_survey_responses', 'is_archived');

        $imported = 0;

        $skipped = 0;



        $query = DB::table('gis_survey_responses')->orderBy('id');



        if ($hasArchivedColumn) {

            $query->where(function ($q) {

                $q->where('is_archived', 0)->orWhereNull('is_archived');

            });

        }



        $query->chunk(200, function ($rows) use ($codeGenerator, $hasGisUsers, &$imported, &$skipped) {

            foreach ($rows as $row) {

                $legacyId = (int) $row->id;



                if ($legacyId <= 0) {

                    $skipped++;

                    continue;

                }



                if (Need::query()

                    ->where('source_platform', 'legacy_gis')

                    ->where('source_record_id', $legacyId)

                    ->exists()) {

                    $skipped++;

                    continue;

                }



                $title = trim((string) ($row->title ?? ''));

                if ($title === '') {

                    $skipped++;

                    continue;

                }



                $legacyStatus = trim((string) ($row->approval_status ?? $row->status ?? ''));

                $status = NeedStatus::fromLegacy($legacyStatus);



                $governorateId = NeedWorkflowService::resolveGovernorateIdByArabicName($row->governorate_name ?? null);

                $branchId = $governorateId

                    ? NeedWorkflowService::resolveBranchIdForGovernorate($governorateId)

                    : null;



                $latitude = $row->latitude ?? null;

                $longitude = $row->longitude ?? null;



                Need::query()->create([

                    'need_code' => $codeGenerator->next(),

                    'title' => $title,

                    'description' => $this->nullableString($row->description ?? null),

                    'summary' => $this->nullableString($row->summary ?? null),

                    'need_owner_type' => 'citizen',

                    'need_scope' => 'individual',

                    'need_type' => $this->nullableString($row->need_type ?? null),

                    'need_category' => $this->nullableString($row->need_category ?? null),

                    'source_platform' => 'legacy_gis',

                    'source_module' => 'gis_survey_responses',

                    'source_record_id' => $legacyId,

                    'governorate_id' => $governorateId,

                    'branch_id' => $branchId,

                    'district_name' => $this->nullableString($row->district_name ?? null),

                    'administrative_unit_name' => $this->nullableString($row->administrative_unit_name ?? null),

                    'countryside_name' => $this->nullableString($row->countryside_name ?? null),

                    'locality_name' => $this->nullableString($row->locality_name ?? null),

                    'village_or_neighborhood' => $this->nullableString($row->village_or_neighborhood ?? null),

                    'address_details' => $this->nullableString($row->address_details ?? null),

                    'latitude' => is_numeric($latitude) ? (float) $latitude : null,

                    'longitude' => is_numeric($longitude) ? (float) $longitude : null,

                    'location_source' => $this->nullableString($row->location_source ?? null),

                    'sector' => $this->nullableString($row->sector ?? null),

                    'economic_sector' => $this->nullableString($row->economic_sector ?? null),

                    'syrsic_section' => $this->nullableString($row->syrsic_section ?? $row->syrsic_section_code ?? null),

                    'syrsic_division' => $this->nullableString($row->syrsic_division ?? $row->syrsic_division_code ?? null),

                    'syrsic_group' => $this->nullableString($row->syrsic_group ?? $row->syrsic_group_code ?? null),

                    'syrsic_class' => $this->nullableString($row->syrsic_class ?? $row->syrsic_class_code ?? null),

                    'syrsic_activity' => $this->nullableString($row->syrsic_activity ?? $row->syrsic_activity_code ?? null),

                    'priority' => $this->nullableString($row->priority ?? null) ?: 'متوسطة',

                    'status' => $status,

                    'approval_status' => $status,

                    'proposed_intervention' => $this->nullableString($row->proposed_intervention ?? null),

                    'applicant_name' => $this->nullableString($row->applicant_name ?? null),

                    'applicant_phone' => $this->nullableString($row->applicant_phone ?? null),

                    'applicant_email' => $this->nullableString($row->applicant_email ?? null),

                    'applicant_type' => $this->nullableString($row->applicant_type ?? null),

                    'organization_name' => $this->nullableString($row->organization_name ?? null),

                    'beneficiaries_count' => $this->nullableInt($row->beneficiaries_count ?? null),

                    'expected_jobs_count' => $this->nullableInt($row->expected_jobs_count ?? null),

                    'expected_projects_count' => $this->nullableInt($row->expected_projects_count ?? null),

                    'impact_level' => $this->nullableString($row->impact_level ?? null),

                    'urgency_level' => $this->nullableString($row->urgency_level ?? null),

                    'notes' => $this->nullableString($row->notes ?? null),

                    'created_by' => $this->resolveCreatedBy($hasGisUsers, $row->created_by ?? null),

                    'updated_by' => $this->resolveCreatedBy($hasGisUsers, $row->updated_by ?? null),

                    'is_mapped' => is_numeric($latitude) && is_numeric($longitude),

                    'is_public' => false,

                    'created_at' => $row->created_at ?? now(),

                    'updated_at' => $row->updated_at ?? now(),

                ]);



                $imported++;

            }

        });



        $this->info("Imported: {$imported}, Skipped: {$skipped}");



        return self::SUCCESS;

    }



    private function resolveCreatedBy(bool $hasGisUsers, mixed $gisUserId): ?int

    {

        $gisUserId = (int) $gisUserId;

        if (!$hasGisUsers || $gisUserId <= 0) {

            return null;

        }



        $email = DB::table('gis_users')->where('id', $gisUserId)->value('email');

        $email = trim((string) $email);

        if ($email === '') {

            return null;

        }



        return User::query()->where('email', $email)->value('id');

    }



    private function nullableString(mixed $value): ?string

    {

        $value = trim((string) $value);



        return $value === '' ? null : $value;

    }



    private function nullableInt(mixed $value): ?int

    {

        if ($value === null || $value === '') {

            return null;

        }



        return is_numeric($value) ? (int) $value : null;

    }

}


