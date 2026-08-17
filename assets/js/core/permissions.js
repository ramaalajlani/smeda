window.AppPermissions = {
  /*
  |--------------------------------------------------------------------------
  | Trainers
  |--------------------------------------------------------------------------
  */
  VIEW_TRAINERS: 'view_trainers',
  MANAGE_TRAINERS: 'manage_trainers',
  VIEW_TRAINER_PROFILES: 'view_trainer_profiles',
  EDIT_OWN_TRAINER_PROFILE: 'edit_own_trainer_profile',

  /*
  |--------------------------------------------------------------------------
  | Training Centers
  |--------------------------------------------------------------------------
  */
  VIEW_CENTERS: 'view_centers',
  MANAGE_CENTERS: 'manage_centers',

  /*
  |--------------------------------------------------------------------------
  | Training Kits
  |--------------------------------------------------------------------------
  */
  VIEW_KITS: 'view_kits',
  MANAGE_KITS: 'manage_kits',
  MANAGE_TRAINING_CATEGORIES: 'manage_training_categories',
  NOMINATE_TRAINING_KITS: 'nominate_training_kits',
  REVIEW_TRAINING_KIT_NOMINATIONS: 'review_training_kit_nominations',

  /*
  |--------------------------------------------------------------------------
  | Training Programs
  |--------------------------------------------------------------------------
  */
  VIEW_PROGRAMS: 'view_programs',
  MANAGE_PROGRAMS: 'manage_programs',

  /*
  |--------------------------------------------------------------------------
  | Training Courses
  |--------------------------------------------------------------------------
  */
  VIEW_COURSES: 'view_courses',
  MANAGE_COURSES: 'manage_courses',
  VIEW_COURSE_DETAILS: 'view_course_details',

  /*
  |--------------------------------------------------------------------------
  | Trainees
  |--------------------------------------------------------------------------
  */
  VIEW_TRAINEES: 'view_trainees',
  MANAGE_TRAINEES: 'manage_trainees',

  /*
  |--------------------------------------------------------------------------
  | Certificates
  |--------------------------------------------------------------------------
  */
  VIEW_CERTIFICATES: 'view_certificates',
  ISSUE_CERTIFICATES: 'issue_certificates',

  VIEW_CERTIFICATE_APPROVALS: 'view_certificate_approvals',
  APPROVE_CENTER_CERTIFICATES: 'approve_center_certificates',
  APPROVE_TRAINING_CERTIFICATES: 'approve_training_certificates',
  APPROVE_DEPUTY_CERTIFICATES: 'approve_deputy_certificates',
  APPROVE_GENERAL_DIRECTOR_CERTIFICATES: 'approve_general_director_certificates',

  PRINT_CERTIFICATES: 'print_certificates',
  VERIFY_CERTIFICATES: 'verify_certificates',

  /*
  |--------------------------------------------------------------------------
  | Reports / Audit
  |--------------------------------------------------------------------------
  */
  VIEW_REPORTS: 'view_reports',
  VIEW_AUDIT: 'view_audit',

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - General
  |--------------------------------------------------------------------------
  */
  VIEW_REGISTRATION_REQUESTS: 'view_registration_requests',

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Centers
  |--------------------------------------------------------------------------
  */
  CREATE_CENTER_REGISTRATION_REQUESTS: 'create_center_registration_requests',
  REVIEW_CENTER_REGISTRATION_REQUESTS: 'review_center_registration_requests',

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Trainers
  |--------------------------------------------------------------------------
  */
  CREATE_TRAINER_REGISTRATION_REQUESTS: 'create_trainer_registration_requests',
  REVIEW_TRAINER_REGISTRATION_REQUESTS: 'review_trainer_registration_requests',

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Trainees
  |--------------------------------------------------------------------------
  */
  CREATE_TRAINEE_REGISTRATION_REQUESTS: 'create_trainee_registration_requests',
  REVIEW_TRAINEE_REGISTRATION_REQUESTS: 'review_trainee_registration_requests',

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Courses
  |--------------------------------------------------------------------------
  */
  CREATE_COURSE_REGISTRATION_REQUESTS: 'create_course_registration_requests',
  CONFIRM_COURSE_REGISTRATION_REQUESTS: 'confirm_course_registration_requests',
  COMPLETE_COURSE_REGISTRATION_REQUESTS: 'complete_course_registration_requests',

  /*
  |--------------------------------------------------------------------------
  | Access / System Administration
  |--------------------------------------------------------------------------
  */
  VIEW_USERS: 'view_users',
  MANAGE_USER_ACCESS: 'manage_user_access',
  VIEW_ROLES: 'view_roles',
  MANAGE_ROLES: 'manage_roles',
  VIEW_PERMISSIONS: 'view_permissions',
  MANAGE_PERMISSIONS: 'manage_permissions',
  ASSIGN_ROLES: 'assign_roles',
  REVOKE_ROLES: 'revoke_roles',
  ASSIGN_PERMISSIONS: 'assign_permissions',
  REVOKE_PERMISSIONS: 'revoke_permissions',

  CONSULTING_REQUEST_ACCESS_ROLES: [
    'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
    'branch_manager', 'branch_officer', 'governor', 'project_owner',
    'consultant_union_admin', 'consultant_office',
  ],
  CONSULTING_OFFICE_MANAGE_ROLES: [
    'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
    'consultant_union_admin', 'branch_manager', 'governor',
  ],
  CONSULTING_REQUEST_CREATE_ROLES: [
    'project_owner', 'admin', 'super_admin', 'system_admin',
    'general_director', 'project_services_manager', 'branch_manager', 'governor',
  ],
  CONSULTING_ADMIN_ROLES: [
    'admin', 'super_admin', 'system_admin', 'general_director', 'project_services_manager',
    'consultant_union_admin', 'branch_manager',
  ],
};