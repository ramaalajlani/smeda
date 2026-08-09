# Account Smoke Test Report

- Date: 2026-08-06T12:33:48+00:00
- API: `http://127.0.0.1:8000/api`
- Passed: **32** / 32
- Failed: **0**

| Role | Email | Result | Detail |
|---|---|---|---|
| `admin` | `admin@system.com` | PASS | login+me ok; roles=general_director,admin |
| `super_admin` | `super.admin@system.com` | PASS | login+me ok; roles=super_admin |
| `general_director` | `general@system.com` | PASS | login+me ok; roles=general_director |
| `deputy_general_director` | `deputy@system.com` | PASS | login+me ok; roles=deputy_general_director,deputy_director |
| `deputy_director` | `deputy@system.com` | PASS | login+me ok; roles=deputy_general_director,deputy_director |
| `branch_manager` | `branch.damascus@system.com` | PASS | login+me ok; roles=branch_manager |
| `governor` | `governor.tartus@system.com` | PASS | login+me ok; roles=governor |
| `finance_manager` | `finance.manager@system.com` | PASS | login+me ok; roles=finance_manager |
| `finance_officer` | `finance.officer@system.com` | PASS | login+me ok; roles=finance_officer |
| `data_entry` | `data-entry.damascus@system.com` | PASS | login+me ok; roles=data_entry |
| `data_reviewer` | `data-reviewer.damascus@system.com` | PASS | login+me ok; roles=data_reviewer |
| `center_user` | `center@system.com` | PASS | login+me ok; roles=center_user |
| `trainer_user` | `trainer@system.com` | PASS | login+me ok; roles=trainer_user |
| `trainee_user` | `trainee@system.com` | PASS | login+me ok; roles=trainee_user |
| `funding_partner` | `funding.partner@system.com` | PASS | login+me ok; roles=funding_partner |
| `consultant_office` | `consultant.office@system.com` | PASS | login+me ok; roles=consultant_office |
| `training_manager` | `manager@system.com` | PASS | login+me ok; roles=training_manager |
| `project_services_manager` | `projects@system.com` | PASS | login+me ok; roles=project_services_manager |
| `auditor` | `auditor@system.com` | PASS | login+me ok; roles=auditor |
| `media_manager` | `media@system.com` | PASS | login+me ok; roles=media_manager |
| `incubator_manager` | `incubator.manager@system.com` | PASS | login+me ok; roles=incubator_manager |
| `entrepreneur_manager` | `entrepreneur.manager@system.com` | PASS | login+me ok; roles=entrepreneur_manager |
| `system_admin` | `system.admin@system.com` | PASS | login+me ok; roles=system_admin |
| `central_bank_admin` | `central.bank@system.com` | PASS | login+me ok; roles=central_bank_admin |
| `consultant_union_admin` | `consultant.union@system.com` | PASS | login+me ok; roles=consultant_union_admin |
| `project_owner` | `project.owner@system.com` | PASS | login+me ok; roles=project_owner |
| `branch_officer` | `branch.officer.damascus@system.com` | PASS | login+me ok; roles=branch_officer |
| `workforce_manager` | `workforce@system.com` | PASS | login+me ok; roles=workforce_manager |
| `training_supervisor` | `training.supervisor@system.com` | PASS | login+me ok; roles=training_supervisor |
| `incubator_mentor` | `incubator.mentor@system.com` | PASS | login+me ok; roles=incubator_mentor |
| `development_manager` | `development@system.com` | PASS | login+me ok; roles=development_manager |
| `local_development_manager` | `local.development@system.com` | PASS | login+me ok; roles=local_development_manager |
