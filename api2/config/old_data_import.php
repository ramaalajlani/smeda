<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Old database connections (import SQL dumps into these DBs first)
    |--------------------------------------------------------------------------
    |
    | mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_authority3;"
    | mysql -u root old_authority3 < "u142331648_authority3 (2).sql"
    | mysql -u root -e "CREATE DATABASE IF NOT EXISTS old_entrep;"
    | mysql -u root old_entrep < u142331648_entrep_db.sql
    |
    */
    'connections' => [
        'authority3' => [
            'driver' => 'mysql',
            'host' => env('OLD_AUTHORITY3_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('OLD_AUTHORITY3_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('OLD_AUTHORITY3_DB_DATABASE', 'old_authority3'),
            'username' => env('OLD_AUTHORITY3_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('OLD_AUTHORITY3_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ],
        'entrep' => [
            'driver' => 'mysql',
            'host' => env('OLD_ENTREP_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('OLD_ENTREP_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('OLD_ENTREP_DB_DATABASE', 'old_entrep'),
            'username' => env('OLD_ENTREP_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('OLD_ENTREP_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ],
    ],

    'sql_dumps' => [
        'authority3' => base_path('u142331648_authority3 (2).sql'),
        'entrep' => base_path('u142331648_entrep_db.sql'),
    ],

    'defaults' => [
        'system_user_id' => 1,
        'fallback_governorate_code' => 'GOV-DAMASCUS',
        'fallback_branch_code' => 'BR-DAMASCUS',
        'imported_user_password_note' => 'Legacy bcrypt hash preserved — users can login with existing password.',
        'incompatible_password_note' => 'Password hash format unknown — user must use password reset.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Import order (respects foreign keys)
    |--------------------------------------------------------------------------
    */
    'phases' => [
        'foundation' => ['governorate_match', 'branch_match'],
        'users_auth' => ['authority3_users', 'entrep_users', 'authority3_roles'],
        'training' => [
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
        ],
        'registration' => [
            'training_center_registration_requests',
            'trainer_registration_requests',
            'trainee_registration_requests',
            'course_registration_requests',
            'course_registration_request_members',
        ],
        'certificates' => ['certificates', 'certificate_approvals'],
        'finance' => [
            'funding_applications_from_surveys',
            'funding_application_details_from_surveys',
            'funding_documents_from_survey_files',
        ],
        'needs' => ['needs_from_surveys'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table mappings: old_table@source => new_table
    |--------------------------------------------------------------------------
    */
    'table_mappings' => [

        // --- authority3: near 1:1 ---
        'authority3.users' => [
            'target' => 'users',
            'dedupe' => ['email'],
            'columns' => [
                'id' => 'id',
                'name' => 'name',
                'email' => 'email',
                'email_verified_at' => 'email_verified_at',
                'password' => 'password',
                'entity_type' => 'entity_type',
                'training_center_id' => ['fk' => 'training_centers'],
                'trainer_id' => ['fk' => 'trainers'],
                'trainee_id' => ['fk' => 'trainees'],
                'parent_user_id' => ['fk' => 'users'],
                'is_active' => 'is_active',
                'remember_token' => 'remember_token',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => [
                'phone' => null,
                'governorate_id' => null,
                'branch_id' => null,
            ],
            'skip_emails' => [
                'admin@system.com',
                'manager@system.com',
                'deputy@system.com',
                'auditor@system.com',
                'general@system.com',
                'branch.damascus@system.com',
                'branch.aleppo@system.com',
            ],
        ],

        'authority3.training_centers' => [
            'target' => 'training_centers',
            'dedupe' => ['code'],
            'columns' => [
                'id' => 'id',
                'name' => 'name',
                'code' => 'code',
                'governorate' => 'governorate',
                'city' => 'city',
                'district' => 'district',
                'address' => 'address',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'location_visibility' => 'location_visibility',
                'license_number' => 'license_number',
                'license_issue_date' => 'license_issue_date',
                'license_expiry_date' => 'license_expiry_date',
                'license_issued_by' => 'license_issued_by',
                'license_image_path' => 'license_image_path',
                'phone' => 'phone',
                'email' => 'email',
                'classification' => 'classification',
                'accreditation_status' => 'accreditation_status',
                'supports_offline_training' => 'supports_offline_training',
                'supports_online_training' => 'supports_online_training',
                'accreditation_start_date' => 'accreditation_start_date',
                'accreditation_end_date' => 'accreditation_end_date',
                'is_active' => 'is_active',
                'notes' => 'notes',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
                'deleted_at' => 'deleted_at',
            ],
            'defaults' => [
                'governorate_id' => ['resolver' => 'governorate_from_string', 'source' => 'governorate'],
                'branch_id' => ['resolver' => 'branch_from_governorate_string', 'source' => 'governorate'],
            ],
        ],

        'authority3.trainers' => ['target' => 'trainers', 'dedupe' => ['trainer_code']],
        'authority3.trainees' => ['target' => 'trainees', 'dedupe' => ['trainee_code', 'national_id']],
        'authority3.training_kits' => ['target' => 'training_kits', 'dedupe' => ['code']],
        'authority3.training_programs' => ['target' => 'training_programs', 'dedupe' => ['code']],
        'authority3.training_courses' => ['target' => 'training_courses', 'dedupe' => ['course_code']],
        'authority3.certificates' => [
            'target' => 'certificates',
            'dedupe' => ['certificate_number', 'verification_code'],
            'defaults' => [
                'certificate_code' => ['resolver' => 'certificate_code_from_legacy'],
                'security_hash' => null,
                'governorate_id' => null,
                'branch_id' => null,
            ],
        ],

        // --- entrep_db ---
        'entrep.users' => [
            'target' => 'users',
            'dedupe' => ['email', 'phone'],
            'columns' => [
                'full_name' => 'name',
                'email' => 'email',
                'phone' => 'phone',
                'password' => 'password',
                'created_at' => 'created_at',
            ],
            'defaults' => [
                'entity_type' => 'entrepreneur',
                'is_active' => true,
                'updated_at' => ['resolver' => 'now'],
            ],
            'roles' => [
                'admin' => 'admin',
                'entrepreneur' => 'trainee_user',
                'viewer' => 'auditor',
            ],
        ],

        'entrep.entrepreneur_surveys' => [
            'target' => 'funding_applications',
            'dedupe' => ['application_number', 'email_project'],
            'transform' => 'survey_to_funding_application',
        ],

        'entrep.entrepreneur_survey_files' => [
            'target' => 'funding_documents',
            'dedupe' => ['application_id', 'original_name', 'field_name'],
            'transform' => 'survey_file_to_funding_document',
        ],
    ],

    'unmapped_tables' => [
        'entrep.communities' => 'No community module in new API — archive or future module',
        'entrep.community_members' => 'Depends on communities — not imported',
        'entrep.community_messages' => 'Depends on communities — not imported',
        'entrep.community_reads' => 'Depends on communities — not imported',
        'entrep.entrepreneur_messages' => 'No /messages API — not imported',
        'entrep.login_attempts' => 'Security log only — optional audit_logs import later',
        'authority3.cache' => 'Laravel runtime — skip',
        'authority3.cache_locks' => 'Laravel runtime — skip',
        'authority3.jobs' => 'Laravel runtime — skip',
        'authority3.job_batches' => 'Laravel runtime — skip',
        'authority3.failed_jobs' => 'Laravel runtime — skip',
        'authority3.migrations' => 'Schema metadata — skip',
        'authority3.sessions' => 'Runtime — skip',
        'authority3.password_reset_tokens' => 'Expired tokens — skip',
        'authority3.personal_access_tokens' => 'Regenerated on login — skip',
    ],

    'governorate_name_map' => [
        'دمشق' => 'GOV-DAMASCUS',
        'ريف دمشق' => 'GOV-RIF-DIMASHQ',
        'حلب' => 'GOV-ALEPPO',
        'حمص' => 'GOV-HOMS',
        'حماة' => 'GOV-HAMA',
        'اللاذقية' => 'GOV-LATAKIA',
        'طرطوس' => 'GOV-TARTOUS',
        'إدلب' => 'GOV-IDLIB',
        'دير الزور' => 'GOV-DEIR-EZ-ZOR',
        'الرقة' => 'GOV-RAQQA',
        'الحسكة' => 'GOV-HASAKAH',
        'السويداء' => 'GOV-SUWAYDA',
        'درعا' => 'GOV-DARAA',
        'القنيطرة' => 'GOV-QUNEITRA',
    ],

];
