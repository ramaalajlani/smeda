# SMEDA API Coverage

Generated from `php artisan route:list --path=api --json`.

| Method | Endpoint | Controller | Action | Auth | Role/Permission | Postman Folder | Added |
|---|---|---|---|---|---|---|---|
| GET | /api/admin/access-summary | Admin\AccessSummaryController | — | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/activity-logs | Admin\ActivityLogController | index | Yes | role_or_permission:auditor\|admin\|super_admin\|system_admin\|general_director\|view_audit\|manage_user_access | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/activity-logs/export | Admin\ActivityLogController | export | Yes | role_or_permission:auditor\|admin\|super_admin\|system_admin\|general_director\|view_audit\|manage_user_access | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/activity-logs/{id} | Admin\ActivityLogController | show | Yes | role_or_permission:auditor\|admin\|super_admin\|system_admin\|general_director\|view_audit\|manage_user_access | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/my-children | Admin\UserAccessController | myChildren | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/my-delegatable | Admin\UserAccessController | delegatableOptions | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/permissions | Admin\PermissionController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/permissions | Admin\PermissionController | store | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/permissions/{id} | Admin\PermissionController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/admin/permissions/{id} | Admin\PermissionController | update | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/admin/permissions/{id} | Admin\PermissionController | destroy | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/roles | Admin\RoleController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/roles | Admin\RoleController | store | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/roles/{id} | Admin\RoleController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/admin/roles/{id} | Admin\RoleController | update | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/admin/roles/{id} | Admin\RoleController | destroy | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/roles/{id}/permissions | Admin\RoleController | syncPermissions | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/admin/roles/{id}/permissions/{permissionId} | Admin\RoleController | detachPermission | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/users | Admin\UserAccessController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users | Admin\UserAccessController | store | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/users/{id} | Admin\UserAccessController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/admin/users/{id} | Admin\UserAccessController | update | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/users/{id}/access | Admin\UserAccessController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/users/{id}/activity-logs | Admin\ActivityLogController | forUser | Yes | role_or_permission:auditor\|admin\|super_admin\|system_admin\|general_director\|view_audit\|manage_user_access | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users/{id}/change-password | Admin\UserAccessController | changePassword | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/admin/users/{id}/children | Admin\UserAccessController | childrenOf | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/admin/users/{id}/parent | Admin\UserAccessController | reassignParent | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users/{id}/permissions | Admin\UserAccessController | assignPermission | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users/{id}/permissions/sync | Admin\UserAccessController | syncPermissions | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/admin/users/{id}/permissions/{permission} | Admin\UserAccessController | revokePermission | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users/{id}/roles | Admin\UserAccessController | assignRole | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/admin/users/{id}/roles/sync | Admin\UserAccessController | syncRoles | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/admin/users/{id}/roles/{role} | Admin\UserAccessController | revokeRole | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/admin/users/{id}/status | Admin\UserAccessController | updateStatus | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/agreements | AgreementController | index | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/agreements | AgreementController | store | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/agreements/{id} | AgreementController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/agreements/{id} | AgreementController | update | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/agreements/{id}/approve | AgreementController | approve | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/branches | BranchController | index | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/branches | BranchController | store | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/branches/dashboard | BranchController | dashboard | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/branches/{id} | BranchController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/branches/{id} | BranchController | update | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/branches/{id} | BranchController | destroy | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/certificates | CertificateController | index | Yes | permission:view_certificates | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/certificates/code/{certificate_code} | CertificateController | showByCode | Yes | permission:view_certificates | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/certificates/issue | CertificateController | issue | Yes | permission:issue_certificates | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/certificates/verify | CertificateController | verify | No | Public | 01 - Public APIs | Yes |
| GET | /api/certificates/verify-page | CertificateController | verifyPage | No | Public | 01 - Public APIs | Yes |
| GET | /api/certificates/{id} | CertificateController | show | Yes | permission:view_certificates | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/certificates/{id}/approve | CertificateController | approve | Yes | role_or_permission:approve_center_certificates\|approve_training_certificates\|approve_deputy_certificates\|approve_general_director_certificates | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/categories | Closure | — | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/contracts/{id} | ConsultingContractController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/contracts/{id}/approve-report | ConsultingContractController | approveReport | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/contracts/{id}/messages | ConsultingContractController | messages | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/contracts/{id}/messages | ConsultingContractController | sendMessage | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/contracts/{id}/report | ConsultingContractController | uploadReport | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/contracts/{id}/review | ConsultingContractController | submitReview | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/contracts/{id}/sign | ConsultingContractController | sign | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/offices | ConsultingOfficeController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/offices | ConsultingOfficeController | store | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/offices/{id} | ConsultingOfficeController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/consulting/offices/{id} | ConsultingOfficeController | update | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/consulting/offices/{id} | ConsultingOfficeController | destroy | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/offices/{id}/activate | ConsultingOfficeController | activate | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/offices/{id}/suspend | ConsultingOfficeController | suspend | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/offices/{id}/violations | ConsultingOfficeController | addViolation | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|consultant_union_admin\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/requests | ConsultingRequestController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests | ConsultingRequestController | store | Yes | role_or_permission:project_owner\|admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|governor | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/requests/stats | ConsultingRequestController | stats | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/requests/{id} | ConsultingRequestController | show | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/consulting/requests/{id} | ConsultingRequestController | update | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/consulting/requests/{id} | ConsultingRequestController | destroy | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/accept-offer | ConsultingRequestController | acceptOffer | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/attachments | ConsultingRequestController | uploadAttachment | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/consulting/requests/{id}/offers | ConsultingOfferController | index | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|project_owner\|consultant_union_admin\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/offers | ConsultingOfferController | store | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/sort | ConsultingRequestController | sort | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/submit | ConsultingRequestController | submit | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/consulting/requests/{id}/transfer | ConsultingRequestController | transfer | Yes | role_or_permission:admin\|super_admin\|system_admin\|general_director\|project_services_manager\|branch_manager\|branch_officer\|governor\|consultant_union_admin\|project_owner\|consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/dashboard | DashboardController | index | Yes | dashboard.access | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/electronic-signatures/{id}/snapshot-image | UserElectronicSignatureController | snapshotImage | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/entrepreneur/my-profile | EntrepreneurProfileController | myProfile | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/entrepreneur/profile | EntrepreneurProfileController | store | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/entrepreneur/profile/{id} | EntrepreneurProfileController | update | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/entrepreneur/profiles | EntrepreneurProfileController | index | Yes | role_or_permission:general_director\|deputy_general_director\|deputy_director\|admin\|super_admin\|system_admin\|branch_manager\|incubator_manager\|entrepreneur_manager\|entrepreneur.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/entrepreneur/profiles/export | EntrepreneurProfileController | export | Yes | role_or_permission:general_director\|deputy_general_director\|deputy_director\|admin\|super_admin\|system_admin\|branch_manager\|incubator_manager\|entrepreneur_manager\|entrepreneur.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/entrepreneur/profiles/public-stats | EntrepreneurProfileController | publicStats | No | Public | 01 - Public APIs | Yes |
| GET | /api/entrepreneur/profiles/stats | EntrepreneurProfileController | stats | Yes | role_or_permission:general_director\|deputy_general_director\|deputy_director\|admin\|super_admin\|system_admin\|branch_manager\|incubator_manager\|entrepreneur_manager\|entrepreneur.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/entrepreneur/profiles/{id} | EntrepreneurProfileController | show | Yes | role_or_permission:general_director\|deputy_general_director\|deputy_director\|admin\|super_admin\|system_admin\|branch_manager\|incubator_manager\|entrepreneur_manager\|entrepreneur.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/entrepreneur/profiles/{id}/review | EntrepreneurProfileController | review | Yes | role_or_permission:general_director\|deputy_general_director\|deputy_director\|admin\|super_admin\|system_admin\|branch_manager\|incubator_manager\|entrepreneur_manager\|entrepreneur.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/applications | FundingApplicationController | index | Yes | role_or_permission:project_owner\|consultant_office\|funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|general_director\|deputy_general_director\|deputy_director\|auditor\|admin\|super_admin\|system_admin\|finance.applications.view | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications | FundingApplicationController | store | Yes | role_or_permission:project_owner\|finance.applications.create\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{applicationId}/documents | FundingDocumentController | store | Yes | role_or_permission:project_owner\|consultant_office\|funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|general_director\|deputy_general_director\|deputy_director\|auditor\|admin\|super_admin\|system_admin\|finance.applications.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/applications/{applicationId}/documents/{documentId}/download | FundingDocumentController | download | Yes | role_or_permission:project_owner\|consultant_office\|funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|general_director\|deputy_general_director\|deputy_director\|auditor\|admin\|super_admin\|system_admin\|finance.applications.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/applications/{id} | FundingApplicationController | show | Yes | role_or_permission:project_owner\|consultant_office\|funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|consultant_union_admin\|branch_manager\|branch_officer\|governor\|general_director\|deputy_general_director\|deputy_director\|auditor\|admin\|super_admin\|system_admin\|finance.applications.view | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/finance/applications/{id} | FundingApplicationController | update | Yes | role_or_permission:project_owner\|finance.applications.update\|branch_manager\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/approve | FundingApplicationController | approve | Yes | role_or_permission:finance_manager\|finance.applications.approve\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/assign-consultant | FundingApplicationController | assignConsultant | Yes | role_or_permission:branch_manager\|finance_manager\|finance.applications.assign_consultant\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/assign-partner | FundingApplicationController | assignPartner | Yes | role_or_permission:finance_manager\|central_bank_admin\|finance.applications.assign_partner\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/branch-review | FundingApplicationController | branchReview | Yes | role_or_permission:branch_manager\|finance.applications.review_branch\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/create-loan | FundingApplicationController | createLoan | Yes | role_or_permission:finance_manager\|finance.loans.manage\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/reject | FundingApplicationController | reject | Yes | role_or_permission:branch_manager\|finance_manager\|finance.applications.reject\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/request-completion | FundingApplicationController | requestCompletion | Yes | role_or_permission:branch_manager\|finance.applications.request_completion\|finance_manager\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/applications/{id}/submit | FundingApplicationController | submit | Yes | role_or_permission:project_owner\|finance.applications.submit\|general_director\|admin\|super_admin\|system_admin | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/central-bank/dashboard | FundingPartnerController | centralBankDashboard | Yes | role_or_permission:central_bank_admin\|finance.central_bank.dashboard | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/cloud | FundingMetricsController | cloud | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-assignments | FundingConsultantController | indexAssignments | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-assignments/{id}/accept | FundingConsultantController | acceptAssignment | Yes | role_or_permission:consultant_office\|finance.consultant_assignments.accept | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-assignments/{id}/approve-price | FundingConsultantController | approvePrice | Yes | role_or_permission:branch_manager\|finance_manager\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.approve_price | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-assignments/{id}/price-offer | FundingConsultantController | priceOffer | Yes | role_or_permission:consultant_office\|finance.consultant_assignments.submit_price\|finance.consultants.submit_price | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-assignments/{id}/reject | FundingConsultantController | rejectAssignment | Yes | role_or_permission:consultant_office\|finance.consultant_assignments.reject | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-office/dashboard | FundingConsultantController | officeDashboard | Yes | role:consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-offices | FundingConsultantController | indexOffices | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-offices | FundingConsultantController | storeOffice | Yes | role_or_permission:consultant_union_admin\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.create\|finance.consultants.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-offices/{id} | FundingConsultantController | showOffice | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/finance/consultant-offices/{id} | FundingConsultantController | updateOffice | Yes | role_or_permission:consultant_union_admin\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.update\|finance.consultants.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-offices/{id}/activate | FundingConsultantController | activateOffice | Yes | role_or_permission:consultant_union_admin\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.activate\|finance.consultants.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-offices/{id}/approve | FundingConsultantController | approveOffice | Yes | role_or_permission:consultant_union_admin\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.approve\|finance.consultants.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-offices/{id}/assignments | FundingConsultantController | officeAssignments | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-offices/{id}/metrics | FundingConsultantController | officeMetrics | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-offices/{id}/reports | FundingConsultantController | officeReports | Yes | role_or_permission:consultant_union_admin\|consultant_office\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.consultants.view\|finance.consultants.view_all | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-offices/{id}/suspend | FundingConsultantController | suspendOffice | Yes | role_or_permission:consultant_union_admin\|general_director\|admin\|super_admin\|system_admin\|finance.consultants.suspend\|finance.consultants.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/consultant-reports | FundingConsultantController | storeReport | Yes | role_or_permission:consultant_office\|general_director\|admin\|super_admin\|system_admin\|finance.consultant_reports.create\|finance.consultants.submit_report | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/consultant-union/dashboard | FundingConsultantController | unionDashboard | Yes | role_or_permission:consultant_union_admin\|finance.consultant_union.dashboard | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/defaulted | FundingMetricsController | defaulted | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/defaulted/stats | FundingMetricsController | defaultedStats | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/funded | FundingMetricsController | funded | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/funded/stats | FundingMetricsController | fundedStats | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/funding-partner/dashboard | FundingPartnerController | partnerDashboard | Yes | role:funding_partner | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/loans | FundedLoanController | index | Yes | role_or_permission:funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.loans.view\|finance.loans.view_own | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/loans/stats | FundedLoanController | stats | Yes | role_or_permission:funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.loans.view\|finance.loans.view_own | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/loans/{id} | FundedLoanController | show | Yes | role_or_permission:funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.loans.view\|finance.loans.view_own | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/finance/loans/{id} | FundedLoanController | update | Yes | role_or_permission:finance_manager\|funding_partner\|general_director\|admin\|super_admin\|system_admin\|finance.loans.manage\|finance.loans.update_own_status | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/loans/{id}/close | FundedLoanController | close | Yes | role_or_permission:finance_manager\|general_director\|admin\|super_admin\|system_admin\|finance.loans.close\|finance.loans.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/loans/{id}/mark-defaulted | FundedLoanController | markDefaulted | Yes | role_or_permission:finance_manager\|general_director\|admin\|super_admin\|system_admin\|finance.loans.defaulted\|finance.loans.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/loans/{id}/payments | FundedLoanController | payments | Yes | role_or_permission:funding_partner\|finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.loans.view\|finance.loans.view_own | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/loans/{id}/payments | FundedLoanController | storePayment | Yes | role_or_permission:finance_manager\|general_director\|admin\|super_admin\|system_admin\|finance.loans.payments\|finance.loans.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/manager/dashboard | FundingMetricsController | managerDashboard | Yes | role_or_permission:finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|finance.metrics.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/metrics | FundingMetricsController | metrics | Yes | role_or_permission:finance_manager\|finance_officer\|central_bank_admin\|branch_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.metrics.view\|finance.metrics.national\|finance.metrics.branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/my-consultant-assignments | FundingConsultantController | myAssignments | Yes | role:consultant_office | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/my-partner-assignments | FundingPartnerController | myAssignments | Yes | role:funding_partner | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/partner-assignments/{id}/decision | FundingPartnerController | decision | Yes | role_or_permission:funding_partner\|central_bank_admin\|finance_manager\|general_director\|admin\|super_admin\|system_admin\|finance.partner_assignments.decide\|finance.partners.decide | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners | FundingPartnerController | index | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/partners | FundingPartnerController | store | Yes | role_or_permission:central_bank_admin\|general_director\|admin\|super_admin\|system_admin\|finance.partners.create\|finance.partners.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners/{id} | FundingPartnerController | show | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/finance/partners/{id} | FundingPartnerController | update | Yes | role_or_permission:central_bank_admin\|general_director\|admin\|super_admin\|system_admin\|finance.partners.update\|finance.partners.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/partners/{id}/activate | FundingPartnerController | activatePartner | Yes | role_or_permission:central_bank_admin\|general_director\|admin\|super_admin\|system_admin\|finance.partners.activate\|finance.partners.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/partners/{id}/approve | FundingPartnerController | approvePartner | Yes | role_or_permission:central_bank_admin\|general_director\|admin\|super_admin\|system_admin\|finance.partners.approve\|finance.partners.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners/{id}/assignments | FundingPartnerController | partnerAssignments | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners/{id}/decisions | FundingPartnerController | partnerDecisions | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners/{id}/loans | FundingPartnerController | partnerLoans | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/partners/{id}/metrics | FundingPartnerController | partnerMetrics | Yes | role_or_permission:central_bank_admin\|funding_partner\|finance_manager\|finance_officer\|general_director\|admin\|super_admin\|system_admin\|auditor\|finance.partners.view\|finance.partners.view_all | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/partners/{id}/suspend | FundingPartnerController | suspendPartner | Yes | role_or_permission:central_bank_admin\|general_director\|admin\|super_admin\|system_admin\|finance.partners.suspend\|finance.partners.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/records | FinancialRecordController | index | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|finance_manager\|finance_officer\|central_bank_admin\|auditor\|admin\|super_admin\|system_admin\|view_finance\|manage_finance | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/records | FinancialRecordController | store | Yes | role_or_permission:general_director\|finance_manager\|admin\|super_admin\|system_admin\|manage_finance | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/finance/records/{id} | FinancialRecordController | show | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|finance_manager\|finance_officer\|central_bank_admin\|auditor\|admin\|super_admin\|system_admin\|view_finance\|manage_finance | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/finance/records/{id} | FinancialRecordController | update | Yes | role_or_permission:general_director\|finance_manager\|admin\|super_admin\|system_admin\|manage_finance | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/finance/records/{id}/approve | FinancialRecordController | approve | Yes | role_or_permission:general_director\|finance_manager\|admin\|super_admin\|system_admin\|manage_finance | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/governorates | GovernorateController | index | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/inbox | InboxController | inbox | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/inbox | InboxController | store | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/inbox/sent | InboxController | sent | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/inbox/unread-count | InboxController | unreadCount | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/inbox/users-list | InboxController | usersList | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/inbox/{id} | InboxController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/inbox/{id} | InboxController | destroy | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/inbox/{id}/reply | InboxController | reply | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/applications | IncubatorController | allApplications | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/applications/{id} | IncubatorController | showApplication | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/incubation/applications/{id}/review | IncubatorController | reviewApplication | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|incubation.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/incubation/apply | IncubatorController | apply | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/my-applications | IncubatorController | myApplications | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/my-project | IncubatorController | myProject | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/my-sessions | IncubatorController | myMentoringSessions | Yes | role_or_permission:incubator_mentor\|incubator_manager\|admin\|super_admin\|system_admin\|incubation.mentor | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/projects | IncubatorController | projects | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/projects/{id} | IncubatorController | showProject | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/incubation/projects/{id} | IncubatorController | updateProject | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|incubation.manage | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/incubation/projects/{id}/reports | IncubatorController | storeProgressReport | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/incubation/projects/{id}/sessions | IncubatorController | storeMentoringSession | Yes | role_or_permission:incubator_mentor\|incubator_manager\|admin\|super_admin\|system_admin\|incubation.mentor | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/sessions | IncubatorController | indexMentoringSessions | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubation/stats | IncubatorController | stats | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubators | IncubatorController | index | No | Public | 01 - Public APIs | Yes |
| POST | /api/incubators | IncubatorController | store | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|incubation.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubators/{id} | IncubatorController | show | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/incubators/{id} | IncubatorController | update | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|incubation.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/incubators/{id}/applications | IncubatorController | applications | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|branch_officer\|incubator_manager\|incubator_mentor\|admin\|super_admin\|system_admin\|auditor\|incubation.view | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/incubators/{id}/programs | IncubatorController | storeProgram | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|incubation.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/locations/communities | SyriaLocationController | communities | No | Public | 01 - Public APIs | Yes |
| GET | /api/locations/districts | SyriaLocationController | districts | No | Public | 01 - Public APIs | Yes |
| GET | /api/locations/governorates | SyriaLocationController | governorates | No | Public | 01 - Public APIs | Yes |
| GET | /api/locations/map | SyriaLocationController | mapPoints | No | Public | 01 - Public APIs | Yes |
| GET | /api/locations/search | SyriaLocationController | search | No | Public | 01 - Public APIs | Yes |
| GET | /api/locations/subdistricts | SyriaLocationController | subdistricts | No | Public | 01 - Public APIs | Yes |
| POST | /api/login | AuthController | login | No | Public | 00 - Authentication | Yes |
| POST | /api/logout | AuthController | logout | Yes | auth:sanctum (controller/policy may authorize further) | 00 - Authentication | Yes |
| GET | /api/map/trainees | TrainingMapController | trainees | Yes | permission:view_trainees | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/map/trainers | TrainingMapController | trainers | Yes | permission:view_trainers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/map/training-centers | TrainingMapController | centers | No | Public | 01 - Public APIs | Yes |
| GET | /api/map/training-courses | TrainingMapController | courses | Yes | permission:view_courses | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/me | AuthController | me | Yes | auth:sanctum (controller/policy may authorize further) | 00 - Authentication | Yes |
| PUT | /api/me | AuthController | updateMe | Yes | auth:sanctum (controller/policy may authorize further) | 00 - Authentication | Yes |
| POST | /api/me/change-password | AuthController | changeMyPassword | Yes | auth:sanctum (controller/policy may authorize further) | 00 - Authentication | Yes |
| GET | /api/my-electronic-signature | UserElectronicSignatureController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/my-electronic-signature | UserElectronicSignatureController | store | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/my-electronic-signature | UserElectronicSignatureController | destroy | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/my-electronic-signature/image | UserElectronicSignatureController | myImage | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/my-trainer-profile | TrainerProfileController | myProfile | Yes | role_or_permission:view_trainer_profiles\|edit_own_trainer_profile | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/my-trainer-profile | TrainerProfileController | updateMyProfile | Yes | permission:edit_own_trainer_profile | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs | NeedController | index | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs | NeedController | store | Yes | role_or_permission:needs.create\|needs.create_citizen\|needs.create_state | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/admin-units | NeedController | adminUnits | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/ai-suggest | NeedController | aiSuggest | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/analytics | NeedController | analytics | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/dashboard | NeedController | dashboard | Yes | permission:needs.dashboard | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/export | NeedController | export | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/lookups | NeedController | lookups | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/lookups/manage | NeedLookupAdminController | index | Yes | permission:needs.manage_lookups | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/lookups/manage | NeedLookupAdminController | storeLookup | Yes | permission:needs.manage_lookups | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/needs/lookups/manage/{id} | NeedLookupAdminController | updateLookup | Yes | permission:needs.manage_lookups | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/map | NeedController | map | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/sectors | NeedLookupAdminController | storeSector | Yes | permission:needs.manage_lookups | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/needs/sectors/{id} | NeedLookupAdminController | updateSector | Yes | permission:needs.manage_lookups | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/workspace/data-entry | NeedController | dataEntryWorkspace | Yes | role:data_entry | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/workspace/reviewer | NeedController | reviewerWorkspace | Yes | role:data_reviewer | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/needs/{id} | NeedController | show | Yes | role_or_permission:needs.view\|needs.view_all\|needs.view_branch | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/needs/{id} | NeedController | update | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/ai-suggest | NeedController | aiSuggestForNeed | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/approve | NeedController | approve | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/classify | NeedController | classify | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/reject | NeedController | reject | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/resolve | NeedController | resolve | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/return | NeedController | returnForEdit | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/needs/{id}/review | NeedController | review | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/news | NewsController | index | No | Public | 01 - Public APIs | Yes |
| POST | /api/news | NewsController | store | Yes | role_or_permission:media_manager\|general_director\|admin\|super_admin\|system_admin\|news.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/news/stats | NewsController | stats | Yes | role_or_permission:media_manager\|general_director\|admin\|super_admin\|system_admin\|news.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/news/{id} | NewsController | show | No | Public | 01 - Public APIs | Yes |
| PUT | /api/news/{id} | NewsController | update | Yes | role_or_permission:media_manager\|general_director\|admin\|super_admin\|system_admin\|news.manage | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/news/{id} | NewsController | destroy | Yes | role_or_permission:media_manager\|general_director\|admin\|super_admin\|system_admin\|news.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/notifications | NotificationController | index | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/notifications/read-all | NotificationController | markAllRead | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/notifications/summary | NotificationController | summary | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/notifications/{id} | NotificationController | destroy | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/notifications/{id}/read | NotificationController | markRead | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/program-bank | ProgramBankController | index | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|program_bank.view\|view_programs\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank | ProgramBankController | store | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.create\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/program-bank/reports | ProgramBankController | reports | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|program_bank.reports\|program_bank.view\|view_reports | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/program-bank/stats | ProgramBankController | stats | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|program_bank.view\|view_programs\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/program-bank/{id} | ProgramBankController | show | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|auditor\|program_bank.view\|view_programs\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/program-bank/{id} | ProgramBankController | update | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/program-bank/{id} | ProgramBankController | destroy | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.delete\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank/{id}/create-course | ProgramBankController | createCourseFromProgram | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.create\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank/{id}/duplicate | ProgramBankController | duplicate | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.create\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank/{id}/modules | ProgramBankController | storeModule | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/program-bank/{id}/modules/reorder | ProgramBankController | reorderModules | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/program-bank/{id}/modules/{moduleId} | ProgramBankController | updateModule | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/program-bank/{id}/modules/{moduleId} | ProgramBankController | destroyModule | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank/{id}/outcomes | ProgramBankController | storeOutcome | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/program-bank/{id}/outcomes/{outcomeId} | ProgramBankController | updateOutcome | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/program-bank/{id}/outcomes/{outcomeId} | ProgramBankController | destroyOutcome | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/program-bank/{id}/service-links | ProgramBankController | syncServiceLinks | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|program_bank.update\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/program-bank/{id}/transition | ProgramBankController | transition | Yes | role_or_permission:training_manager\|general_director\|admin\|super_admin\|system_admin\|deputy_director\|program_bank.approve\|manage_programs | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/public/finance/cloud | PublicBrowseController | financeCloud | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/finance/metrics | PublicBrowseController | financeMetrics | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/governorates | PublicBrowseController | governorates | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/job-postings | PublicBrowseController | jobPostings | No | Public | 01 - Public APIs | Yes |
| POST | /api/public/needs | PublicBrowseController | storeGuestNeed | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/needs/lookups | PublicBrowseController | needsLookups | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/needs/map | PublicBrowseController | needsMap | No | Public | 01 - Public APIs | Yes |
| GET | /api/public/training-programs | PublicBrowseController | trainingPrograms | No | Public | 01 - Public APIs | Yes |
| POST | /api/register | AuthController | register | No | Public | 00 - Authentication | Yes |
| GET | /api/registration-requests/centers | TrainingCenterRegistrationRequestController | index | Yes | permission:view_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/centers | TrainingCenterRegistrationRequestController | store | Yes | permission:create_center_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/centers/{id} | TrainingCenterRegistrationRequestController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/centers/{id}/review | TrainingCenterRegistrationRequestController | review | Yes | permission:review_center_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/courses | CourseRegistrationRequestController | index | Yes | role_or_permission:view_registration_requests\|create_course_registration_requests\|confirm_course_registration_requests\|complete_course_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/courses | CourseRegistrationRequestController | store | Yes | permission:create_course_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/courses/{id} | CourseRegistrationRequestController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/courses/{id}/cancel | CourseRegistrationRequestController | cancel | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/courses/{id}/complete | CourseRegistrationRequestController | complete | Yes | permission:complete_course_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/courses/{id}/confirm-by-guardian | CourseRegistrationRequestController | confirmByGuardian | Yes | permission:confirm_course_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/trainees | TraineeRegistrationRequestController | index | Yes | permission:view_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/trainees | TraineeRegistrationRequestController | store | Yes | permission:create_trainee_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/trainees/{id} | TraineeRegistrationRequestController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/trainees/{id}/review | TraineeRegistrationRequestController | review | Yes | permission:review_trainee_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/trainers | TrainerRegistrationRequestController | index | Yes | permission:view_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/trainers | TrainerRegistrationRequestController | store | Yes | permission:create_trainer_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/registration-requests/trainers/{id} | TrainerRegistrationRequestController | show | Yes | auth:sanctum (controller/policy may authorize further) | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/registration-requests/trainers/{id}/review | TrainerRegistrationRequestController | review | Yes | permission:review_trainer_registration_requests | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/signatures/verify/{code} | ExecutiveSignatureController | verify | No | Public | 01 - Public APIs | Yes |
| GET | /api/success-stories | SuccessStoryController | index | No | Public | 01 - Public APIs | Yes |
| POST | /api/success-stories | SuccessStoryController | store | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|branch_manager\|story.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/success-stories/slug/{slug} | SuccessStoryController | showBySlug | No | Public | 01 - Public APIs | Yes |
| GET | /api/success-stories/stats | SuccessStoryController | stats | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|branch_manager\|story.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/success-stories/{id} | SuccessStoryController | show | No | Public | 01 - Public APIs | Yes |
| PUT | /api/success-stories/{id} | SuccessStoryController | update | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|branch_manager\|story.manage | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/success-stories/{id} | SuccessStoryController | destroy | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|incubator_manager\|branch_manager\|story.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/trainees | TraineeController | index | Yes | permission:view_trainees | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/trainees | TraineeController | store | Yes | permission:view_trainees+manage_trainees | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/trainees/{id} | TraineeController | show | Yes | permission:view_trainees | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/trainees/{id} | TraineeController | update | Yes | permission:view_trainees+manage_trainees | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/trainees/{id} | TraineeController | update | Yes | permission:view_trainees+manage_trainees | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/trainer-profiles/{id} | TrainerProfileController | show | Yes | permission:view_trainer_profiles | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/trainers | TrainerController | index | Yes | permission:view_trainers | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/trainers | TrainerController | store | Yes | permission:view_trainers+manage_trainers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/trainers/{id} | TrainerController | show | Yes | permission:view_trainers | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/trainers/{id} | TrainerController | update | Yes | permission:view_trainers+manage_trainers | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/trainers/{id} | TrainerController | update | Yes | permission:view_trainers+manage_trainers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-centers | TrainingCenterController | index | Yes | permission:view_centers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-centers/{id} | TrainingCenterController | show | Yes | permission:view_centers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses | TrainingCourseController | index | Yes | role_or_permission:trainer_user\|trainee_user\|view_courses | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses | TrainingCourseController | store | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id} | TrainingCourseController | show | Yes | role_or_permission:trainer_user\|trainee_user\|view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/training-courses/{id} | TrainingCourseController | update | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/training-courses/{id} | TrainingCourseController | destroy | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/complete | TrainingCourseController | complete | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/groups | CourseGroupController | index | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/groups | CourseGroupController | store | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/training-courses/{id}/groups/{groupId} | CourseGroupController | destroy | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/groups/{groupId}/assign | CourseGroupController | assign | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/groups/{groupId}/remove | CourseGroupController | remove | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/groups/{groupId}/trainees | CourseGroupController | trainees | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/issue-certificates | CertificateController | issueForCourse | Yes | permission:issue_certificates | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/module-scores | ModuleScoreController | index | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/module-scores | ModuleScoreController | store | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/modules | TrainingCourseController | modules | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/sessions | CourseSessionController | index | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/sessions | CourseSessionController | store | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/sessions/{sessionId}/attendance | CourseSessionController | attendanceIndex | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/sessions/{sessionId}/attendance | CourseSessionController | attendanceStore | Yes | role_or_permission:manage_courses\|manage_trainees\|trainer_user | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/trainees | TrainingCourseController | trainees | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-courses/{id}/trainees | TrainingCourseController | addTrainee | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/training-courses/{id}/trainees/{traineeId} | TrainingCourseController | updateTrainee | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/training-courses/{id}/trainees/{traineeId} | TrainingCourseController | removeTrainee | Yes | permission:manage_courses | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-courses/{id}/ungrouped-trainees | CourseGroupController | ungrouped | Yes | role_or_permission:view_courses\|view_course_details | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-kit-nominations | TrainingKitNominationController | index | Yes | role_or_permission:nominate_training_kits\|review_training_kit_nominations | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-kit-nominations | TrainingKitNominationController | store | Yes | permission:nominate_training_kits | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-kit-nominations/{id} | TrainingKitNominationController | show | Yes | role_or_permission:nominate_training_kits\|review_training_kit_nominations | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-kit-nominations/{id}/review | TrainingKitNominationController | review | Yes | permission:review_training_kit_nominations | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-kit-public-requests | TrainingKitPublicRequestController | store | No | Public | 01 - Public APIs | Yes |
| GET | /api/training-kits | TrainingKitController | index | Yes | permission:view_kits | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-kits | TrainingKitController | store | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-kits/{id} | TrainingKitController | show | Yes | permission:view_kits | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/training-kits/{id} | TrainingKitController | update | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| PATCH | /api/training-kits/{id} | TrainingKitController | update | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-kits/{id}/materials | KitMaterialController | index | Yes | permission:view_kits | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/training-kits/{id}/materials | KitMaterialController | store | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/training-kits/{id}/materials/{materialId} | KitMaterialController | update | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| DELETE | /api/training-kits/{id}/materials/{materialId} | KitMaterialController | destroy | Yes | permission:view_kits+manage_kits | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-programs | TrainingProgramController | index | Yes | permission:view_programs | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-programs/{id} | TrainingProgramController | show | Yes | permission:view_programs | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/training-supervisors | TrainingSupervisorController | index | Yes | permission:view_centers | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/verify-certificate/{certificate_code} | CertificateController | verifyByCode | No | Public | 01 - Public APIs | Yes |
| GET | /api/workforce/job-applications | JobApplicationController | index | Yes | permission:workforce.applications.view | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/workforce/job-applications | JobApplicationController | store | Yes | permission:workforce.applications.create | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/workforce/job-applications/{id} | JobApplicationController | update | Yes | permission:workforce.applications.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/workforce/job-postings | JobPostingController | index | Yes | permission:workforce.jobs.view | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/workforce/job-postings | JobPostingController | store | Yes | permission:workforce.jobs.create | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/workforce/job-postings/{id} | JobPostingController | show | Yes | permission:workforce.jobs.view | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/workforce/job-postings/{id} | JobPostingController | update | Yes | permission:workforce.jobs.manage | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/workforce/staff-training-requests | StaffTrainingRequestController | index | Yes | permission:workforce.training_requests.view | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/workforce/staff-training-requests | StaffTrainingRequestController | store | Yes | permission:workforce.training_requests.create | 99 - Full Catalog / Actor folders | Yes |
| PUT | /api/workforce/staff-training-requests/{id} | StaffTrainingRequestController | update | Yes | permission:workforce.training_requests.view | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/workforces | WorkforceController | index | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|auditor\|admin\|super_admin\|system_admin\|training_manager\|development_manager\|workforce_manager | 99 - Full Catalog / Actor folders | Yes |
| POST | /api/workforces/enroll | WorkforceController | enroll | Yes | role_or_permission:general_director\|admin\|super_admin\|system_admin\|training_manager | 99 - Full Catalog / Actor folders | Yes |
| GET | /api/workforces/{id} | WorkforceController | show | Yes | role_or_permission:general_director\|deputy_general_director\|branch_manager\|auditor\|admin\|super_admin\|system_admin\|training_manager\|development_manager\|workforce_manager | 99 - Full Catalog / Actor folders | Yes |

---

## Summary

Total API routes found: **368** (method + path; HEAD excluded)

Total API routes added to Postman: **368** (via Public + Full Catalog; Actor folders contain role-filtered subsets)

Postman request items (including per-actor copies + auth helpers): **3964**

Missing routes: **none** (all discovered `/api` routes are in Public or Full Catalog)

Duplicate routes: Actor folders intentionally re-list endpoints the role can access; canonical unique set is Public + `99 - Full Catalog`.

Routes with unclear permissions (auth:sanctum only — further checks in controller/policy): **58**

- `GET /api/agreements` → App\Http\Controllers\Api\AgreementController@index
- `POST /api/agreements` → App\Http\Controllers\Api\AgreementController@store
- `GET /api/agreements/{id}` → App\Http\Controllers\Api\AgreementController@show
- `PUT /api/agreements/{id}` → App\Http\Controllers\Api\AgreementController@update
- `POST /api/agreements/{id}/approve` → App\Http\Controllers\Api\AgreementController@approve
- `GET /api/branches` → App\Http\Controllers\Api\BranchController@index
- `POST /api/branches` → App\Http\Controllers\Api\BranchController@store
- `GET /api/branches/dashboard` → App\Http\Controllers\Api\BranchController@dashboard
- `GET /api/branches/{id}` → App\Http\Controllers\Api\BranchController@show
- `PUT /api/branches/{id}` → App\Http\Controllers\Api\BranchController@update
- `DELETE /api/branches/{id}` → App\Http\Controllers\Api\BranchController@destroy
- `GET /api/dashboard` → App\Http\Controllers\Api\DashboardController@index
- `GET /api/electronic-signatures/{id}/snapshot-image` → App\Http\Controllers\Api\UserElectronicSignatureController@snapshotImage
- `GET /api/entrepreneur/my-profile` → App\Http\Controllers\Api\EntrepreneurProfileController@myProfile
- `POST /api/entrepreneur/profile` → App\Http\Controllers\Api\EntrepreneurProfileController@store
- `PUT /api/entrepreneur/profile/{id}` → App\Http\Controllers\Api\EntrepreneurProfileController@update
- `GET /api/governorates` → App\Http\Controllers\Api\GovernorateController@index
- `GET /api/inbox` → App\Http\Controllers\Api\InboxController@inbox
- `POST /api/inbox` → App\Http\Controllers\Api\InboxController@store
- `GET /api/inbox/sent` → App\Http\Controllers\Api\InboxController@sent
- `GET /api/inbox/unread-count` → App\Http\Controllers\Api\InboxController@unreadCount
- `GET /api/inbox/users-list` → App\Http\Controllers\Api\InboxController@usersList
- `GET /api/inbox/{id}` → App\Http\Controllers\Api\InboxController@show
- `DELETE /api/inbox/{id}` → App\Http\Controllers\Api\InboxController@destroy
- `POST /api/inbox/{id}/reply` → App\Http\Controllers\Api\InboxController@reply
- `GET /api/incubation/applications/{id}` → App\Http\Controllers\Api\IncubatorController@showApplication
- `POST /api/incubation/apply` → App\Http\Controllers\Api\IncubatorController@apply
- `GET /api/incubation/my-applications` → App\Http\Controllers\Api\IncubatorController@myApplications
- `GET /api/incubation/my-project` → App\Http\Controllers\Api\IncubatorController@myProject
- `POST /api/incubation/projects/{id}/reports` → App\Http\Controllers\Api\IncubatorController@storeProgressReport
- `POST /api/logout` → App\Http\Controllers\Api\AuthController@logout
- `GET /api/me` → App\Http\Controllers\Api\AuthController@me
- `PUT /api/me` → App\Http\Controllers\Api\AuthController@updateMe
- `POST /api/me/change-password` → App\Http\Controllers\Api\AuthController@changeMyPassword
- `GET /api/my-electronic-signature` → App\Http\Controllers\Api\UserElectronicSignatureController@show
- `POST /api/my-electronic-signature` → App\Http\Controllers\Api\UserElectronicSignatureController@store
- `DELETE /api/my-electronic-signature` → App\Http\Controllers\Api\UserElectronicSignatureController@destroy
- `GET /api/my-electronic-signature/image` → App\Http\Controllers\Api\UserElectronicSignatureController@myImage
- `POST /api/needs/ai-suggest` → App\Http\Controllers\Api\NeedController@aiSuggest
- `GET /api/needs/analytics` → App\Http\Controllers\Api\NeedController@analytics
- `PUT /api/needs/{id}` → App\Http\Controllers\Api\NeedController@update
- `POST /api/needs/{id}/ai-suggest` → App\Http\Controllers\Api\NeedController@aiSuggestForNeed
- `POST /api/needs/{id}/approve` → App\Http\Controllers\Api\NeedController@approve
- `POST /api/needs/{id}/classify` → App\Http\Controllers\Api\NeedController@classify
- `POST /api/needs/{id}/reject` → App\Http\Controllers\Api\NeedController@reject
- `POST /api/needs/{id}/resolve` → App\Http\Controllers\Api\NeedController@resolve
- `POST /api/needs/{id}/return` → App\Http\Controllers\Api\NeedController@returnForEdit
- `POST /api/needs/{id}/review` → App\Http\Controllers\Api\NeedController@review
- `GET /api/notifications` → App\Http\Controllers\Api\NotificationController@index
- `POST /api/notifications/read-all` → App\Http\Controllers\Api\NotificationController@markAllRead
- `GET /api/notifications/summary` → App\Http\Controllers\Api\NotificationController@summary
- `DELETE /api/notifications/{id}` → App\Http\Controllers\Api\NotificationController@destroy
- `POST /api/notifications/{id}/read` → App\Http\Controllers\Api\NotificationController@markRead
- `GET /api/registration-requests/centers/{id}` → App\Http\Controllers\Api\TrainingCenterRegistrationRequestController@show
- `GET /api/registration-requests/courses/{id}` → App\Http\Controllers\Api\CourseRegistrationRequestController@show
- `POST /api/registration-requests/courses/{id}/cancel` → App\Http\Controllers\Api\CourseRegistrationRequestController@cancel
- `GET /api/registration-requests/trainees/{id}` → App\Http\Controllers\Api\TraineeRegistrationRequestController@show
- `GET /api/registration-requests/trainers/{id}` → App\Http\Controllers\Api\TrainerRegistrationRequestController@show


## Non-API (excluded)

Signed print/PDF routes under `routes/web.php` (certificates/cards) are not under `/api` and were excluded from this collection.
