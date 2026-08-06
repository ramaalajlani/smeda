# Software Requirements Specification (SRS)

## Project
SMEDC Integrated Services Platform - Laravel 11

## Document Control
- Version: `1.0`
- Status: `For Review`
- Date: `2026-07-11`
- Language: Arabic (with technical English terms)
- Reference Standard: IEEE 29148 style

---

## 1. Introduction

### 1.1 Purpose
This document defines the official software requirements for the SMEDC platform, covering backend APIs, frontend integration behavior, role-based authorization, data scope controls, workflow rules, and operational quality requirements.  
It is intended to be the single source of truth for development, QA, security hardening, and release acceptance.

### 1.2 Scope
The platform provides centralized digital services for multi-role public and institutional users across multiple domains:
- Access and authorization management
- Needs (GIS) lifecycle
- Funding and loans lifecycle
- Consulting requests and contracts
- Training operations and certifications
- Incubation and mentoring flows
- Workforce and job modules
- Notifications and internal inbox
- Administrative monitoring and audit visibility

### 1.3 Stakeholders
- Product owner and service owners
- Backend engineering team
- Frontend engineering team
- QA / testing team
- Security / audit team
- Operations / DevOps

### 1.4 Definitions
- RBAC: Role-Based Access Control
- IDOR: Insecure Direct Object Reference
- Scope Control: Restricting data access by branch/governorate/institution ownership
- Status Transition: Controlled state change rule for workflow entities
- Status History: Immutable log of sensitive status changes

---

## 2. Overall Description

### 2.1 Product Perspective
The platform is implemented as:
- Laravel 11 backend with REST JSON APIs under `/api/*`
- Sanctum token authentication for protected endpoints
- Frontend pages under `/front/*` consuming backend APIs
- Role and permission controls using policy and middleware pattern

### 2.2 User Roles (Examples)
System roles include, but are not limited to:
- Platform administration: `admin`, `super_admin`, `system_admin`, `general_director`, `deputy_general_director`
- Regional governance: `branch_manager`, `branch_officer`, `governor`
- Finance: `finance_manager`, `finance_officer`, `central_bank_admin`, `funding_partner`
- Consulting: `consultant_union_admin`, `consultant_office`, `project_owner`
- Training and incubation: `training_manager`, `incubator_manager`, `incubator_mentor`
- Oversight: `auditor`

### 2.3 Operating Environment
- PHP 8.2+
- Laravel 11
- MySQL/MariaDB
- Local/public storage for file handling
- Browser-based frontend (desktop-first, responsive behavior)

### 2.4 Constraints
- No WebSocket requirement in current phase
- Notification updates use polling (20-30 seconds)
- No mandatory Redis/queue migration in this phase
- Avoid large-scale refactor unless formally approved

### 2.5 Assumptions
- Seeded baseline roles/permissions are available
- Frontend API base configuration is valid for target environment
- Database migrations are executed in release process

---

## 3. Functional Requirements

## 3.1 Authentication and Session
- The system shall support user register/login/logout APIs.
- The system shall support authenticated self-profile retrieval and update.
- The system shall support self-password change for authenticated users.
- The system shall enforce throttling for authentication and sensitive endpoints.

## 3.2 Authorization (Server-Side)
- All sensitive actions (`show/update/delete/approve/reject/status-change`) shall be authorized server-side.
- Authorization shall not depend on frontend button visibility.
- Policies and middleware shall enforce role/permission checks for protected modules.

## 3.3 IDOR and Data Scope Protection
- Any endpoint receiving a resource identifier shall validate scope ownership before read/write.
- Cross-branch/cross-governorate/cross-institution unauthorized access shall return `403`.
- Reusable scope helpers shall be used where defined (e.g., needs and finance scopes).

## 3.4 Needs (GIS) Module
- The system shall support create/read/update/review/approve/reject/return/classify/resolve flows for Needs.
- The system shall apply strict status transitions for Need lifecycle.
- The system shall generate unique `need_code` safely under concurrent writes.
- Needs visibility shall obey branch/governorate and role scope rules.

## 3.5 Funding Module
- The system shall support funding application lifecycle: draft, submit, branch review, consultant/funder review, approve/reject, funded.
- The system shall support assignment to consultant office and funding partner under policy and scope control.
- The system shall only allow loan creation when application is in an eligible state.
- The system shall generate collision-safe loan identifiers under concurrency controls.

## 3.6 Consulting Module
- The system shall support consulting request create/update/submit/sort/accept-offer/transfer flows.
- The system shall enforce scope-based request visibility and mutation permissions.
- Consulting offers shall only be accepted from valid scoped offices and relevant specialization.
- Workflow updates shall be validated against allowed status transitions.

## 3.7 Notifications and Inbox
- The system shall provide per-user unread notifications summary.
- The system shall provide centralized `group_unread_count` for role/group visibility.
- Frontend shall refresh notification counters using polling (20-30 seconds target).
- The system shall support mark single notification read and mark all read.
- Internal inbox APIs shall support unread count and conversation actions.

## 3.8 Training Module
- The system shall support training entities (centers, trainers, trainees, courses, programs, nominations).
- Course and registration actions shall be protected by role/permission middleware and policy checks.
- Certificate issue/approve/view/verify flows shall remain available per configured authorization.

## 3.9 Incubation Module
- The system shall support incubator, application, project, mentoring sessions, and reporting flows.
- Access to incubation operations shall follow role/permission constraints.

## 3.10 Workforce Module
- The system shall support job postings, job applications, and staff training requests.
- All mutation operations shall be constrained by role and permission middleware.

## 3.11 Admin Access and Audit
- The system shall support role/permission/user access management APIs.
- The system shall provide activity log retrieval for audit-enabled roles.
- Sensitive changes shall be traceable with actor identity and timestamp.

## 3.12 Status History
- The system shall persist status change history for critical models.
- Each history record shall include:
  - `model_type`
  - `model_id`
  - `from_status`
  - `to_status`
  - `changed_by`
  - `reason` (optional)
  - `created_at`
- Initial required model coverage:
  - `Need`
  - `FundingApplication`
  - `ConsultingRequest`

---

## 4. Business Rules

- BR-01: A state transition is invalid unless explicitly allowed by transition rules.
- BR-02: Auditor users cannot perform mutation actions in protected modules.
- BR-03: Data scope controls override generic view permissions for sensitive resources.
- BR-04: Status changes for critical workflows must be atomic and auditable.
- BR-05: Role-group notification badge must represent unread count even if notification page is not opened.

---

## 5. External Interface Requirements

## 5.1 API Interface
- Protocol: HTTP/HTTPS
- Payload: JSON
- Auth: Bearer token (Sanctum)
- Protected routes grouped under `auth:sanctum`

## 5.2 Frontend Integration
- Frontend pages consume API endpoints and render role-based service experiences.
- Notification UI includes:
  - Topbar personal unread badge
  - Sidebar/group unread badge
  - Polling refresh behavior

## 5.3 Database Interface
- Relational schema with FK relationships for user-scope entities
- Additional `status_histories` table for workflow auditability

---

## 6. Non-Functional Requirements

## 6.1 Security
- Enforce server-side authorization for all sensitive routes.
- Prevent IDOR by scoped existence checks.
- Avoid direct unsafe SQL composition from external input.
- Apply request throttling for exposed/public-risk endpoints.

## 6.2 Reliability
- Use `DB::transaction` for multi-step sensitive updates.
- Use `lockForUpdate` where race conditions can corrupt workflow/codes.

## 6.3 Performance
- Paginate list endpoints.
- Use indexed access patterns for scoped list/filter APIs.
- Keep dashboard/stat endpoints optimized for role-scoped reads.

## 6.4 Maintainability
- Centralize workflow transition logic in validators/services.
- Reuse policy/scope helpers instead of per-endpoint duplicated rules.

## 6.5 Observability and Audit
- Record high-risk operations via audit logging mechanisms.
- Ensure status history records remain queryable for incident review.

---

## 7. Data Requirements

Core entities include:
- User, Role, Permission
- Branch, Governorate
- Need
- FundingApplication, FundingDocument, FundedLoan
- ConsultingRequest, ConsultingOffer, ConsultingContract
- Notification, Inbox message
- AuditLog, StatusHistory

Data integrity requirements:
- FK consistency
- Controlled status values via application-level validation
- Immutable event-style status history records

---

## 8. Verification and Validation

Required test coverage categories:
- Authorization tests for sensitive endpoints (`403` expected on unauthorized access)
- Scope/IDOR tests across branch/governorate boundaries
- Invalid status transition tests (`422` expected)
- Concurrency tests for unique code/loan generation and status races
- Notification summary and polling behavior tests
- Regression tests for key dashboards and list/show routes

---

## 9. Release Acceptance Criteria

A release is acceptable when:
1. All critical mutation/read endpoints enforce server-side authorization.
2. No known exploitable IDOR path exists in targeted modules.
3. Status transitions are centrally validated for critical models.
4. Status history is stored for needs, funding applications, consulting requests.
5. Frontend receives and displays notification summary and group badge correctly.
6. Core smoke and feature tests pass in staging.

---

## 10. Out of Scope (Current Version)

- Migration to WebSocket real-time push notifications
- Queue/Redis architectural dependency additions
- Major UI redesign outside notification badge integration
- Auth-cookie redesign to HttpOnly migration in this release

---

## 11. Document Deliverables (Companion Docs)

- `docs/requirements_traceability_matrix_v1.0.md`
- `docs/API_Documentation_v1.0.md`

