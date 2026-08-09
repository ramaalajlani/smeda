/**
 * Generates SMEDA Postman collection + environments + coverage from Laravel route:list JSON.
 * Run: node generate-collection.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createHash } from 'crypto';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const routesPath = path.join(__dirname, '_routes_raw.json');
const outCollection = path.join(__dirname, 'SMEDA-API.postman_collection.json');
const outLocal = path.join(__dirname, 'SMEDA-Local.postman_environment.json');
const outProd = path.join(__dirname, 'SMEDA-Production.postman_environment.json');
const outCoverage = path.join(__dirname, 'API-COVERAGE.md');
const outReadme = path.join(__dirname, 'README.md');

const routesRaw = JSON.parse(
  fs.readFileSync(routesPath, 'utf8').replace(/^\uFEFF/, '')
);

/** All permissions from RolePermissionSeeder (complete list used by national roles). */
const ALL_PERMISSIONS = [
  'view_trainers','manage_trainers','view_trainer_profiles','edit_own_trainer_profile',
  'view_centers','manage_centers','view_kits','manage_kits','nominate_training_kits','review_training_kit_nominations',
  'view_programs','manage_programs','program_bank.view','program_bank.create','program_bank.update','program_bank.delete','program_bank.approve','program_bank.reports',
  'view_courses','manage_courses','view_course_details','view_trainees','manage_trainees',
  'view_certificates','issue_certificates','view_certificate_approvals','approve_center_certificates','approve_training_certificates','approve_deputy_certificates','approve_general_director_certificates','print_certificates','verify_certificates',
  'view_reports','view_audit',
  'view_registration_requests','create_center_registration_requests','review_center_registration_requests','create_trainer_registration_requests','review_trainer_registration_requests','create_trainee_registration_requests','review_trainee_registration_requests','create_course_registration_requests','confirm_course_registration_requests','complete_course_registration_requests',
  'manage_roles','view_roles','create_roles','update_roles','delete_roles','manage_permissions','view_permissions','create_permissions','update_permissions','delete_permissions','assign_roles','revoke_roles','assign_permissions','revoke_permissions','view_users','manage_user_access',
  'view_governorates','view_branches','manage_branches','manage_branch_managers','view_national_reports','view_branch_reports','manage_agreements','approve_agreements','view_finance','manage_finance','approve_finance',
  'finance.applications.view','finance.applications.create','finance.applications.update','finance.applications.submit','finance.applications.review_branch','finance.applications.request_completion','finance.applications.assign_consultant','finance.applications.assign_partner','finance.applications.approve','finance.applications.reject',
  'finance.consultants.view','finance.consultants.manage','finance.consultants.assign','finance.consultants.submit_price','finance.consultants.approve_price','finance.consultants.submit_report',
  'finance.partners.view','finance.partners.manage','finance.partners.review','finance.partners.decide',
  'finance.loans.view','finance.loans.manage','finance.loans.payments','finance.loans.defaulted','finance.loans.close',
  'finance.metrics.view','finance.metrics.national','finance.metrics.branch',
  'finance.consultant_union.dashboard','finance.consultants.view_all','finance.consultants.create','finance.consultants.update','finance.consultants.approve','finance.consultants.activate','finance.consultants.suspend','finance.consultants.monitor','finance.consultants.reports.view','finance.consultants.price_offers.view',
  'finance.consultant_office.dashboard','finance.consultant_assignments.view_own','finance.consultant_assignments.accept','finance.consultant_assignments.reject','finance.consultant_assignments.submit_price','finance.consultant_reports.create','finance.consultant_reports.update_own','finance.consultant_reports.view_own',
  'finance.central_bank.dashboard','finance.partners.view_all','finance.partners.create','finance.partners.update','finance.partners.approve','finance.partners.activate','finance.partners.suspend','finance.partners.monitor','finance.partner_decisions.view_all','finance.bank_metrics.view',
  'finance.funding_partner.dashboard','finance.partner_assignments.view_own','finance.partner_assignments.review','finance.partner_assignments.decide','finance.partner_assignments.approve_amount','finance.loans.view_own','finance.loans.update_own_status',
  'needs.view','needs.view_all','needs.view_branch','needs.create','needs.create_citizen','needs.create_state','needs.update','needs.review','needs.approve','needs.reject','needs.return','needs.classify','needs.resolve','needs.export','needs.dashboard','needs.map','needs.manage_lookups','needs.manage_admin_units','needs.view_state_only',
  'workforce.jobs.view','workforce.jobs.create','workforce.jobs.manage','workforce.applications.create','workforce.applications.view','workforce.training_requests.create','workforce.training_requests.view',
  'incubation.view','incubation.manage','incubation.mentor','entrepreneur.manage','story.manage','news.manage',
];

const ACCESS_PERMISSIONS = [
  'manage_roles','view_roles','create_roles','update_roles','delete_roles',
  'manage_permissions','view_permissions','create_permissions','update_permissions','delete_permissions',
  'assign_roles','revoke_roles','assign_permissions','revoke_permissions','view_users','manage_user_access',
];

const NEEDS_DATA_ENTRY = ['needs.view','needs.view_branch','needs.create','needs.create_citizen','needs.update','needs.map'];
const NEEDS_DATA_REVIEWER = ['needs.view','needs.view_branch','needs.review','needs.return','needs.map'];
const NEEDS_GOVERNOR = ['needs.view','needs.view_branch','needs.view_state_only','needs.create_state','needs.map','needs.dashboard','needs.export'];
const NEEDS_BRANCH_MANAGER = ['needs.view','needs.view_branch','needs.create','needs.create_citizen','needs.create_state','needs.update','needs.review','needs.approve','needs.reject','needs.return','needs.classify','needs.resolve','needs.export','needs.dashboard','needs.map'];
const NEEDS_BRANCH_OFFICER = ['needs.view','needs.view_branch','needs.create','needs.create_citizen','needs.map'];
const NEEDS_AUDITOR = ['needs.view','needs.view_all','needs.export','needs.dashboard','needs.map'];
const NEEDS_PROJECT = ['needs.view','needs.view_all','needs.create','needs.create_citizen','needs.create_state','needs.update','needs.review','needs.approve','needs.reject','needs.return','needs.classify','needs.resolve','needs.export','needs.dashboard','needs.map','needs.manage_lookups','needs.manage_admin_units'];
const NEEDS_DEV = ['needs.view','needs.view_all','needs.create_state','needs.classify','needs.dashboard','needs.map','needs.export'];
const WORKFORCE_BASIC = ['workforce.jobs.view','workforce.applications.create','workforce.applications.view','workforce.training_requests.create','workforce.training_requests.view'];
const WORKFORCE_MANAGER = ['workforce.jobs.view','workforce.jobs.create','workforce.jobs.manage','workforce.applications.create','workforce.applications.view','workforce.training_requests.create','workforce.training_requests.view'];
const WORKFORCE_EMPLOYER = ['workforce.jobs.view','workforce.jobs.create','workforce.jobs.manage','workforce.applications.view'];
const FINANCE_BRANCH_MGR = ['finance.applications.view','finance.applications.review_branch','finance.applications.request_completion','finance.applications.assign_consultant','finance.consultants.view','finance.partners.view','finance.loans.view','finance.metrics.view','finance.metrics.branch','view_finance'];
const FINANCE_BRANCH_OFF = ['finance.applications.view','finance.consultants.view'];
const FINANCE_VIEW = ['finance.applications.view','finance.consultants.view','finance.partners.view','finance.loans.view','finance.metrics.view'];
const FINANCE_CONSULTANT_OFFICE = ['finance.consultant_office.dashboard','finance.consultant_assignments.view_own','finance.consultant_assignments.accept','finance.consultant_assignments.reject','finance.consultant_assignments.submit_price','finance.consultant_reports.create','finance.consultant_reports.update_own','finance.consultant_reports.view_own','finance.applications.view','finance.consultants.view'];
const FINANCE_PARTNER = ['finance.funding_partner.dashboard','finance.partner_assignments.view_own','finance.partner_assignments.review','finance.partner_assignments.decide','finance.partner_assignments.approve_amount','finance.loans.view_own','finance.loans.update_own_status','finance.applications.view','finance.partners.view','finance.loans.view'];
const FINANCE_UNION = ['finance.consultant_union.dashboard','finance.consultants.view_all','finance.consultants.create','finance.consultants.update','finance.consultants.approve','finance.consultants.activate','finance.consultants.suspend','finance.consultants.monitor','finance.consultants.reports.view','finance.consultants.price_offers.view','finance.applications.view','finance.consultants.view'];
const FINANCE_CB = ['finance.central_bank.dashboard','finance.partners.view_all','finance.partners.create','finance.partners.update','finance.partners.approve','finance.partners.activate','finance.partners.suspend','finance.partners.monitor','finance.partner_decisions.view_all','finance.bank_metrics.view','finance.applications.view','finance.partners.view','finance.loans.view'];
const INCUBATION_VIEW = ['incubation.view'];
const INCUBATION_MANAGE = ['incubation.view','incubation.manage','story.manage'];
const INCUBATION_MENTOR = ['incubation.view','incubation.mentor'];
const ENTREPRENEUR_MANAGE = ['entrepreneur.manage','incubation.view','story.manage'];
const MEDIA = ['news.manage','story.manage'];
const PROJECT_SVC_TRAINING = ['view_trainers','manage_trainers','view_centers','manage_centers','view_kits','manage_kits','view_programs','manage_programs','view_courses','manage_courses','view_course_details','view_trainees','manage_trainees','view_certificates','issue_certificates','view_certificate_approvals','approve_training_certificates','print_certificates','verify_certificates','view_registration_requests','review_center_registration_requests','review_trainer_registration_requests','review_trainee_registration_requests','view_reports','program_bank.view','program_bank.create','program_bank.update','program_bank.approve','program_bank.reports'];
const PROJECT_SVC_ADMIN = ['view_users','manage_user_access','view_governorates','view_branches','manage_branches','view_national_reports','view_branch_reports','view_reports'];

const ROLE_PERMISSIONS = {
  general_director: ALL_PERMISSIONS,
  admin: ALL_PERMISSIONS,
  deputy_general_director: ALL_PERMISSIONS,
  deputy_director: ALL_PERMISSIONS,
  super_admin: ALL_PERMISSIONS,
  system_admin: ACCESS_PERMISSIONS,
  governor: [
    'view_branch_reports','view_governorates','view_branches','view_national_reports',
    'view_trainers','view_trainer_profiles','view_centers','view_courses','view_course_details','view_trainees',
    'view_certificates','view_certificate_approvals','view_registration_requests','view_reports',
    ...NEEDS_GOVERNOR,
  ],
  branch_manager: [
    'view_branch_reports','view_governorates','view_branches',
    'view_trainers','manage_trainers','view_trainer_profiles','view_centers','manage_centers',
    'view_courses','manage_courses','view_course_details','view_trainees','manage_trainees',
    'view_certificates','issue_certificates','view_certificate_approvals','approve_center_certificates','print_certificates','verify_certificates',
    'view_registration_requests','review_center_registration_requests','review_trainer_registration_requests','review_trainee_registration_requests','review_training_kit_nominations',
    ...FINANCE_BRANCH_MGR, ...NEEDS_BRANCH_MANAGER, ...WORKFORCE_MANAGER,
  ],
  branch_officer: [
    'view_branch_reports','view_governorates','view_branches','view_trainers','view_centers','view_courses','view_trainees','view_certificates','view_registration_requests','view_reports',
    ...FINANCE_BRANCH_OFF, ...NEEDS_BRANCH_OFFICER, ...WORKFORCE_BASIC,
  ],
  workforce_manager: [...WORKFORCE_MANAGER, 'view_reports'],
  training_manager: [
    'view_trainers','manage_trainers','view_trainer_profiles','view_centers','manage_centers',
    'view_kits','manage_kits','review_training_kit_nominations','view_programs','manage_programs',
    'program_bank.view','program_bank.create','program_bank.update','program_bank.delete','program_bank.approve','program_bank.reports',
    'view_courses','manage_courses','view_course_details','view_trainees','manage_trainees',
    'view_certificates','issue_certificates','view_certificate_approvals','approve_training_certificates','print_certificates','verify_certificates',
    'view_reports','view_registration_requests','review_center_registration_requests','review_trainer_registration_requests','review_trainee_registration_requests',
    ...WORKFORCE_MANAGER,
  ],
  training_supervisor: [
    'view_trainers','view_trainer_profiles','view_centers','view_kits','view_programs','view_courses','view_course_details','view_trainees',
    'view_certificates','view_certificate_approvals','print_certificates','verify_certificates','view_registration_requests',
    'review_center_registration_requests','review_trainer_registration_requests','review_trainee_registration_requests','review_training_kit_nominations','view_reports',
    ...WORKFORCE_BASIC,
  ],
  center_user: [
    'view_centers','manage_centers','view_kits','manage_kits','view_trainers','manage_trainers',
    'view_courses','manage_courses','view_course_details','view_trainees','manage_trainees',
    'view_certificates','issue_certificates','view_certificate_approvals','approve_center_certificates','print_certificates','verify_certificates',
    'view_registration_requests','create_center_registration_requests','create_trainer_registration_requests','create_trainee_registration_requests','complete_course_registration_requests',
    ...WORKFORCE_EMPLOYER,
  ],
  trainer_user: [
    'view_courses','view_course_details','view_trainees','view_certificates','print_certificates',
    'view_trainer_profiles','edit_own_trainer_profile','nominate_training_kits',
    'create_trainer_registration_requests','create_course_registration_requests','confirm_course_registration_requests',
  ],
  trainee_user: [
    'view_courses','view_course_details','view_certificates','print_certificates',
    'create_trainee_registration_requests','create_course_registration_requests','confirm_course_registration_requests',
    ...WORKFORCE_BASIC,
  ],
  auditor: [
    'view_trainers','view_trainer_profiles','view_centers','view_kits','view_programs','program_bank.view','program_bank.reports',
    'view_courses','view_course_details','view_trainees','view_certificates','view_certificate_approvals','view_audit','view_reports','verify_certificates','view_registration_requests','view_finance',
    ...FINANCE_VIEW, ...NEEDS_AUDITOR,
  ],
  data_entry: NEEDS_DATA_ENTRY,
  data_reviewer: NEEDS_DATA_REVIEWER,
  project_services_manager: [...NEEDS_PROJECT, ...PROJECT_SVC_TRAINING, ...PROJECT_SVC_ADMIN, ...WORKFORCE_MANAGER],
  development_manager: NEEDS_DEV,
  local_development_manager: NEEDS_DEV,
  finance_manager: ALL_PERMISSIONS.filter((p) => p.startsWith('finance.')),
  finance_officer: [
    'finance.applications.view','finance.applications.create','finance.applications.update','finance.applications.review_branch',
    'finance.consultants.view','finance.partners.view','finance.loans.view','finance.metrics.view','finance.metrics.national',
  ],
  consultant_office: FINANCE_CONSULTANT_OFFICE,
  funding_partner: FINANCE_PARTNER,
  consultant_union_admin: FINANCE_UNION,
  central_bank_admin: FINANCE_CB,
  project_owner: [
    'finance.applications.view','finance.applications.create','finance.applications.update','finance.applications.submit',
    ...WORKFORCE_EMPLOYER, ...INCUBATION_VIEW,
  ],
  incubator_manager: [...INCUBATION_MANAGE, 'view_reports','view_governorates','view_branches'],
  incubator_mentor: INCUBATION_MENTOR,
  entrepreneur_manager: [...ENTREPRENEUR_MANAGE, 'view_reports','view_governorates'],
  media_manager: [...MEDIA, 'view_reports'],
};

const ACTORS = [
  { key: 'super_admin', folder: '02 - Super Admin', emailVar: 'super_admin_email', passVar: 'super_admin_password', scope: 'وطني — صلاحيات كاملة', demo: 'super.admin@system.com', demoPassword: '12345678' },
  { key: 'admin', folder: '03 - Admin', emailVar: 'admin_email', passVar: 'admin_password', scope: 'وطني — صلاحيات كاملة', demo: 'admin@system.com', demoPassword: '12345678' },
  { key: 'general_director', folder: '04 - General Director', emailVar: 'general_director_email', passVar: 'general_director_password', scope: 'وطني', demo: 'general@system.com', demoPassword: '12345678' },
  { key: 'deputy_general_director', folder: '05 - Deputy General Director', emailVar: 'deputy_general_director_email', passVar: 'deputy_general_director_password', scope: 'وطني', demo: 'deputy@system.com', demoPassword: '12345678' },
  { key: 'deputy_director', folder: '06 - Deputy Director', emailVar: 'deputy_director_email', passVar: 'deputy_director_password', scope: 'وطني', demo: 'deputy@system.com', demoPassword: '12345678' },
  { key: 'branch_manager', folder: '07 - Branch Manager', emailVar: 'branch_manager_email', passVar: 'branch_manager_password', scope: 'فرع (branch_id)', demo: 'branch.damascus@system.com', demoPassword: '12345678' },
  { key: 'governor', folder: '08 - Governor', emailVar: 'governor_email', passVar: 'governor_password', scope: 'محافظة (governorate_id)', demo: 'governor.tartus@system.com', demoPassword: '12345678' },
  { key: 'finance_manager', folder: '09 - Finance Manager', emailVar: 'finance_manager_email', passVar: 'finance_manager_password', scope: 'وطني — تمويل', demo: 'finance.manager@system.com', demoPassword: '12345678' },
  { key: 'finance_officer', folder: '10 - Finance Officer', emailVar: 'finance_officer_email', passVar: 'finance_officer_password', scope: 'تمويل تشغيلي', demo: 'finance.officer@system.com', demoPassword: '12345678' },
  { key: 'data_entry', folder: '11 - Data Entry', emailVar: 'data_entry_email', passVar: 'data_entry_password', scope: 'فرع — إدخال احتياجات', demo: 'data-entry.damascus@system.com', demoPassword: '12345678' },
  { key: 'data_reviewer', folder: '12 - Data Reviewer', emailVar: 'data_reviewer_email', passVar: 'data_reviewer_password', scope: 'فرع — مراجعة احتياجات', demo: 'data-reviewer.damascus@system.com', demoPassword: '12345678' },
  { key: 'center_user', folder: '13 - Training Center', emailVar: 'center_user_email', passVar: 'center_user_password', scope: 'مركز تدريبي', demo: 'center@system.com', demoPassword: '12345678' },
  { key: 'trainer_user', folder: '14 - Trainer', emailVar: 'trainer_email', passVar: 'trainer_password', scope: 'مدرب', demo: 'trainer@system.com', demoPassword: '12345678' },
  { key: 'trainee_user', folder: '15 - Trainee', emailVar: 'trainee_email', passVar: 'trainee_password', scope: 'متدرب', demo: 'trainee@system.com', demoPassword: '12345678' },
  { key: 'funding_partner', folder: '16 - Funding Partner', emailVar: 'funding_partner_email', passVar: 'funding_partner_password', scope: 'شريك تمويل', demo: 'funding.partner@system.com', demoPassword: '12345678' },
  { key: 'consultant_office', folder: '17 - Consultant Office', emailVar: 'consultant_office_email', passVar: 'consultant_office_password', scope: 'مكتب استشاري', demo: 'consultant.office@system.com', demoPassword: '12345678' },
  { key: 'training_manager', folder: '18 - Training Manager', emailVar: 'training_manager_email', passVar: 'training_manager_password', scope: 'وطني — تدريب', demo: 'manager@system.com', demoPassword: '12345678' },
  { key: 'project_services_manager', folder: '19 - Project Services Manager', emailVar: 'psm_email', passVar: 'psm_password', scope: 'وطني — خدمات مشاريع / GIS', demo: 'projects@system.com', demoPassword: '12345678' },
  { key: 'auditor', folder: '20 - Auditor', emailVar: 'auditor_email', passVar: 'auditor_password', scope: 'وطني — قراءة وتدقيق', demo: 'auditor@system.com', demoPassword: '12345678' },
  { key: 'media_manager', folder: '21 - Media Manager', emailVar: 'media_manager_email', passVar: 'media_manager_password', scope: 'إعلام وأخبار', demo: 'media@system.com', demoPassword: '12345678' },
  { key: 'incubator_manager', folder: '22 - Incubator Manager', emailVar: 'incubator_manager_email', passVar: 'incubator_manager_password', scope: 'حاضنات', demo: 'incubator.manager@system.com', demoPassword: '12345678' },
  { key: 'entrepreneur_manager', folder: '23 - Entrepreneur Manager', emailVar: 'entrepreneur_manager_email', passVar: 'entrepreneur_manager_password', scope: 'رواد أعمال', demo: 'entrepreneur.manager@system.com', demoPassword: '12345678' },
  { key: 'system_admin', folder: '24 - System Admin', emailVar: 'system_admin_email', passVar: 'system_admin_password', scope: 'إدارة وصول فقط', demo: 'system.admin@system.com', demoPassword: '12345678' },
  { key: 'central_bank_admin', folder: '25 - Central Bank Admin', emailVar: 'central_bank_email', passVar: 'central_bank_password', scope: 'مصرف مركزي / شركاء', demo: 'central.bank@system.com', demoPassword: '12345678' },
  { key: 'consultant_union_admin', folder: '26 - Consultant Union Admin', emailVar: 'consultant_union_email', passVar: 'consultant_union_password', scope: 'اتحاد المستشارين', demo: 'consultant.union@system.com', demoPassword: '12345678' },
  { key: 'project_owner', folder: '27 - Project Owner', emailVar: 'project_owner_email', passVar: 'project_owner_password', scope: 'صاحب مشروع', demo: 'project.owner@system.com', demoPassword: '12345678' },
  { key: 'branch_officer', folder: '28 - Branch Officer', emailVar: 'branch_officer_email', passVar: 'branch_officer_password', scope: 'فرع — موظف', demo: 'branch.officer.damascus@system.com', demoPassword: '12345678' },
  { key: 'workforce_manager', folder: '29 - Workforce Manager', emailVar: 'workforce_manager_email', passVar: 'workforce_manager_password', scope: 'قوى عاملة', demo: 'workforce@system.com', demoPassword: '12345678' },
  { key: 'training_supervisor', folder: '30 - Training Supervisor', emailVar: 'training_supervisor_email', passVar: 'training_supervisor_password', scope: 'مشرف تدريب', demo: 'training.supervisor@system.com', demoPassword: '12345678' },
  { key: 'incubator_mentor', folder: '31 - Incubator Mentor', emailVar: 'incubator_mentor_email', passVar: 'incubator_mentor_password', scope: 'مرشد حاضنة', demo: 'incubator.mentor@system.com', demoPassword: '12345678' },
  { key: 'development_manager', folder: '32 - Development Manager', emailVar: 'development_manager_email', passVar: 'development_manager_password', scope: 'تنمية — احتياجات', demo: 'development@system.com', demoPassword: '12345678' },
  { key: 'local_development_manager', folder: '33 - Local Development Manager', emailVar: 'local_development_manager_email', passVar: 'local_development_manager_password', scope: 'تنمية محلية', demo: 'local.development@system.com', demoPassword: '12345678' },
];

function uid() {
  return createHash('md5').update(Math.random().toString() + Date.now() + Math.random()).digest('hex').slice(0, 16);
}

function splitMethods(method) {
  return String(method || 'GET')
    .split('|')
    .map((m) => m.trim().toUpperCase())
    .filter((m) => m && m !== 'HEAD');
}

function parseMiddleware(mw) {
  const list = Array.isArray(mw) ? mw : [];
  const info = {
    auth: false,
    permissions: [],
    roles: [],
    roleOrPermission: [],
    dashboardAccess: false,
    raw: list,
  };
  for (const item of list) {
    const s = String(item);
    if (s.includes('Authenticate:sanctum') || s === 'auth:sanctum') info.auth = true;
    if (s.includes('DashboardAccess') || s.includes('dashboard.access')) info.dashboardAccess = true;
    let m;
    if (s.includes('RoleOrPermissionMiddleware:')) {
      m = s.match(/RoleOrPermissionMiddleware:(.+)$/);
      if (m) info.roleOrPermission.push(...m[1].split('|').map((x) => x.trim()).filter(Boolean));
    } else if (s.includes('PermissionMiddleware:')) {
      m = s.match(/PermissionMiddleware:(.+)$/);
      if (m) info.permissions.push(...m[1].split('|').map((x) => x.trim()).filter(Boolean));
    } else if (s.includes('RoleMiddleware:')) {
      m = s.match(/RoleMiddleware:(.+)$/);
      if (m) info.roles.push(...m[1].split('|').map((x) => x.trim()).filter(Boolean));
    }
  }
  return info;
}

/** Shared authenticated routes safe to attach for every logged-in actor. */
function isCommonAuthenticatedRoute(uri) {
  const p = uri.replace(/^api\/?/, '');
  return (
    p === 'me' ||
    p.startsWith('me/') ||
    p === 'logout' ||
    p.startsWith('my-electronic-signature') ||
    p.startsWith('electronic-signatures/') ||
    p.startsWith('notifications') ||
    p.startsWith('inbox') ||
    p === 'governorates' ||
    p === 'dashboard'
  );
}

function roleCanAccess(roleKey, mwInfo, uri) {
  if (!mwInfo.auth) return false;
  const perms = new Set(ROLE_PERMISSIONS[roleKey] || []);

  if (mwInfo.roles.length) {
    if (!mwInfo.roles.includes(roleKey)) return false;
  }
  if (mwInfo.permissions.length) {
    // Multiple permission middlewares are AND in Laravel route groups often stacked
    // Spatie applies each middleware separately → all must pass
    if (!mwInfo.permissions.every((p) => perms.has(p))) {
      // Some routes stack view+manage; require all listed permission middlewares
      return false;
    }
  }
  if (mwInfo.roleOrPermission.length) {
    const ok = mwInfo.roleOrPermission.some((x) => x === roleKey || perms.has(x));
    if (!ok) return false;
  }
  if (mwInfo.dashboardAccess) {
    // Mirror DashboardAccess::MAIN_DASHBOARD_ROLES roughly via having any role in ACTORS list
    // All seeded actors in ACTORS are included in MAIN_DASHBOARD_ROLES except none
  }

  const hasExplicitGate =
    mwInfo.roles.length || mwInfo.permissions.length || mwInfo.roleOrPermission.length;

  if (!hasExplicitGate) {
    // sanctum-only: only attach common routes to every actor; domain workflows live in Full Catalog
    // Full-access national roles still get all sanctum-only routes for exploratory testing
    const isNationalFull = ['general_director', 'admin', 'super_admin', 'deputy_general_director', 'deputy_director'].includes(roleKey);
    if (isNationalFull) return true;
    return isCommonAuthenticatedRoute(uri);
  }

  return true;
}

function moduleFromUri(uri) {
  const p = uri.replace(/^api\/?/, '');
  if (!p) return 'Root';
  if (p.startsWith('public/')) return 'Public Browse';
  if (p.startsWith('admin/')) return 'Admin Access';
  if (p.startsWith('finance/')) return 'Finance';
  if (p.startsWith('needs')) return 'GIS Needs';
  if (p.startsWith('training-courses')) return 'Courses';
  if (p.startsWith('training-kits') || p.startsWith('training-kit')) return 'Training Kits';
  if (p.startsWith('training-centers') || p.startsWith('training-supervisors')) return 'Training Centers';
  if (p.startsWith('training-programs') || p.startsWith('program-bank')) return 'Programs / Program Bank';
  if (p.startsWith('registration-requests')) return 'Registration Requests';
  if (p.startsWith('trainers') || p.startsWith('trainer-profiles') || p.startsWith('my-trainer')) return 'Trainers';
  if (p.startsWith('trainees')) return 'Trainees';
  if (p.startsWith('certificates') || p.startsWith('verify-certificate')) return 'Certificates';
  if (p.startsWith('map/')) return 'Map GIS';
  if (p.startsWith('locations')) return 'Locations';
  if (p.startsWith('news')) return 'News';
  if (p.startsWith('success-stories')) return 'Success Stories';
  if (p.startsWith('incubat') || p.startsWith('incubation')) return 'Incubation';
  if (p.startsWith('entrepreneur')) return 'Entrepreneurs';
  if (p.startsWith('workforce') || p.startsWith('job-') || p.startsWith('staff-training') || p.startsWith('workforces')) return 'Workforce';
  if (p.startsWith('consult')) return 'Consulting';
  if (p.startsWith('notifications')) return 'Notifications';
  if (p.startsWith('inbox')) return 'Inbox';
  if (p.startsWith('branches') || p.startsWith('governorates') || p.startsWith('agreements') || p === 'dashboard') return 'Org / Dashboard';
  if (p.startsWith('me') || p === 'logout' || p.startsWith('my-electronic') || p.startsWith('electronic-signatures')) return 'Profile / Signatures';
  if (p.startsWith('signatures')) return 'Signatures';
  return p.split('/')[0] || 'Other';
}

function requestName(method, uri) {
  const path = uri.replace(/^api\/?/, '');
  const parts = path.split('/').filter(Boolean);
  const last = parts[parts.length - 1] || '';
  const actionHints = {
    POST: 'Create',
    PUT: 'Update',
    PATCH: 'Update',
    DELETE: 'Delete',
    GET: 'List/Show',
  };
  let verb = actionHints[method] || method;
  if (method === 'GET' && (last.match(/^\{\w+\}$/) || last === '{id}')) verb = 'Show';
  if (method === 'GET' && !last.match(/^\{/)) {
    if (parts.length <= 1 || last.match(/^[a-z0-9\-]+$/i)) verb = last.includes('{') ? 'Show' : 'List';
  }
  const actionTail = ['approve','reject','submit','review','return','classify','resolve','verify','export','duplicate','transition','enroll','login','logout','register'];
  const lower = last.toLowerCase();
  if (actionTail.includes(lower) || lower.includes('approve') || lower.includes('assign')) {
    verb = lower.split('-').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
  }
  const resource = parts
    .filter((p) => !p.startsWith('{'))
    .slice(-2)
    .join(' / ') || path;
  return `${method} ${resource}`.replace(/\s+/g, ' ').trim();
}

function pathToPostman(uri) {
  // api/needs/{id} -> {{api_url}}/needs/{{need_id}} when known
  let rest = uri.replace(/^api\/?/, '');
  const varMap = {
    id: null, // contextual
    applicationId: 'finance_application_id',
    documentId: 'document_id',
    materialId: 'material_id',
    moduleId: 'module_id',
    outcomeId: 'outcome_id',
    sessionId: 'session_id',
    traineeId: 'trainee_id',
    groupId: 'group_id',
    permissionId: 'permission_id',
    certificate_code: 'certificate_code',
    code: 'signature_code',
    slug: 'story_slug',
    role: 'role_name',
    permission: 'permission_name',
  };
  const segs = rest.split('/').map((seg) => {
    const m = seg.match(/^\{(\w+)\}$/);
    if (!m) return seg;
    const key = m[1];
    if (varMap[key]) return `{{${varMap[key]}}}`;
    // infer from path context
    if (key === 'id') {
      if (rest.includes('needs')) return '{{need_id}}';
      if (rest.includes('finance/applications') || rest.includes('applications')) return '{{finance_application_id}}';
      if (rest.includes('training-courses') || rest.includes('courses')) return '{{course_id}}';
      if (rest.includes('certificates')) return '{{certificate_id}}';
      if (rest.includes('trainers') || rest.includes('trainer-profiles')) return '{{trainer_id}}';
      if (rest.includes('trainees')) return '{{trainee_id}}';
      if (rest.includes('training-centers')) return '{{center_id}}';
      if (rest.includes('branches')) return '{{branch_id}}';
      if (rest.includes('users')) return '{{user_id}}';
      if (rest.includes('incubators') || rest.includes('incubation')) return '{{incubator_id}}';
      if (rest.includes('news')) return '{{news_id}}';
      if (rest.includes('loans')) return '{{loan_id}}';
      if (rest.includes('agreements')) return '{{agreement_id}}';
      return '{{id}}';
    }
    return `{{${key}}}`;
  });
  return `{{api_url}}/${segs.join('/')}`;
}

function buildQuery(uri, method) {
  if (method !== 'GET') return [];
  const common = [
    { key: 'page', value: '1', description: 'رقم الصفحة', disabled: true },
    { key: 'per_page', value: '15', description: 'عدد العناصر', disabled: true },
    { key: 'search', value: '', description: 'بحث نصي', disabled: true },
  ];
  if (uri.includes('needs') || uri.includes('finance') || uri.includes('training')) {
    common.push(
      { key: 'status', value: '', description: 'تصفية بالحالة', disabled: true },
      { key: 'governorate_id', value: '{{governorate_id}}', description: 'محافظة', disabled: true },
      { key: 'branch_id', value: '{{branch_id}}', description: 'فرع', disabled: true },
      { key: 'date_from', value: '', description: 'من تاريخ', disabled: true },
      { key: 'date_to', value: '', description: 'إلى تاريخ', disabled: true },
      { key: 'sort_by', value: 'created_at', description: 'حقل الترتيب', disabled: true },
      { key: 'sort_direction', value: 'desc', description: 'asc|desc', disabled: true },
    );
  }
  return common;
}

function buildBody(method, uri) {
  if (!['POST', 'PUT', 'PATCH'].includes(method)) return undefined;
  const isUpload =
    uri.includes('documents') ||
    uri.includes('attachments') ||
    uri.includes('electronic-signature') ||
    uri.includes('materials') ||
    (uri.includes('registration-requests/centers') && method === 'POST');

  if (isUpload && method === 'POST') {
    return {
      mode: 'formdata',
      formdata: [
        { key: 'file', type: 'file', src: [], description: 'ملف مرفق (امتدادات حسب الـ API؛ حجم محدود بـ throttle:file-upload)' },
        { key: 'title', type: 'text', value: 'Sample attachment', description: 'اختياري' },
        { key: 'description', type: 'text', value: 'Uploaded via Postman', description: 'اختياري' },
      ],
    };
  }

  let sample = {};
  if (uri.includes('login')) {
    sample = { email: '{{email}}', password: '{{password}}', device_name: 'postman' };
  } else if (uri.includes('register')) {
    sample = {
      name: 'Test User',
      email: 'test.user@example.com',
      password: 'TestPassword123!',
      password_confirmation: 'TestPassword123!',
      account_type: 'trainee',
      device_name: 'postman',
    };
  } else if (uri.includes('change-password')) {
    sample = {
      current_password: 'CHANGE_ME',
      password: 'NewPassword123!',
      password_confirmation: 'NewPassword123!',
    };
  } else if (uri.endsWith('/me') && method === 'PUT') {
    sample = { name: 'Updated Name', phone: '0999999999' };
  } else if (uri.includes('needs') && method === 'POST' && !uri.includes('ai-suggest')) {
    sample = {
      title: 'احتياج تجريبي',
      description: 'وصف تفصيلي للاحتياج عبر Postman',
      need_category: 'service_gap',
      targeting_type: 'entrepreneurs',
      sector_codes: ['services'],
      governorate_id: '{{governorate_id}}',
      branch_id: '{{branch_id}}',
      latitude: 33.5138,
      longitude: 36.2765,
    };
  } else if (uri.includes('ai-suggest')) {
    sample = {
      title: 'تأسيس مركز ريادة أعمال',
      description: 'إنشاء مركز لدعم رواد الأعمال في المحافظة',
      sector: 'services',
      district_name: 'دمشق',
    };
  } else if (uri.includes('training-courses') && method === 'POST') {
    sample = {
      title: 'دورة تجريبية',
      training_center_id: '{{center_id}}',
      training_kit_id: '{{kit_id}}',
      start_date: '2026-09-01',
      end_date: '2026-09-30',
      status: 'draft',
    };
  } else if (uri.includes('admin/users') && method === 'POST') {
    sample = {
      name: 'Test User',
      email: 'test.user@example.com',
      phone: '0999999999',
      password: 'TestPassword123!',
      password_confirmation: 'TestPassword123!',
      roles: ['branch_officer'],
      governorate_id: '{{governorate_id}}',
      branch_id: '{{branch_id}}',
      is_active: true,
    };
  } else if (uri.includes('finance/applications') && method === 'POST') {
    sample = {
      title: 'طلب تمويل تجريبي',
      amount_requested: 5000000,
      branch_id: '{{branch_id}}',
      governorate_id: '{{governorate_id}}',
      description: 'طلب تمويل عبر Postman',
    };
  } else if (uri.includes('/approve') || uri.includes('/reject') || uri.includes('/return') || uri.includes('/review')) {
    sample = { notes: 'ملاحظة تجريبية من Postman', reason: 'سبب الإجراء' };
  } else if (uri.includes('certificates/issue')) {
    sample = { training_course_id: '{{course_id}}', trainee_id: '{{trainee_id}}' };
  } else if (uri.includes('certificates/verify')) {
    sample = { certificate_code: '{{certificate_code}}' };
  } else {
    sample = { note: 'Replace with fields required by validation in the controller/FormRequest' };
  }

  return {
    mode: 'raw',
    raw: JSON.stringify(sample, null, 2),
    options: { raw: { language: 'json' } },
  };
}

function permissionLabel(mwInfo) {
  if (!mwInfo.auth) return 'Public';
  const bits = [];
  if (mwInfo.roles.length) bits.push('role:' + mwInfo.roles.join('|'));
  if (mwInfo.permissions.length) bits.push('permission:' + mwInfo.permissions.join('+'));
  if (mwInfo.roleOrPermission.length) bits.push('role_or_permission:' + mwInfo.roleOrPermission.join('|'));
  if (mwInfo.dashboardAccess) bits.push('dashboard.access');
  if (!bits.length) bits.push('auth:sanctum (controller/policy may authorize further)');
  return bits.join(' ; ');
}

function saveIdScript(uri) {
  const scripts = [];
  scripts.push(`const json = pm.response.json();
const data = json.data || json.user || json;
const id = data && (data.id || (data.data && data.data.id));`);
  if (uri.includes('training-courses')) {
    scripts.push(`if (id) pm.environment.set("course_id", id);`);
  } else if (uri.includes('needs') && !uri.includes('lookups')) {
    scripts.push(`if (id) pm.environment.set("need_id", id);`);
  } else if (uri.includes('certificates')) {
    scripts.push(`if (id) pm.environment.set("certificate_id", id);`);
  } else if (uri.includes('finance/applications')) {
    scripts.push(`if (id) pm.environment.set("finance_application_id", id);`);
  } else if (uri.includes('trainees')) {
    scripts.push(`if (id) pm.environment.set("trainee_id", id);`);
  } else if (uri.includes('trainers')) {
    scripts.push(`if (id) pm.environment.set("trainer_id", id);`);
  } else if (uri.includes('training-centers')) {
    scripts.push(`if (id) pm.environment.set("center_id", id);`);
  } else if (uri.includes('admin/users')) {
    scripts.push(`if (id) pm.environment.set("user_id", id);`);
  }
  return scripts.join('\n');
}

const LOGIN_TEST = `const response = pm.response.json();

// Inject credentials used in this login into shared env vars
try {
    const raw = pm.request.body && pm.request.body.raw ? pm.request.body.raw : '';
    if (raw) {
        const reqBody = JSON.parse(raw);
        if (reqBody.email) {
            pm.environment.set("email", reqBody.email);
        }
        if (reqBody.password) {
            pm.environment.set("password", reqBody.password);
        }
    }
} catch (e) {}

if (response.token) {
    pm.environment.set("token", response.token);
}

if (response.token_type) {
    pm.environment.set("token_type", response.token_type);
} else {
    pm.environment.set("token_type", "Bearer");
}

if (response.user) {
    if (response.user.id) {
        pm.environment.set("user_id", response.user.id);
    }

    if (response.user.email) {
        pm.environment.set("email", response.user.email);
    }

    const roles = response.user.roles || [];
    const role = Array.isArray(roles) ? roles[0] : (response.user.role || roles);
    if (role) {
        pm.environment.set("role", role);
        pm.environment.set("actor", role);
    }

    if (response.user.governorate_id) {
        pm.environment.set("governorate_id", response.user.governorate_id);
    }

    if (response.user.branch_id) {
        pm.environment.set("branch_id", response.user.branch_id);
    }

    if (response.user.training_center_id) {
        pm.environment.set("center_id", response.user.training_center_id);
    }

    if (response.user.trainer_id) {
        pm.environment.set("trainer_id", response.user.trainer_id);
    }

    if (response.user.trainee_id) {
        pm.environment.set("trainee_id", response.user.trainee_id);
    }
}
`;

function makeRequestItem({ name, method, uri, authMode, description, emailExpr, passExpr, isLogin, saveId, emailVar, passVar }) {
  const url = pathToPostman(uri);
  const body = buildBody(isLogin ? 'POST' : method, isLogin ? 'api/login' : uri);
  if (isLogin && body?.raw) {
    body.raw = JSON.stringify(
      {
        email: emailExpr || '{{email}}',
        password: passExpr || '{{password}}',
        device_name: 'postman',
      },
      null,
      2
    );
  }

  const item = {
    name,
    request: {
      method,
      header: [
        { key: 'Accept', value: 'application/json' },
        ...(body?.mode === 'raw' ? [{ key: 'Content-Type', value: 'application/json' }] : []),
      ],
      url,
      description,
    },
    response: [],
  };

  if (body) item.request.body = body;
  const query = buildQuery(uri, method);
  if (query.length && typeof item.request.url === 'string') {
    item.request.url = {
      raw: url,
      host: ['{{api_url}}'],
      path: url.replace('{{api_url}}/', '').split('/'),
      query,
    };
  }

  if (authMode === 'noauth') {
    item.request.auth = { type: 'noauth' };
  }

  const exec = [];
  if (isLogin) exec.push(LOGIN_TEST);
  if (saveId && ['POST', 'PUT'].includes(method)) exec.push(saveIdScript(uri));

  const events = [];
  if (isLogin && emailVar && passVar) {
    events.push({
      listen: 'prerequest',
      script: {
        type: 'text/javascript',
        exec: [
          `const email = pm.environment.get("${emailVar}") || "";`,
          `const password = pm.environment.get("${passVar}") || "";`,
          'if (email) { pm.environment.set("email", email); }',
          'if (password) { pm.environment.set("password", password); }',
        ],
      },
    });
  }
  if (exec.length) {
    events.push({
      listen: 'test',
      script: { type: 'text/javascript', exec: exec.join('\n').split('\n') },
    });
  }
  if (events.length) item.event = events;

  if (isLogin) {
    item.response = [
      {
        name: '200 Success',
        originalRequest: item.request,
        status: 'OK',
        code: 200,
        _postman_previewlanguage: 'json',
        header: [{ key: 'Content-Type', value: 'application/json' }],
        body: JSON.stringify(
          {
            message: 'تم تسجيل الدخول بنجاح.',
            token: '1|examplePlainTextToken',
            token_type: 'Bearer',
            user: {
              id: 1,
              name: 'Example User',
              email: 'replace@example.com',
              roles: ['general_director'],
              permissions: ['view_users'],
              governorate_id: 1,
              branch_id: 1,
            },
          },
          null,
          2
        ),
      },
      {
        name: '401 Unauthenticated',
        originalRequest: item.request,
        status: 'Unauthorized',
        code: 401,
        _postman_previewlanguage: 'json',
        header: [{ key: 'Content-Type', value: 'application/json' }],
        body: JSON.stringify({ message: 'بيانات الدخول غير صحيحة.' }, null, 2),
      },
      {
        name: '422 Validation Error',
        originalRequest: item.request,
        status: 'Unprocessable Entity',
        code: 422,
        _postman_previewlanguage: 'json',
        header: [{ key: 'Content-Type', value: 'application/json' }],
        body: JSON.stringify(
          {
            message: 'The email field is required.',
            errors: { email: ['The email field is required.'] },
          },
          null,
          2
        ),
      },
    ];
  } else if (authMode === 'noauth' && method === 'GET') {
    item.response = [
      {
        name: '200 Success',
        originalRequest: item.request,
        status: 'OK',
        code: 200,
        _postman_previewlanguage: 'json',
        header: [{ key: 'Content-Type', value: 'application/json' }],
        body: JSON.stringify({ data: [] }, null, 2),
      },
    ];
  }

  return item;
}

function docsFor(route, mwInfo) {
  const action = route.action || '';
  return [
    'Purpose:',
    `${route.method} ${route.uri}`,
    '',
    'Controller:',
    action,
    '',
    'Required Role / Permission:',
    permissionLabel(mwInfo),
    '',
    'Authentication:',
    mwInfo.auth ? 'Bearer Token (Sanctum).' : 'No Auth (public).',
    '',
    'Path Parameters:',
    'Use environment variables for IDs (need_id, course_id, ...).',
    '',
    'Query Parameters:',
    'See disabled query params on GET requests (page, per_page, search, filters).',
    '',
    'Request Body:',
    ['POST', 'PUT', 'PATCH'].includes(route.method) ? 'See body example; align with FormRequest/controller validation.' : 'N/A',
    '',
    'Expected Success Status:',
    route.method === 'POST' ? '200 or 201' : route.method === 'DELETE' ? '200 or 204' : '200',
    '',
    'Possible Errors:',
    '401 Unauthenticated · 403 Forbidden · 404 Not Found · 422 Validation',
    '',
    'Notes:',
    'Policies and data scopes (branch/governorate/center) may further restrict results.',
  ].join('\n');
}

// Normalize routes
const endpoints = [];
for (const r of routesRaw) {
  const methods = splitMethods(r.method);
  for (const method of methods) {
    const mwInfo = parseMiddleware(r.middleware);
    endpoints.push({
      method,
      uri: r.uri,
      action: r.action,
      name: r.name,
      mwInfo,
      module: moduleFromUri(r.uri),
    });
  }
}

const publicEndpoints = endpoints.filter((e) => !e.mwInfo.auth);
const authEndpoints = endpoints.filter((e) => e.mwInfo.auth);

function folderFromItems(name, items, description) {
  return {
    name,
    description: description || '',
    item: items,
  };
}

function groupByModule(list) {
  const map = new Map();
  for (const ep of list) {
    if (!map.has(ep.module)) map.set(ep.module, []);
    map.get(ep.module).push(ep);
  }
  return [...map.entries()]
    .sort((a, b) => a[0].localeCompare(b[0]))
    .map(([mod, eps]) =>
      folderFromItems(
        mod,
        eps
          .sort((a, b) => a.uri.localeCompare(b.uri) || a.method.localeCompare(b.method))
          .map((ep) =>
            makeRequestItem({
              name: requestName(ep.method, ep.uri),
              method: ep.method,
              uri: ep.uri,
              authMode: ep.mwInfo.auth ? 'inherit' : 'noauth',
              description: docsFor(ep, ep.mwInfo),
              isLogin: false,
              saveId: true,
            })
          )
      )
    );
}

// Auth folder
const authFolder = folderFromItems('00 - Authentication', [
  makeRequestItem({
    name: 'Login',
    method: 'POST',
    uri: 'api/login',
    authMode: 'noauth',
    description: docsFor({ method: 'POST', uri: 'api/login', action: 'AuthController@login' }, { auth: false, roles: [], permissions: [], roleOrPermission: [], dashboardAccess: false }),
    isLogin: true,
  }),
  makeRequestItem({
    name: 'Register',
    method: 'POST',
    uri: 'api/register',
    authMode: 'noauth',
    description: 'Public self-registration. account_type from SelfRegistrationCatalog.',
    isLogin: true,
  }),
  makeRequestItem({
    name: 'Get Current User / Me',
    method: 'GET',
    uri: 'api/me',
    authMode: 'inherit',
    description: 'Returns { user } with roles and permissions.',
  }),
  makeRequestItem({
    name: 'Update Me',
    method: 'PUT',
    uri: 'api/me',
    authMode: 'inherit',
    description: 'Update name/phone.',
  }),
  makeRequestItem({
    name: 'Change Password',
    method: 'POST',
    uri: 'api/me/change-password',
    authMode: 'inherit',
    description: 'Authenticated password change. No public forgot/reset endpoints exist.',
  }),
  makeRequestItem({
    name: 'Logout',
    method: 'POST',
    uri: 'api/logout',
    authMode: 'inherit',
    description: 'Revokes current Sanctum token.',
  }),
], 'Sanctum Bearer token auth. No refresh-token or forgot-password routes in this API.');

const publicFolder = folderFromItems(
  '01 - Public APIs',
  groupByModule(publicEndpoints),
  'All routes without auth:sanctum. Authorization: No Auth.'
);

const catalogFolder = folderFromItems(
  '99 - Full Catalog by Module (Authenticated)',
  groupByModule(authEndpoints),
  'Complete authenticated catalog once (all sanctum routes). Prefer Actor folders for role-scoped testing.'
);

const actorFolders = ACTORS.map((actor) => {
  const perms = ROLE_PERMISSIONS[actor.key] || [];
  const allowed = authEndpoints.filter((ep) => roleCanAccess(actor.key, ep.mwInfo, ep.uri));
  // Always include profile basics
  const description = [
    `Role slug: \`${actor.key}\``,
    `Scope: ${actor.scope}`,
    `Permissions count (seeded): ${perms.length}`,
    `Key permissions: ${perms.slice(0, 12).join(', ')}${perms.length > 12 ? ', …' : ''}`,
    actor.demo ? `Demo email (local seed): ${actor.demo} — password in README (demo only).` : 'No fixed demo account in seeders.',
    'Restrictions: Spatie middleware + Policies + Branch/Need/Training data scopes may still return 403/empty.',
  ].join('\n');

  const login = makeRequestItem({
    name: `00 - Login as ${actor.key}`,
    method: 'POST',
    uri: 'api/login',
    authMode: 'noauth',
    description: `Login as ${actor.key}. Set ${actor.emailVar} / ${actor.passVar} in environment. On send: injects email+password+token into environment.`,
    emailExpr: `{{${actor.emailVar}}}`,
    passExpr: `{{${actor.passVar}}}`,
    isLogin: true,
    emailVar: actor.emailVar,
    passVar: actor.passVar,
  });

  return folderFromItems(actor.folder, [login, ...groupByModule(allowed)], description);
});

const collection = {
  info: {
    _postman_id: uid(),
    name: 'SMEDA API',
    description: [
      'Complete SMEDA Laravel API Postman collection.',
      `Generated from php artisan route:list (${endpoints.length} method+path endpoints).`,
      'Collection auth: Bearer {{token}}. Public + Login use No Auth.',
      'Import SMEDA-Local or SMEDA-Production environment, set credentials, run Login.',
    ].join('\n\n'),
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
  },
  auth: {
    type: 'bearer',
    bearer: [{ key: 'token', value: '{{token}}', type: 'string' }],
  },
  event: [
    {
      listen: 'test',
      script: {
        type: 'text/javascript',
        exec: [
          'pm.test("Response status is valid", function () {',
          '    pm.expect(pm.response.code).to.be.oneOf([',
          '        200, 201, 202, 204, 400, 401, 403, 404, 422',
          '    ]);',
          '});',
          '',
          'pm.test("Response is JSON when body exists", function () {',
          '    if (pm.response.text()) {',
          '        const contentType = pm.response.headers.get("Content-Type") || "";',
          '        pm.expect(contentType).to.include("application/json");',
          '    }',
          '});',
          '',
          'pm.test("Response time is acceptable", function () {',
          '    pm.expect(pm.response.responseTime).to.be.below(5000);',
          '});',
        ],
      },
    },
  ],
  variable: [
    { key: 'api_url', value: '{{base_url}}/api' },
  ],
  item: [authFolder, publicFolder, ...actorFolders, catalogFolder],
};

function envFile(name, values) {
  return {
    id: uid(),
    name,
    values: values.map((v) => ({
      key: v.key,
      value: v.value,
      type: 'default',
      enabled: true,
    })),
    _postman_variable_scope: 'environment',
  };
}

const baseVars = [
  { key: 'base_url', value: 'http://127.0.0.1:8000' },
  { key: 'api_url', value: '{{base_url}}/api' },
  { key: 'email', value: 'replace@example.com' },
  { key: 'password', value: 'CHANGE_ME' },
  { key: 'token', value: '' },
  { key: 'token_type', value: 'Bearer' },
  { key: 'user_id', value: '' },
  { key: 'actor', value: '' },
  { key: 'role', value: '' },
  { key: 'governorate_id', value: '1' },
  { key: 'branch_id', value: '1' },
  { key: 'center_id', value: '1' },
  { key: 'trainer_id', value: '1' },
  { key: 'trainee_id', value: '1' },
  { key: 'course_id', value: '1' },
  { key: 'certificate_id', value: '1' },
  { key: 'certificate_code', value: 'SMEDA-DEMO-0001' },
  { key: 'need_id', value: '1' },
  { key: 'application_id', value: '1' },
  { key: 'case_id', value: '1' },
  { key: 'finance_application_id', value: '1' },
  { key: 'kit_id', value: '1' },
  { key: 'loan_id', value: '1' },
  { key: 'news_id', value: '1' },
  { key: 'agreement_id', value: '1' },
  { key: 'incubator_id', value: '1' },
  { key: 'document_id', value: '1' },
  { key: 'id', value: '1' },
  { key: 'signature_code', value: 'SIG-DEMO-001' },
  { key: 'story_slug', value: 'sample-story' },
  { key: 'role_name', value: 'branch_manager' },
  { key: 'permission_name', value: 'view_users' },
];

for (const a of ACTORS) {
  baseVars.push({ key: a.emailVar, value: a.demo || 'replace@example.com' });
  baseVars.push({
    key: a.passVar,
    value: a.demo || a.demoPassword ? (a.demoPassword || '12345678') : 'CHANGE_ME',
  });
}

const localEnv = envFile('SMEDA Local', baseVars);
const prodEnv = envFile(
  'SMEDA Production',
  baseVars.map((v) => {
    if (v.key === 'base_url') return { ...v, value: 'https://smeda.gov.sy/api' };
    if (v.key === 'api_url') return { ...v, value: '{{base_url}}/api' };
    if (v.key.endsWith('_email') || v.key === 'email') return { ...v, value: 'replace@example.com' };
    if (v.key.endsWith('_password') || v.key === 'password') return { ...v, value: 'CHANGE_ME' };
    if (v.key === 'token') return { ...v, value: '' };
    return v;
  })
);

// Count requests
function countRequests(items) {
  let n = 0;
  for (const it of items) {
    if (it.request) n += 1;
    if (it.item) n += countRequests(it.item);
  }
  return n;
}

const requestCount = countRequests(collection.item);

fs.writeFileSync(outCollection, JSON.stringify(collection, null, 2));
fs.writeFileSync(outLocal, JSON.stringify(localEnv, null, 2));
fs.writeFileSync(outProd, JSON.stringify(prodEnv, null, 2));

// Coverage markdown
const coverageRows = endpoints.map((ep) => {
  const ctrl = (ep.action || '').replace(/^App\\\\Http\\\\Controllers\\\\Api\\\\/, '').replace(/App\\Http\\Controllers\\Api\\/, '');
  const [controller, action] = ctrl.includes('@') ? ctrl.split('@') : [ctrl, ''];
  let folder = ep.mwInfo.auth ? '99 - Full Catalog / Actor folders' : '01 - Public APIs';
  if (ep.uri === 'api/login' || ep.uri === 'api/register' || ep.uri === 'api/me' || ep.uri === 'api/logout' || ep.uri.startsWith('api/me/')) {
    folder = '00 - Authentication';
  }
  return `| ${ep.method} | /${ep.uri} | ${controller} | ${action || '—'} | ${ep.mwInfo.auth ? 'Yes' : 'No'} | ${permissionLabel(ep.mwInfo).replace(/\|/g, '\\|')} | ${folder} | Yes |`;
});

const unclear = endpoints.filter(
  (ep) =>
    ep.mwInfo.auth &&
    !ep.mwInfo.roles.length &&
    !ep.mwInfo.permissions.length &&
    !ep.mwInfo.roleOrPermission.length
);

const coverageMd = `# SMEDA API Coverage

Generated from \`php artisan route:list --path=api --json\`.

| Method | Endpoint | Controller | Action | Auth | Role/Permission | Postman Folder | Added |
|---|---|---|---|---|---|---|---|
${coverageRows.join('\n')}

---

## Summary

Total API routes found: **${endpoints.length}** (method + path; HEAD excluded)

Total API routes added to Postman: **${endpoints.length}** (via Public + Full Catalog; Actor folders contain role-filtered subsets)

Postman request items (including per-actor copies + auth helpers): **${requestCount}**

Missing routes: **none** (all discovered \`/api\` routes are in Public or Full Catalog)

Duplicate routes: Actor folders intentionally re-list endpoints the role can access; canonical unique set is Public + \`99 - Full Catalog\`.

Routes with unclear permissions (auth:sanctum only — further checks in controller/policy): **${unclear.length}**

${unclear
  .slice(0, 80)
  .map((e) => `- \`${e.method} /${e.uri}\` → ${e.action}`)
  .join('\n')}
${unclear.length > 80 ? `\n… and ${unclear.length - 80} more.` : ''}

## Non-API (excluded)

Signed print/PDF routes under \`routes/web.php\` (certificates/cards) are not under \`/api\` and were excluded from this collection.
`;

fs.writeFileSync(outCoverage, coverageMd);

const readme = `# SMEDA API — Postman

Professional Postman package for the Laravel backend in \`api2/\`.

## Files

| File | Purpose |
|------|---------|
| \`SMEDA-API.postman_collection.json\` | Full collection (auth, public, actors, full catalog) |
| \`SMEDA-Local.postman_environment.json\` | Local environment |
| \`SMEDA-Production.postman_environment.json\` | Production environment |
| \`API-COVERAGE.md\` | Route-by-route coverage table |
| \`generate-collection.mjs\` | Regenerator (optional) |

## 1. Import Collection

Postman → **Import** → select \`SMEDA-API.postman_collection.json\`.

## 2. Import Environment

Import \`SMEDA-Local.postman_environment.json\` (or Production).

## 3. Select Environment

Top-right environment dropdown → **SMEDA Local**.

## 4. Credentials

Set \`email\` / \`password\`, or actor-specific variables such as \`general_director_email\`.

Do **not** commit real passwords. Placeholders use \`CHANGE_ME\`.

### Local demo accounts (from seeders)

Default demo password used by \`UserSeeder\` / GIS seeders: \`12345678\` (local/dev only).

| Email | Typical role |
|-------|----------------|
| admin@system.com | admin + general_director |
| general@system.com | general_director |
| deputy@system.com | deputy_director / deputy_general_director |
| branch.damascus@system.com | branch_manager |
| governor.tartus@system.com | governor |
| manager@system.com | training_manager |
| center@system.com | center_user |
| trainer@system.com | trainer_user |
| trainee@system.com | trainee_user |
| auditor@system.com | auditor |
| projects@system.com | project_services_manager |
| data-entry.damascus@system.com | data_entry |
| data-reviewer.damascus@system.com | data_reviewer |
| central.bank@system.com | central_bank_admin |
| finance.manager@system.com | finance_manager |

## 5. Login and token

Run **00 - Authentication → Login** (or an actor’s \`00 - Login as …\`).

Tests save \`token\`, \`token_type\`, \`user_id\`, \`role\`, \`actor\`, \`governorate_id\`, \`branch_id\`, center/trainer/trainee ids when present.

Collection auth is **Bearer {{token}}**.

## 6. Test a specific Actor

1. Set that actor’s email/password variables.
2. Run \`00 - Login as <role>\` inside the actor folder.
3. Run requests under that folder only (they are filtered by route middleware vs seeded permissions).

## 7. Collection Runner

Runner → select folder (e.g. \`07 - Branch Manager\`) → run. Ensure Login is first.

## 8. Change base_url

| Env | base_url | api_url |
|-----|----------|---------|
| Local | \`http://127.0.0.1:8000\` | \`{{base_url}}/api\` |
| Production | \`https://smeda.gov.sy/api\` | \`{{base_url}}/api\` → final \`https://smeda.gov.sy/api/api\` |

Production path matches Hostinger layout documented in \`docs/api/README.md\` / deploy notes (\`/api\` public folder + Laravel \`/api\` prefix). Adjust if your host differs (e.g. \`https://new.smeda.gov.sy/api2/public\`).

## 9. File uploads

Requests that accept files use **form-data** with a \`file\` field. Choose a local file in Postman. Server rate-limits uploads (\`throttle:file-upload\`).

## 10. Tests

Collection-level tests check status code set, JSON content-type, and response time < 5s.

Login/create scripts persist IDs into the environment when present.

## 11. Roles in project (32)

\`general_director\`, \`admin\`, \`deputy_general_director\`, \`governor\`, \`branch_manager\`, \`branch_officer\`, \`workforce_manager\`, \`training_manager\`, \`training_supervisor\`, \`deputy_director\`, \`center_user\`, \`trainer_user\`, \`trainee_user\`, \`auditor\`, \`data_entry\`, \`data_reviewer\`, \`project_services_manager\`, \`development_manager\`, \`local_development_manager\`, \`finance_manager\`, \`finance_officer\`, \`consultant_office\`, \`funding_partner\`, \`consultant_union_admin\`, \`central_bank_admin\`, \`project_owner\`, \`incubator_manager\`, \`incubator_mentor\`, \`entrepreneur_manager\`, \`media_manager\`, \`super_admin\`, \`system_admin\`.

Source: \`database/seeders/RolePermissionSeeder.php\` (no \`RolesAndPermissionsSeeder.php\`).

## 12. Scope notes

- **National full:** \`general_director\`, \`admin\`, \`super_admin\`, deputies — all permissions.
- **Branch:** \`branch_manager\` / officers / data_* scoped by \`branch_id\`.
- **Governor:** \`governorate_id\` for needs and consulting patterns.
- **Center/Trainer/Trainee:** entity ids on the user.
- **system_admin:** access-admin permissions only.

Exact enforcement: Spatie middleware on routes + Policies + \`AccessControlGuard\` / data scopes.

## 13. Not added / limitations

- \`routes/web.php\` signed print/PDF/QR pages (not JSON API).
- No forgot/reset password or refresh-token endpoints exist in API.
- Actor folders filter by **route middleware** vs **seeded role permissions**. Routes that are \`auth:sanctum\` only appear under all actors; Policies may still deny.
- Bodies are representative examples — always confirm against FormRequest/controller validation for production tests.

## Regenerate

\`\`\`bash
cd api2
php artisan route:list --path=api --json > ../postman/_routes_raw.json
cd ../postman
node generate-collection.mjs
\`\`\`

## Counts (this generation)

- Discovered endpoints: see \`API-COVERAGE.md\`
- Actor folders: ${ACTORS.length}
- Postman request items: ${requestCount}
`;

fs.writeFileSync(outReadme, readme);

console.log(JSON.stringify({
  endpoints: endpoints.length,
  public: publicEndpoints.length,
  authenticated: authEndpoints.length,
  actors: ACTORS.length,
  requestItems: requestCount,
  unclearPermissions: unclear.length,
  files: [
    'SMEDA-API.postman_collection.json',
    'SMEDA-Local.postman_environment.json',
    'SMEDA-Production.postman_environment.json',
    'README.md',
    'API-COVERAGE.md',
  ],
}, null, 2));
