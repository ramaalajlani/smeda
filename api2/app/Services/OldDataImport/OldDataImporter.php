<?php

namespace App\Services\OldDataImport;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class OldDataImporter
{
    private Connection $authority3;

    private Connection $entrep;

    public function __construct(
        private readonly ImportReport $report,
        private readonly IdMapper $idMapper,
        private readonly ValueResolver $resolver,
        private readonly bool $dryRun = true,
    ) {
        $this->authority3 = DB::connection('old_authority3');
        $this->entrep = DB::connection('old_entrep');
    }

    public static function make(bool $dryRun = true): self
    {
        $report = new ImportReport;

        return new self($report, new IdMapper($dryRun), new ValueResolver($report), $dryRun);
    }

    public function report(): ImportReport
    {
        return $this->report;
    }

    public function analyzeSqlDumps(): void
    {
        $analyzer = new SqlDumpAnalyzer;

        foreach (config('old_data_import.sql_dumps', []) as $source => $path) {
            $tables = $analyzer->analyzeFile($path);
            $insertCounts = $analyzer->countInserts($path);

            $this->report->tablesOld += count($tables);

            foreach ($tables as $name => $meta) {
                $key = "{$source}.{$name}";
                $mapped = array_key_exists($key, config('old_data_import.table_mappings', []))
                    || $this->isDirectAuthority3Table($source, $name);

                if ($mapped) {
                    $this->report->tablesMapped++;
                } else {
                    $this->report->tablesUnmapped++;
                    $unmapped = config('old_data_import.unmapped_tables', []);
                    $this->report->unmappedTables[$key] = $unmapped[$key] ?? 'No mapping defined yet';
                }

                $this->report->byTable[$key]['columns'] = count($meta['columns']);
                $this->report->byTable[$key]['insert_batches'] = $insertCounts[$name] ?? 0;
            }
        }
    }

    public function run(): void
    {
        $this->importAuthority3Users();
        $this->importEntrepUsers();
        $this->importSimpleAuthority3Tables([
            'training_centers',
            'training_center_platforms',
            'trainers',
            'trainer_profiles',
            'trainees',
            'training_kits',
            'trainer_training_kit',
            'training_programs',
            'training_program_training_kit',
            'training_kit_nominations',
            'training_courses',
            'training_course_trainee',
            'workforces',
            'training_center_registration_requests',
            'trainer_registration_requests',
            'trainee_registration_requests',
            'course_registration_requests',
            'course_registration_request_members',
            'certificates',
            'certificate_approvals',
            'model_has_roles',
        ]);
        $this->importEntrepreneurSurveys();
        $this->importEntrepreneurSurveyFiles();
    }

    private function isDirectAuthority3Table(string $source, string $table): bool
    {
        return $source === 'authority3' && Schema::hasTable($table);
    }

    private function importAuthority3Users(): void
    {
        $table = 'authority3.users';
        $skip = config('old_data_import.table_mappings.authority3.users.skip_emails', []);

        $rows = $this->authority3->table('users')->orderBy('id')->get();
        foreach ($rows as $row) {
            $this->report->increment($table, 'read');

            if (in_array($row->email, $skip, true)) {
                $this->report->increment($table, 'skipped');
                continue;
            }

            $existing = DB::table('users')->where('email', $row->email)->first();
            if ($existing) {
                $this->idMapper->remember('authority3', 'users', (int) $row->id, (int) $existing->id, $row->email);
                $this->report->increment($table, 'skipped');
                continue;
            }

            $payload = [
                'name' => $row->name,
                'email' => $row->email,
                'email_verified_at' => $row->email_verified_at,
                'password' => $row->password,
                'entity_type' => $row->entity_type,
                'is_active' => (bool) $row->is_active,
                'remember_token' => $row->remember_token,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];

            $this->report->passwordNotes[$row->email] = $this->resolver->passwordNote($row->password);

            if ($this->dryRun) {
                $this->report->increment($table, 'inserted');
                continue;
            }

            $newId = DB::table('users')->insertGetId($payload);
            $this->idMapper->remember('authority3', 'users', (int) $row->id, $newId, $row->email);
            $this->assignEntityRole($row->entity_type, $newId);
            $this->report->increment($table, 'inserted');
        }
    }

    private function importEntrepUsers(): void
    {
        $table = 'entrep.users';
        $roleMap = config('old_data_import.table_mappings.entrep.users.roles', []);

        $rows = $this->entrep->table('users')->orderBy('id')->get();
        foreach ($rows as $row) {
            $this->report->increment($table, 'read');

            $sanitizedPhone = $this->resolver->sanitizePhone($row->phone, [
                'source' => $table,
                'old_id' => $row->id,
                'column' => 'phone',
            ]);

            $existing = DB::table('users')
                ->when(filled($row->email), fn ($q) => $q->where('email', $row->email))
                ->first();

            if (! $existing && $sanitizedPhone !== null) {
                $existing = DB::table('users')->where('phone', $sanitizedPhone)->first();
            }

            if ($existing) {
                $this->idMapper->remember('entrep', 'users', (int) $row->id, (int) $existing->id, $row->email ?: $row->phone);
                $this->syncUserPhone((int) $existing->id, $existing->phone, $sanitizedPhone, $table);
                $this->report->increment($table, 'skipped');
                continue;
            }

            if (blank($row->email)) {
                $this->report->addError($table, 'Missing email — skipped', ['old_id' => $row->id]);
                $this->report->increment($table, 'skipped');
                continue;
            }

            $payload = [
                'name' => $row->full_name,
                'email' => $row->email,
                'phone' => $sanitizedPhone,
                'password' => $row->password,
                'entity_type' => 'entrepreneur',
                'governorate_id' => $this->resolver->governorateIdFromArabicName($row->governorate ?? null),
                'branch_id' => $this->resolver->branchIdFromGovernorateName($row->governorate ?? null),
                'is_active' => true,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->created_at ?? now(),
            ];

            $this->report->passwordNotes[$row->email] = $this->resolver->passwordNote($row->password);

            if ($this->dryRun) {
                $this->report->increment($table, 'inserted');
                continue;
            }

            $newId = DB::table('users')->insertGetId($payload);
            $this->idMapper->remember('entrep', 'users', (int) $row->id, $newId, $row->email);

            $role = $roleMap[$row->role] ?? 'trainee_user';
            $this->assignEntityRole($role, $newId);

            $this->report->increment($table, 'inserted');
        }
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function importSimpleAuthority3Tables(array $tables): void
    {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! $this->tableExistsOnOld($table)) {
                continue;
            }

            $key = "authority3.{$table}";
            $rows = $this->fetchOldRows($this->authority3, $table);
            $columns = Schema::getColumnListing($table);

            foreach ($rows as $row) {
                $this->report->increment($key, 'read');
                $payload = $this->mapRowToTarget($table, (array) $row, $columns);

                if ($this->isDuplicate($table, $payload)) {
                    $this->report->increment($key, 'skipped');
                    continue;
                }

                if ($this->dryRun) {
                    $this->report->increment($key, 'inserted');
                    continue;
                }

                try {
                    $newId = DB::table($table)->insertGetId($payload);
                    if (isset($row->id)) {
                        $this->idMapper->remember('authority3', $table, (int) $row->id, $newId);
                    }
                    $this->report->increment($key, 'inserted');
                } catch (\Throwable $e) {
                    $this->report->addError($key, $e->getMessage(), ['old_id' => $row->id ?? null]);
                }
            }
        }
    }

    private function importEntrepreneurSurveys(): void
    {
        $table = 'entrep.entrepreneur_surveys';
        if (! $this->tableExistsOnOld('entrepreneur_surveys', 'entrep')) {
            return;
        }

        $systemUserId = (int) config('old_data_import.defaults.system_user_id', 1);
        $rows = $this->entrep->table('entrepreneur_surveys')->orderBy('id')->get();

        foreach ($rows as $row) {
            $this->report->increment($table, 'read');
            $survey = (array) $row;

            $applicantUserId = $this->idMapper->resolve('entrep', 'users', (int) $row->user_id)
                ?? $this->idMapper->resolve('authority3', 'users', (int) $row->user_id)
                ?? $systemUserId;

            $application = $this->resolver->surveyToFundingApplication($survey, $applicantUserId, $systemUserId);

            $exists = DB::table('funding_applications')
                ->where('application_number', $application['application_number'])
                ->exists();

            if ($exists) {
                $this->report->increment($table, 'skipped');
                continue;
            }

            if ($this->dryRun) {
                $this->report->increment($table, 'inserted');
                $this->report->increment('entrep.entrepreneur_surveys→needs', 'inserted');
                continue;
            }

            DB::beginTransaction();
            try {
                $applicationId = DB::table('funding_applications')->insertGetId($application);
                $this->idMapper->remember('entrep', 'entrepreneur_surveys', (int) $row->id, $applicationId);

                $details = [
                    'funding_application_id' => $applicationId,
                    'employees_count' => (int) ($row->direct_jobs ?? 0),
                    'notes' => substr((string) ($row->data_json ?? ''), 0, 65000),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->created_at ?? now(),
                ];
                DB::table('funding_application_details')->insert($details);

                $need = $this->resolver->surveyToNeed($survey, $systemUserId, $applicationId, $application['phone']);
                $needId = DB::table('needs')->insertGetId($need);
                $this->idMapper->remember('entrep', 'needs_from_surveys', (int) $row->id, $needId);

                DB::commit();
                $this->report->increment($table, 'inserted');
                $this->report->increment('entrep.entrepreneur_surveys→needs', 'inserted');
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->report->addError($table, $e->getMessage(), ['old_id' => $row->id]);
            }
        }
    }

    private function importEntrepreneurSurveyFiles(): void
    {
        $table = 'entrep.entrepreneur_survey_files';
        if (! $this->tableExistsOnOld('entrepreneur_survey_files', 'entrep')) {
            return;
        }

        $systemUserId = (int) config('old_data_import.defaults.system_user_id', 1);
        $rows = $this->entrep->table('entrepreneur_survey_files')->orderBy('id')->get();

        foreach ($rows as $row) {
            $this->report->increment($table, 'read');

            $applicationId = $this->idMapper->resolve('entrep', 'entrepreneur_surveys', (int) $row->survey_id);
            if (! $applicationId) {
                $this->report->increment($table, 'skipped');
                continue;
            }

            $exists = DB::table('funding_documents')
                ->where('funding_application_id', $applicationId)
                ->where('original_name', $row->original_name)
                ->where('document_type', $row->field_name)
                ->exists();

            if ($exists) {
                $this->report->increment($table, 'skipped');
                continue;
            }

            if (! is_file(public_path($row->file_path)) && ! is_file(base_path($row->file_path))) {
                $this->report->missingFiles[] = [
                    'old_id' => $row->id,
                    'file_path' => $row->file_path,
                    'original_name' => $row->original_name,
                ];
            }

            $payload = [
                'funding_application_id' => $applicationId,
                'document_type' => $row->field_name,
                'file_path' => $row->file_path,
                'original_name' => $row->original_name ?? basename((string) $row->file_path),
                'mime_type' => $row->mime_type,
                'size' => (int) ($row->file_size ?? 0),
                'uploaded_by' => $systemUserId,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->created_at ?? now(),
            ];

            if ($this->dryRun) {
                $this->report->increment($table, 'inserted');
                continue;
            }

            try {
                DB::table('funding_documents')->insert($payload);
                $this->report->increment($table, 'inserted');
            } catch (\Throwable $e) {
                $this->report->addError($table, $e->getMessage(), ['old_id' => $row->id]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $targetColumns
     * @return array<string, mixed>
     */
    private function mapRowToTarget(string $table, array $row, array $targetColumns): array
    {
        $payload = [];
        foreach ($targetColumns as $column) {
            if (array_key_exists($column, $row)) {
                $payload[$column] = $row[$column];
            }
        }

        foreach ($row as $column => $value) {
            if (str_ends_with($column, '_id') && $value !== null) {
                $entity = str_replace('_id', '', $column);
                if ($entity === 'parent_user') {
                    $entity = 'users';
                }
                if ($entity === 'approved_by' || $entity === 'submitted_by' || $entity === 'reviewed_by') {
                    $entity = 'users';
                }

                $mapped = $this->idMapper->resolve('authority3', $entity, (int) $value)
                    ?? $this->idMapper->resolve('authority3', $table, (int) $value);

                if ($mapped !== null) {
                    $payload[$column] = $mapped;
                }
            }
        }

        if ($table === 'training_centers' && empty($payload['governorate_id'])) {
            $payload['governorate_id'] = $this->resolver->governorateIdFromArabicName($row['governorate'] ?? null);
            $payload['branch_id'] = $this->resolver->branchIdFromGovernorateName($row['governorate'] ?? null);
        }

        if ($table === 'certificates') {
            $payload['certificate_code'] = $payload['certificate_code']
                ?? $payload['verification_code']
                ?? ('LEG-CERT-'.$row['id']);
        }

        foreach (['phone', 'applicant_phone', 'contact_phone', 'mobile', 'guardian_phone'] as $phoneColumn) {
            if (array_key_exists($phoneColumn, $payload)) {
                $payload[$phoneColumn] = $this->resolver->sanitizePhone($payload[$phoneColumn], [
                    'source' => "authority3.{$table}",
                    'old_id' => $row['id'] ?? null,
                    'column' => $phoneColumn,
                ]);
            }
        }

        unset($payload['id']);

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function isDuplicate(string $table, array $payload): bool
    {
        $query = DB::table($table);

        return match ($table) {
            'users' => $query->where('email', $payload['email'] ?? '')->exists(),
            'training_centers' => $query->where('code', $payload['code'] ?? '')->exists(),
            'trainers' => $query->where('trainer_code', $payload['trainer_code'] ?? '')->exists(),
            'trainees' => $query->where('trainee_code', $payload['trainee_code'] ?? '')->exists(),
            'training_kits' => $query->where('code', $payload['code'] ?? '')->exists(),
            'training_programs' => $query->where('code', $payload['code'] ?? '')->exists(),
            'training_courses' => $query->where('course_code', $payload['course_code'] ?? '')->exists(),
            'certificates' => $query->where('certificate_number', $payload['certificate_number'] ?? '')->exists(),
            default => false,
        };
    }

    private function tableExistsOnOld(string $table, string $source = 'authority3'): bool
    {
        $connection = $source === 'entrep' ? $this->entrep : $this->authority3;

        return $connection->getSchemaBuilder()->hasTable($table);
    }

    private function fetchOldRows(Connection $connection, string $table): Collection
    {
        $query = $connection->table($table);
        $columns = $connection->getSchemaBuilder()->getColumnListing($table);

        if (in_array('id', $columns, true)) {
            $query->orderBy('id');
        }

        return $query->get();
    }

    private function syncUserPhone(int $userId, ?string $currentPhone, ?string $sanitizedPhone, string $table): void
    {
        if ($sanitizedPhone === $currentPhone) {
            return;
        }

        $currentIsValid = filled($currentPhone)
            && (new PhoneSanitizer)->sanitize($currentPhone) === $currentPhone;

        $shouldUpdate = $sanitizedPhone !== null
            || (filled($currentPhone) && ! $currentIsValid);

        if (! $shouldUpdate) {
            return;
        }

        if ($this->dryRun) {
            $this->report->increment($table, 'updated');

            return;
        }

        DB::table('users')->where('id', $userId)->update([
            'phone' => $sanitizedPhone,
            'updated_at' => now(),
        ]);
        $this->report->increment($table, 'updated');
    }

    private function assignEntityRole(?string $entityType, int $userId): void
    {
        if ($this->dryRun || ! class_exists(Role::class)) {
            return;
        }

        $roleName = match ($entityType) {
            'admin', 'general_director' => 'general_director',
            'branch_manager' => 'branch_manager',
            'training_manager' => 'training_manager',
            'deputy_director', 'deputy_general_director' => 'deputy_general_director',
            'center_user' => 'center_user',
            'trainer_user' => 'trainer_user',
            'trainee_user', 'entrepreneur' => 'trainee_user',
            'auditor' => 'auditor',
            default => null,
        };

        if (! $roleName) {
            return;
        }

        $user = \App\Models\User::find($userId);
        if ($user && ! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }
}
