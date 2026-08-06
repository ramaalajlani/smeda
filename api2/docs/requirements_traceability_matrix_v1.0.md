# Requirements Traceability Matrix (RTM) v1.0

## Project
SMEDC Integrated Services Platform - Laravel 11

## Purpose
This matrix maps Functional Requirements (FR) to:
- API endpoint(s)
- Authorization policy/scope control
- Test case reference (existing or required)

---

## Matrix

| FR ID | Requirement Summary | API Endpoint(s) | Policy / Scope | Test Case Reference |
|---|---|---|---|---|
| FR-AUTH-001 | User login/register/logout | `POST /api/register`, `POST /api/login`, `POST /api/logout` | `auth:sanctum`, throttles | `tests/Feature/SecurityHardeningFixesTest.php` (related), manual auth smoke |
| FR-AUTH-002 | Self profile read/update | `GET /api/me`, `PUT /api/me` | authenticated user context | `tests/Feature/AdminAccessManagementTest.php` (related), manual |
| FR-AUTHZ-001 | Server-side auth for sensitive actions | Multiple mutation endpoints | Policies + middleware (`role_or_permission`, `permission`) | `tests/Feature/RoleAccessIsolationTest.php` |
| FR-IDOR-001 | Block cross-scope access to needs | `GET/PUT/POST /api/needs/{id}/*` | `NeedPolicy`, `NeedDataScope` | `tests/Feature/BranchIsolationTest.php`, `tests/Feature/NeedsModuleTest.php` |
| FR-IDOR-002 | Block cross-scope funding app access | `GET/PUT/POST /api/finance/applications/{id}/*` | `FundingApplicationPolicy`, `FinanceDataScope` | `tests/Feature/Security/FinanceBranchOfficerIsolationTest.php` |
| FR-IDOR-003 | Block consulting cross-branch mutation | `POST /api/consulting/requests/{id}/sort`, `/transfer` | `ConsultingRequestPolicy`, `ConsultingDataScope` | `tests/Feature/Security/ConsultingCrossBranchMutationTest.php` |
| FR-NEED-001 | Need workflow actions available by role | `/api/needs/{id}/review|approve|reject|return|classify|resolve` | `NeedPolicy` | `tests/Feature/NeedsModuleTest.php` |
| FR-NEED-002 | Need transitions are controlled | same as above | `StatusTransitionValidator` (Need rules) | `tests/Feature/NeedsModuleTest.php` + required transition-negative tests |
| FR-NEED-003 | Need status changes are transactional | Need workflow service methods | `DB::transaction`, `lockForUpdate` | required concurrency tests |
| FR-NEED-004 | Need code uniqueness under concurrency | Need creation endpoint | `NeedCodeGenerator` with lock in txn | required concurrency tests |
| FR-FIN-001 | Funding app lifecycle control | `/api/finance/applications/{id}/submit|branch-review|approve|reject` | `FundingApplicationPolicy`, transition validator | `tests/Feature/FundingPlatformTest.php` |
| FR-FIN-002 | Funding assignments policy/scoped | `/api/finance/applications/{id}/assign-consultant|assign-partner` | `FundingApplicationPolicy`, `FinanceDataScope` | `tests/Feature/FundingInstitutionalPartnersTest.php` |
| FR-FIN-003 | Loan creation eligibility | `POST /api/finance/applications/{id}/create-loan` | service state checks + policy | `tests/Feature/FundingPlatformTest.php` |
| FR-FIN-004 | Loan number collision protection | create loan operation | transactional number generation | required concurrency tests |
| FR-CON-001 | Consulting request lifecycle | `/api/consulting/requests/{id}/submit|sort|accept-offer|transfer` | `ConsultingRequestPolicy`, transition validator | `tests/Feature/Security/ConsultingCrossBranchMutationTest.php` |
| FR-CON-002 | Offer submission by valid scoped office only | `POST /api/consulting/requests/{id}/offers` | `ConsultingOfferController` scope checks | required dedicated offer scope test |
| FR-CON-003 | Consulting request view scoped | `GET /api/consulting/requests/{id}` | `ConsultingRequestPolicy::view` | `tests/Feature/Security/ConsultingCrossBranchMutationTest.php` (partial), required read tests |
| FR-HIST-001 | Persist status history (Need) | Need state actions | `StatusHistoryService` | required: assert `status_histories` entries |
| FR-HIST-002 | Persist status history (FundingApplication) | funding state actions | `StatusHistoryService` | required: assert `status_histories` entries |
| FR-HIST-003 | Persist status history (ConsultingRequest) | consulting state actions | `StatusHistoryService` | required: assert `status_histories` entries |
| FR-NOT-001 | Notification summary endpoint | `GET /api/notifications/summary` | authenticated user | manual + required API test |
| FR-NOT-002 | Group unread count in summary | `GET /api/notifications/summary` | `NotificationSummaryService` | required API contract test |
| FR-NOT-003 | Mark read/all read operations | `POST /api/notifications/{id}/read`, `/read-all` | user-owned notification filter | required notification feature tests |
| FR-NOT-004 | Inbox unread count and messaging | `/api/inbox/*` | authenticated and role-limited | manual + required inbox tests |
| FR-TRN-001 | Course/registration actions authorization | `/api/training-courses/*`, registration request endpoints | training policies + permissions | `tests/Feature/TrainingBackendHardeningTest.php`, `tests/Feature/TrainingArchitectureHardeningTest.php` |
| FR-TRN-002 | Certificate verification public access | `/api/certificates/verify`, `/api/verify-certificate/{code}` | throttle + input constraints | `tests/Feature/Security/CertificateVerifyPublicTest.php` |
| FR-INC-001 | Incubation security boundaries | `/api/incubation/*`, `/api/incubators/*` | incubation access rules/policies | `tests/Feature/Security/IncubationSecurityTest.php` |
| FR-WRK-001 | Workforce endpoint authorization | `/api/workforce/*`, `/api/workforces/*` | permission middleware | manual + required workforce feature tests |
| FR-ADM-001 | Admin role/permission/user management authorization | `/api/admin/users/*`, `/api/admin/roles/*`, `/api/admin/permissions/*` | admin middleware + `UserAccessPolicy` etc | `tests/Feature/AdminAccessManagementTest.php` |

---

## Notes for QA

1. **Priority P0 coverage**  
   Must include authorization, IDOR, status transition validation, and status history insertion assertions.

2. **Priority P1 coverage**  
   Must include notification summary contract, group badge logic inputs, and polling behavior verification.

3. **Gap items to add as automated tests**
- Negative transition tests for each critical status workflow.
- Concurrency tests for `need_code` and loan number generation.
- Assertions that each sensitive transition creates exactly one `status_histories` row.

---

## Change Log
- v1.0: Initial baseline mapping for current route/policy structure.

