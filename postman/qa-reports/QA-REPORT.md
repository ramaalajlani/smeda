# SMEDA QA Report

- Generated: 2026-08-06T14:43:24+00:00
- API: `http://127.0.0.1:8000`
- Login: **32** pass / **0** fail
- Permission matrix: **450** pass / **0** fail
- Scenarios: **11** pass / **0** fail
- Total: **493** pass / **0** fail

## Scenarios

| Scenario | Result | HTTP | Detail |
|---|---|---|---|
| S1 Create Need (data_entry) | PASS | 201 | need_id=1074 |
| S1b View Need (data_reviewer) | PASS | 200 | HTTP 200 |
| S2 Create Finance Application (project_owner) | PASS | 201 | application_id=1069 |
| S2b View Finance App (finance_manager) | PASS | 200 | HTTP 200 |
| S3 List Courses (training_manager) | PASS | 200 | HTTP 200 |
| S3b List Courses (center_user) | PASS | 200 | HTTP 200 |
| S3c List Courses (trainee_user) | PASS | 200 | HTTP 200 |
| S4 List Certificates (training_manager) | PASS | 200 | HTTP 200 |
| S4b Public Certificate Verify | PASS | 422 | HTTP 422 (public endpoint reachable) |
| S5 Deny admin users (trainee_user) | PASS | 403 | HTTP 403 |
| S5b Deny central-bank dashboard (data_entry) | PASS | 403 | HTTP 403 |

## Login

| Role | Email | Result | Detail |
|---|---|---|---|
| `admin` | `admin@system.com` | PASS | token ok |
| `super_admin` | `super.admin@system.com` | PASS | token ok |
| `general_director` | `general@system.com` | PASS | token ok |
| `deputy_general_director` | `deputy@system.com` | PASS | token ok |
| `deputy_director` | `deputy@system.com` | PASS | token ok |
| `branch_manager` | `branch.damascus@system.com` | PASS | token ok |
| `governor` | `governor.tartus@system.com` | PASS | token ok |
| `finance_manager` | `finance.manager@system.com` | PASS | token ok |
| `finance_officer` | `finance.officer@system.com` | PASS | token ok |
| `data_entry` | `data-entry.damascus@system.com` | PASS | token ok |
| `data_reviewer` | `data-reviewer.damascus@system.com` | PASS | token ok |
| `center_user` | `center@system.com` | PASS | token ok |
| `trainer_user` | `trainer@system.com` | PASS | token ok |
| `trainee_user` | `trainee@system.com` | PASS | token ok |
| `funding_partner` | `funding.partner@system.com` | PASS | token ok |
| `consultant_office` | `consultant.office@system.com` | PASS | token ok |
| `training_manager` | `manager@system.com` | PASS | token ok |
| `project_services_manager` | `projects@system.com` | PASS | token ok |
| `auditor` | `auditor@system.com` | PASS | token ok |
| `media_manager` | `media@system.com` | PASS | token ok |
| `incubator_manager` | `incubator.manager@system.com` | PASS | token ok |
| `entrepreneur_manager` | `entrepreneur.manager@system.com` | PASS | token ok |
| `system_admin` | `system.admin@system.com` | PASS | token ok |
| `central_bank_admin` | `central.bank@system.com` | PASS | token ok |
| `consultant_union_admin` | `consultant.union@system.com` | PASS | token ok |
| `project_owner` | `project.owner@system.com` | PASS | token ok |
| `branch_officer` | `branch.officer.damascus@system.com` | PASS | token ok |
| `workforce_manager` | `workforce@system.com` | PASS | token ok |
| `training_supervisor` | `training.supervisor@system.com` | PASS | token ok |
| `incubator_mentor` | `incubator.mentor@system.com` | PASS | token ok |
| `development_manager` | `development@system.com` | PASS | token ok |
| `local_development_manager` | `local.development@system.com` | PASS | token ok |

## Permission Matrix (failures only)

_No matrix failures._

## Full Matrix CSV hint
See `QA-REPORT.json` → `matrix` for complete allow/deny results.
