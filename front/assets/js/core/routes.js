window.APP_ROUTES = {
  /*
  |--------------------------------------------------------------------------
  | Auth
  |--------------------------------------------------------------------------
  */
  register: () => `${window.APP_CONFIG.API_BASE_URL}/register`,
  login: () => `${window.APP_CONFIG.API_BASE_URL}/login`,
  me: () => `${window.APP_CONFIG.API_BASE_URL}/me`,
  logout: () => `${window.APP_CONFIG.API_BASE_URL}/logout`,

  /*
  |--------------------------------------------------------------------------
  | Dashboard
  |--------------------------------------------------------------------------
  */
  dashboard: () => `${window.APP_CONFIG.API_BASE_URL}/dashboard`,

  /*
  |--------------------------------------------------------------------------
  | Trainers
  |--------------------------------------------------------------------------
  */
  trainers: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/trainers`,
      params
    ),

  trainerShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/trainers/${id}`,

  /*
  |--------------------------------------------------------------------------
  | Trainer Profiles
  |--------------------------------------------------------------------------
  */
  trainerProfileShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/trainer-profiles/${id}`,

  myTrainerProfile: () =>
    `${window.APP_CONFIG.API_BASE_URL}/my-trainer-profile`,

  myTrainerProfileUpdate: () =>
    `${window.APP_CONFIG.API_BASE_URL}/my-trainer-profile`,

  /*
  |--------------------------------------------------------------------------
  | Trainees
  |--------------------------------------------------------------------------
  */
  trainees: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/trainees`,
      params
    ),

  traineeShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/trainees/${id}`,

  /*
  |--------------------------------------------------------------------------
  | Workforce
  |--------------------------------------------------------------------------
  */
  workforces: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/workforces`,
      params
    ),

  workforceShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/workforces/${id}`,

  workforceEnroll: () =>
    `${window.APP_CONFIG.API_BASE_URL}/workforces/enroll`,

  jobPostings: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/workforce/job-postings`, params),

  jobPostingShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/workforce/job-postings/${id}`,

  jobPostingStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/workforce/job-postings`,

  jobApplications: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/workforce/job-applications`, params),

  jobApplicationStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/workforce/job-applications`,

  staffTrainingRequests: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/workforce/staff-training-requests`, params),

  staffTrainingRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/workforce/staff-training-requests`,

  trainingKitPublicRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/training-kit-public-requests`,

  /*
  |--------------------------------------------------------------------------
  | Training Centers
  |--------------------------------------------------------------------------
  */
  trainingCenters: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/training-centers`,
      params
    ),

  trainingCenterShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-centers/${id}`,

  mapTrainingCenters: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/map/training-centers`,
      params
    ),

  mapTrainees: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/map/trainees`,
      params
    ),

  /*
  |--------------------------------------------------------------------------
  | Training Kits
  |--------------------------------------------------------------------------
  */
  trainingKits: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/training-kits`,
      params
    ),

  trainingKitShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-kits/${id}`,

  /*
  |--------------------------------------------------------------------------
  | Training Kit Nominations
  |--------------------------------------------------------------------------
  */
  trainingKitNominations: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/training-kit-nominations`,
      params
    ),

  trainingKitNominationShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-kit-nominations/${id}`,

  trainingKitNominationStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/training-kit-nominations`,

  trainingKitNominationReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-kit-nominations/${id}/review`,

  /*
  |--------------------------------------------------------------------------
  | Training Programs
  |--------------------------------------------------------------------------
  */
  trainingPrograms: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/training-programs`,
      params
    ),

  trainingProgramShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-programs/${id}`,

  /*
  |--------------------------------------------------------------------------
  | Training Courses
  |--------------------------------------------------------------------------
  */
  trainingCourses: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/training-courses`,
      params
    ),

  trainingCourseShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}`,

  trainingCourseStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses`,

  trainingCourseUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}`,

  trainingCourseComplete: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}/complete`,

  trainingCourseTrainees: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}/trainees`,

  trainingCourseAddTrainee: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}/trainees`,

  trainingCourseUpdateTrainee: (id, traineeId) =>
    `${window.APP_CONFIG.API_BASE_URL}/training-courses/${id}/trainees/${traineeId}`,

  /*
  |--------------------------------------------------------------------------
  | Admin
  |--------------------------------------------------------------------------
  */
  adminAccessSummary: () =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/access-summary`,

  adminUsers: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/users`,
      params
    ),

  adminUserStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users`,

  adminUserUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}`,

  adminUserAccess: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/access`,

  adminUserUpdateStatus: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/status`,

  adminUserChangePassword: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/change-password`,

  adminUserSyncRoles: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/roles/sync`,

  adminUserSyncPermissions: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/permissions/sync`,

  adminUserActivityLogs: (id, params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/users/${id}/activity-logs`,
      params
    ),

  adminRoles: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/roles`,
      params
    ),

  adminRoleStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/roles`,

  adminRoleUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/roles/${id}`,

  adminRoleDelete: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/roles/${id}`,

  adminRoleSyncPermissions: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/roles/${id}/permissions`,

  adminRoleShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/roles/${id}`,

  adminPermissions: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/permissions`,
      params
    ),

  adminPermissionStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/permissions`,

  adminActivityLogs: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/activity-logs`,
      params
    ),

  adminActivityLogShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/admin/activity-logs/${id}`,

  adminActivityLogsExport: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/admin/activity-logs/export`,
      { format: 'csv', ...params }
    ),

  branchStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/branches`,

  branchUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/branches/${id}`,

  branchDelete: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/branches/${id}`,

  agreements: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/agreements`, params),

  agreementStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/agreements`,

  agreementShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/agreements/${id}`,

  agreementUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/agreements/${id}`,

  agreementApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/agreements/${id}/approve`,

  financeRecords: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/records`, params),

  financeRecordStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/records`,

  financeRecordUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/records/${id}`,

  financeRecordShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/records/${id}`,

  financeRecordApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/records/${id}/approve`,

  fundingApplications: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/applications`, params),

  fundingApplicationStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications`,

  fundingApplicationShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}`,

  fundingApplicationUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}`,

  fundingApplicationSubmit: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/submit`,

  fundingApplicationBranchReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/branch-review`,

  fundingApplicationAssignConsultant: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/assign-consultant`,

  fundingApplicationAssignPartner: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/assign-partner`,

  fundingApplicationCreateLoan: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/create-loan`,

  fundingApplicationDocuments: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/applications/${id}/documents`,

  fundingConsultantOffices: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices`, params),

  fundingPartners: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/partners`, params),

  fundingConsultantAssignmentPrice: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-assignments/${id}/price-offer`,

  fundingConsultantAssignmentApprovePrice: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-assignments/${id}/approve-price`,

  fundingConsultantReports: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-reports`,

  fundingPartnerAssignmentDecision: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partner-assignments/${id}/decision`,

  fundingLoans: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/loans`, params),

  fundingLoanShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}`,

  fundingLoanUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}`,

  fundingLoanPayments: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}/payments`,

  fundingLoanStorePayment: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}/payments`,

  fundingLoanClose: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}/close`,

  fundingLoanMarkDefaulted: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/loans/${id}/mark-defaulted`,

  fundingMetrics: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/metrics`,

  fundingCloud: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/cloud`, params),

  fundingFunded: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/funded`, params),

  fundingDefaulted: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/finance/defaulted`, params),

  consultantUnionDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-union/dashboard`,

  consultantOfficeStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices`,

  consultantOfficeShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}`,

  consultantOfficeUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}`,

  consultantOfficeApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/approve`,

  consultantOfficeActivate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/activate`,

  consultantOfficeSuspend: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/suspend`,

  consultantOfficeAssignments: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/assignments`,

  consultantOfficeReports: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/reports`,

  consultantOfficeMetrics: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-offices/${id}/metrics`,

  myConsultantAssignments: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/my-consultant-assignments`,

  centralBankDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/central-bank/dashboard`,

  fundingPartnerDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/funding-partner/dashboard`,

  consultantOfficeDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/consultant-office/dashboard`,

  financeManagerDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/manager/dashboard`,

  needsDataEntryWorkspace: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/workspace/data-entry`,

  needsReviewerWorkspace: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/workspace/reviewer`,

  fundingPartnerStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners`,

  fundingPartnerShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}`,

  fundingPartnerUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}`,

  fundingPartnerApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/approve`,

  fundingPartnerActivate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/activate`,

  fundingPartnerSuspend: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/suspend`,

  fundingPartnerAssignments: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/assignments`,

  fundingPartnerDecisions: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/decisions`,

  fundingPartnerLoans: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/loans`,

  fundingPartnerMetrics: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/partners/${id}/metrics`,

  myPartnerAssignments: () =>
    `${window.APP_CONFIG.API_BASE_URL}/finance/my-partner-assignments`,

  governorates: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/governorates`, params),

  branches: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/branches`, params),

  branchShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/branches/${id}`,

  /*
  |--------------------------------------------------------------------------
  | Certificates
  |--------------------------------------------------------------------------
  */
  certificates: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/certificates`,
      params
    ),

  certificateShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/certificates/${id}`,

  certificateShowByCode: (certificateCode) =>
    `${window.APP_CONFIG.API_BASE_URL}/certificates/code/${encodeURIComponent(certificateCode)}`,

  certificateIssue: () =>
    `${window.APP_CONFIG.API_BASE_URL}/certificates/issue`,

  certificateApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/certificates/${id}/approve`,

  certificateVerify: () =>
    `${window.APP_CONFIG.API_BASE_URL}/certificates/verify`,

  certificateVerifyByCode: (certificateCode) =>
    `${window.APP_CONFIG.API_BASE_URL}/verify-certificate/${encodeURIComponent(certificateCode)}`,

  certificatePrint: (id) =>
    `${window.APP_CONFIG.BACKEND_BASE_URL}/certificates/${id}/print`,

  certificatePrintByCode: (certificateCode) =>
    `${window.APP_CONFIG.BACKEND_BASE_URL}/certificates/${encodeURIComponent(certificateCode)}/print`,

  certificateViewByCode: (certificateCode) =>
    `${window.APP_CONFIG.BACKEND_BASE_URL}/verify-certificate/${encodeURIComponent(certificateCode)}`,

  certificatePdfByCode: (certificateCode) =>
    `${window.APP_CONFIG.BACKEND_BASE_URL}/certificates/${encodeURIComponent(certificateCode)}/pdf`,

  signatureVerify: (code) =>
    `${window.APP_CONFIG.API_BASE_URL}/signatures/verify/${encodeURIComponent(code)}`,

  myElectronicSignature: () =>
    `${window.APP_CONFIG.API_BASE_URL}/my-electronic-signature`,

  myElectronicSignatureImage: () =>
    `${window.APP_CONFIG.API_BASE_URL}/my-electronic-signature/image`,

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Centers
  |--------------------------------------------------------------------------
  */
  centerRegistrationRequests: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/registration-requests/centers`,
      params
    ),

  centerRegistrationRequestShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/centers/${id}`,

  centerRegistrationRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/centers`,

  centerRegistrationRequestReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/centers/${id}/review`,

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Trainers
  |--------------------------------------------------------------------------
  */
  trainerRegistrationRequests: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainers`,
      params
    ),

  trainerRegistrationRequestShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainers/${id}`,

  trainerRegistrationRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainers`,

  trainerRegistrationRequestReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainers/${id}/review`,

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Trainees
  |--------------------------------------------------------------------------
  */
  traineeRegistrationRequests: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainees`,
      params
    ),

  traineeRegistrationRequestShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainees/${id}`,

  traineeRegistrationRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainees`,

  traineeRegistrationRequestReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/trainees/${id}/review`,

  /*
  |--------------------------------------------------------------------------
  | Registration Requests - Courses
  |--------------------------------------------------------------------------
  */
  courseRegistrationRequests: (params = {}) =>
    window.APP_API.withQuery(
      `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses`,
      params
    ),

  courseRegistrationRequestShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses/${id}`,

  courseRegistrationRequestStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses`,

  courseRegistrationRequestConfirmByGuardian: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses/${id}/confirm-by-guardian`,

  courseRegistrationRequestComplete: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses/${id}/complete`,

  courseRegistrationRequestCancel: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/registration-requests/courses/${id}/cancel`,

  /*
  |--------------------------------------------------------------------------
  | GIS / Needs Map
  |--------------------------------------------------------------------------
  */
  needs: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/needs`, params),

  needShow: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}`,

  needStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs`,

  needUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}`,

  needReview: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/review`,

  needApprove: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/approve`,

  needReject: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/reject`,

  needReturn: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/return`,

  needClassify: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/classify`,

  needsAiSuggest: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/ai-suggest`,

  needAiSuggest: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/ai-suggest`,

  // المستشار الذكي (بروكسي Laravel — لا اتصال مباشر بالخدمة الخارجية)
  aiChat: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/chat`,

  aiChatContinue: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/chat/continue`,

  aiChatReset: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/chat/reset`,

  aiConfig: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/config`,

  aiIsic4Classify: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/isic4/classify`,

  aiChatHistory: () =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/chat/history`,

  aiChatHistoryMessages: (session) =>
    `${window.APP_CONFIG.API_BASE_URL}/ai/chat/history/${encodeURIComponent(session)}/messages`,

  aiChatHistoryResume: (session) =>
        `${window.APP_CONFIG.API_BASE_URL}/ai/chat/history/${encodeURIComponent(session)}/resume`,

  aiKnowledgeDepartments: () =>
        `${window.APP_CONFIG.API_BASE_URL}/ai/knowledge/departments`,

  aiKnowledgeItems: (department) =>
        `${window.APP_CONFIG.API_BASE_URL}/ai/knowledge/${encodeURIComponent(department)}`,

  aiKnowledgeIngest: () =>
        `${window.APP_CONFIG.API_BASE_URL}/ai/knowledge/ingest`,

  needResolve: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/${id}/resolve`,

  needsMap: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/needs/map`, params),

  needsDashboard: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/dashboard`,

  needsLookups: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/lookups`,

  needsAdminUnits: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/needs/admin-units`, params),

  needsExport: (params = {}) =>
    window.APP_API.withQuery(`${window.APP_CONFIG.API_BASE_URL}/needs/export`, params),

  needsLookupsManage: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/lookups/manage`,

  needsLookupUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/lookups/manage/${id}`,

  needsSectorStore: () =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/sectors`,

  needsSectorUpdate: (id) =>
    `${window.APP_CONFIG.API_BASE_URL}/needs/sectors/${id}`,
};