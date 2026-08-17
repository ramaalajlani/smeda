<?php
/**
 * Reusable Dashboard Sidebar
 * Variables expected from parent:
 *   $basePath       — e.g. '../../'
 *   $dashboardRole  — 'branch_manager' | 'incubator_manager' | ...
 *   $activePage     — slug of current page for active link
 *   $sidebarTitle   — (optional) override sidebar title
 */
if (!function_exists('__')) {
    require_once __DIR__ . '/../i18n/bootstrap.php';
}

$dr = $dashboardRole ?? 'branch_manager';

if (!function_exists('ds_menu_label')) {
    function ds_menu_label(array $item): string
    {
        if (!empty($item['label_key'])) {
            return __($item['label_key']);
        }
        return $item['label'] ?? '';
    }
}

// Theme per role
$themes = [
  'branch_manager'       => ['--ds-primary:#17947B','--ds-accent:#06AA89','--ds-soft:#EAF8F4','--ds-border:rgba(23,148,123,.15)','--ds-accent-color:#06AA89','--ds-sidebar-bg:linear-gradient(180deg,#062824 0%,#0a3d35 50%,#17947B 100%)'],
  'incubator_manager'    => ['--ds-primary:#7c3aed','--ds-accent:#a78bfa','--ds-soft:#f5f3ff','--ds-border:rgba(124,58,237,.15)','--ds-accent-color:#a78bfa','--ds-sidebar-bg:linear-gradient(180deg,#1e1b4b 0%,#312e81 50%,#4c1d95 100%)'],
  'media_manager'        => ['--ds-primary:#0369a1','--ds-accent:#38bdf8','--ds-soft:#e0f2fe','--ds-border:rgba(3,105,161,.15)','--ds-accent-color:#38bdf8','--ds-sidebar-bg:linear-gradient(180deg,#0c1a2e 0%,#0f3460 50%,#0369a1 100%)'],
  'entrepreneur_manager' => ['--ds-primary:#d97706','--ds-accent:#f59e0b','--ds-soft:#fffbeb','--ds-border:rgba(217,119,6,.15)','--ds-accent-color:#f59e0b','--ds-sidebar-bg:linear-gradient(180deg,#1c0a00 0%,#451a03 50%,#92400e 100%)'],
  'general_director'     => ['--ds-primary:#17947B','--ds-accent:#06AA89','--ds-soft:#EAF8F4','--ds-border:rgba(23,148,123,.15)','--ds-accent-color:#06AA89','--ds-sidebar-bg:linear-gradient(180deg,#062824 0%,#0a3d35 50%,#17947B 100%)'],
  'training_manager'     => ['--ds-primary:#1a6b5a','--ds-accent:#10b981','--ds-soft:#e8f8f3','--ds-border:rgba(26,107,90,.15)','--ds-accent-color:#10b981','--ds-sidebar-bg:linear-gradient(180deg,#0a2e26 0%,#0d4d3f 50%,#1a6b5a 100%)'],
  'project_services_manager' => ['--ds-primary:#0f766e','--ds-accent:#14b8a6','--ds-soft:#effcfa','--ds-border:rgba(15,118,110,.15)','--ds-accent-color:#14b8a6','--ds-sidebar-bg:linear-gradient(180deg,#042f2e 0%,#0a4f4a 50%,#0f766e 100%)'],
];
$themeVars = implode(';', $themes[$dr] ?? $themes['branch_manager']);

// Icons per role
$roleIcons = [
  'branch_manager'       => 'bi-diagram-3-fill',
  'incubator_manager'    => 'bi-building',
  'media_manager'        => 'bi-newspaper',
  'entrepreneur_manager' => 'bi-rocket-takeoff-fill',
  'general_director'     => 'bi-shield-fill-check',
  'training_manager'     => 'bi-collection-fill',
  'project_services_manager' => 'bi-grid-1x2-fill',
];

// Menu definitions per role
$menus = [
  'branch_manager' => [
    ['group_key' => 'ds_group_main'],
    ['href' => $basePath.'services/admin/branch-manager-dashboard.php', 'icon' => 'bi-speedometer2',        'label_key' => 'ds_dashboard'],
    ['href' => $basePath.'services/incubation/incubators.php',          'icon' => 'bi-building',            'label_key' => 'ds_incubators'],
    ['href' => $basePath.'services/incubation/incubation-apply.php',    'icon' => 'bi-file-earmark-plus',   'label_key' => 'ds_new_request'],
    ['group' => 'منظومة الاحتياجات'],
    ['href' => $basePath.'services/gis/needs-map.php',                  'icon' => 'bi-geo-alt-fill',        'label' => 'الخريطة التفاعلية'],
    ['href' => $basePath.'services/gis/needs-list.php',                 'icon' => 'bi-clipboard-data-fill', 'label' => 'تفاصيل الاحتياجات'],
    ['href' => $basePath.'services/gis/needs-dashboard.php',            'icon' => 'bi-signpost-split-fill', 'label' => 'سير المعالجة (البروسس)'],
    ['href' => $basePath.'services/gis/need-create.php',                'icon' => 'bi-plus-circle-fill',    'label' => 'تسجيل احتياج'],
    ['group' => 'التمويل'],
    ['href' => $basePath.'services/finance/finance-applications-list.php','icon' => 'bi-inboxes-fill',      'label' => 'طلبات التمويل'],
    ['href' => $basePath.'services/finance/finance-cloud.php',          'icon' => 'bi-cloud-fill',         'label' => 'سحابة التمويل'],
    ['group_key' => 'ds_group_admin'],
    ['href' => $basePath.'services/incubation/incubated-projects.php',  'icon' => 'bi-kanban-fill',         'label_key' => 'ds_incubated_projects'],
    ['href' => $basePath.'services/incubation/project-mentoring.php',   'icon' => 'bi-people-fill',         'label_key' => 'ds_mentoring'],
    ['href' => $basePath.'services/incubation/incubation-reports.php',  'icon' => 'bi-bar-chart-fill',      'label_key' => 'ds_reports'],
    ['group_key' => 'ds_group_content'],
    ['href' => $basePath.'services/incubation/success-stories.php',     'icon' => 'bi-trophy-fill',         'label_key' => 'ds_success_stories'],
    ['href' => $basePath.'services/incubation/entrepreneurship-hub.php','icon' => 'bi-rocket-fill',         'label_key' => 'ds_entrepreneurship_hub'],
  ],
  'incubator_manager' => [
    ['group_key' => 'ds_group_main'],
    ['href' => $basePath.'services/admin/incubator-manager-dashboard.php','icon' => 'bi-speedometer2',      'label_key' => 'ds_dashboard'],
    ['href' => $basePath.'services/incubation/incubators.php',           'icon' => 'bi-search',             'label_key' => 'ds_incubators'],
    ['group_key' => 'ds_incubator_mgmt'],
    ['href' => $basePath.'services/incubation/incubated-projects.php',   'icon' => 'bi-kanban-fill',        'label_key' => 'ds_projects'],
    ['href' => $basePath.'services/incubation/project-mentoring.php',    'icon' => 'bi-people-fill',        'label_key' => 'ds_mentoring_sessions'],
    ['href' => $basePath.'services/incubation/incubation-reports.php',   'icon' => 'bi-bar-chart-fill',     'label_key' => 'ds_reports'],
    ['group_key' => 'ds_entrepreneurs'],
    ['href' => $basePath.'services/incubation/entrepreneur-profile.php', 'icon' => 'bi-clipboard2-fill',    'label_key' => 'ds_new_survey'],
    ['href' => $basePath.'services/incubation/success-stories.php',      'icon' => 'bi-trophy-fill',        'label_key' => 'ds_success_stories'],
  ],
  'media_manager' => [
    ['group_key' => 'ds_group_main'],
    ['href' => $basePath.'services/admin/media-manager-dashboard.php',  'icon' => 'bi-speedometer2',        'label_key' => 'ds_dashboard'],
    ['group_key' => 'ds_news'],
    ['href' => $basePath.'services/admin/media-manager-dashboard.php',  'icon' => 'bi-newspaper',           'label_key' => 'ds_news_mgmt'],
    ['group_key' => 'ds_quick_links'],
    ['href' => $basePath.'index.php',                                    'icon' => 'bi-house-fill',          'label_key' => 'ds_home'],
    ['href' => $basePath.'services/incubation/success-stories.php',     'icon' => 'bi-trophy-fill',         'label_key' => 'ds_success_stories'],
  ],
  'entrepreneur_manager' => [
    ['group_key' => 'ds_group_main'],
    ['href' => $basePath.'services/admin/entrepreneur-manager-dashboard.php','icon' => 'bi-speedometer2',   'label_key' => 'ds_dashboard'],
    ['group_key' => 'ds_entrepreneurs'],
    ['href' => $basePath.'services/admin/entrepreneur-manager-dashboard.php','icon' => 'bi-people-fill',    'label_key' => 'ds_surveys'],
    ['href' => $basePath.'services/incubation/entrepreneurship-hub.php',    'icon' => 'bi-rocket-fill',     'label_key' => 'ds_entrepreneurship_hub'],
    ['group_key' => 'ds_group_incubators'],
    ['href' => $basePath.'services/incubation/incubators.php',              'icon' => 'bi-building',        'label_key' => 'ds_incubators'],
    ['href' => $basePath.'services/incubation/success-stories.php',         'icon' => 'bi-trophy-fill',     'label_key' => 'ds_success_stories'],
  ],
  'general_director' => [
    ['group_key' => 'ds_group_main'],
    ['href' => $basePath.'dashboard.php',                                          'icon' => 'bi-speedometer2',    'label' => 'اللوحة الرئيسية'],
    ['group' => 'منظومة الاحتياجات'],
    ['href' => $basePath.'services/gis/needs-map.php',                             'icon' => 'bi-geo-alt-fill',        'label' => 'الخريطة التفاعلية'],
    ['href' => $basePath.'services/gis/needs-list.php',                            'icon' => 'bi-clipboard-data-fill', 'label' => 'تفاصيل الاحتياجات'],
    ['href' => $basePath.'services/gis/needs-dashboard.php',                       'icon' => 'bi-signpost-split-fill', 'label' => 'سير المعالجة'],
    ['href' => $basePath.'services/gis/need-create.php',                           'icon' => 'bi-plus-circle-fill',    'label' => 'تسجيل احتياج'],
    ['group' => 'التمويل'],
    ['href' => $basePath.'services/finance/finance-applications-list.php',         'icon' => 'bi-inboxes-fill',        'label' => 'طلبات التمويل'],
    ['href' => $basePath.'services/finance/finance-cloud.php',                     'icon' => 'bi-cloud-fill',           'label' => 'سحابة التمويل'],
    ['href' => $basePath.'services/finance/finance-manager-dashboard.php',         'icon' => 'bi-speedometer2',         'label' => 'لوحة المالية'],
    ['group' => 'التدريب والمراكز'],
    ['href' => $basePath.'services/training/training-centers-list.php',            'icon' => 'bi-buildings-fill',   'label' => 'المراكز التدريبية'],
    ['href' => $basePath.'services/training/training-programs-list.php',           'icon' => 'bi-book-fill',        'label' => 'البرامج التدريبية'],
    ['href' => $basePath.'services/training/training-courses-list.php',            'icon' => 'bi-calendar-event',   'label' => 'الدورات'],
    ['href' => $basePath.'services/training/training-trainers-list.php',           'icon' => 'bi-person-badge-fill','label' => 'المدربون'],
    ['href' => $basePath.'services/training/training-certificates-list.php',       'icon' => 'bi-patch-check-fill', 'label' => 'الشهادات'],
    ['href' => $basePath.'services/training/training-certificates-approve.php',   'icon' => 'bi-person-fill-check', 'label' => 'اعتماد الشهادات'],
    ['href' => $basePath.'services/training/registration-requests-review.php',     'icon' => 'bi-inbox-fill',       'label' => 'طلبات التسجيل'],
    ['group' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-bags-hub.php',              'icon' => 'bi-briefcase-fill',   'label' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-kits-list.php',             'icon' => 'bi-list-ul',          'label' => 'جميع الحقائب'],
    ['href' => $basePath.'services/training/training-bag-form.php',              'icon' => 'bi-plus-circle-fill', 'label' => 'إضافة حقيبة'],
    ['href' => $basePath.'services/training/training-categories-manage.php',     'icon' => 'bi-diagram-3-fill',   'label' => 'تصنيفات الحقائب'],
    ['group_key' => 'ds_program_bank'],
    ['href' => $basePath.'services/training/program-bank-dashboard.php',           'icon' => 'bi-collection-fill', 'label_key' => 'ds_bank_dashboard'],
    ['href' => $basePath.'services/training/program-bank-list.php',                'icon' => 'bi-list-ul',         'label_key' => 'ds_programs_list'],
    ['href' => $basePath.'services/training/program-bank-form.php',                'icon' => 'bi-plus-circle-fill','label_key' => 'ds_new_program'],
  ],
  'training_manager' => [
    ['group' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-bags-hub.php',         'icon' => 'bi-briefcase-fill',   'label' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-kits-list.php',        'icon' => 'bi-list-ul',          'label' => 'جميع الحقائب'],
    ['href' => $basePath.'services/training/training-bag-form.php',         'icon' => 'bi-plus-circle-fill', 'label' => 'إضافة حقيبة'],
    ['href' => $basePath.'services/training/training-categories-manage.php','icon' => 'bi-diagram-3-fill',   'label' => 'تصنيفات الحقائب'],
    ['group_key' => 'ds_program_bank'],
    ['href' => $basePath.'services/training/program-bank-dashboard.php', 'icon' => 'bi-speedometer2',     'label_key' => 'ds_bank_dashboard'],
    ['href' => $basePath.'services/training/program-bank-list.php',      'icon' => 'bi-collection',       'label_key' => 'ds_all_programs'],
    ['href' => $basePath.'services/training/program-bank-form.php',      'icon' => 'bi-plus-circle-fill', 'label_key' => 'ds_new_program'],
    ['group_key' => 'ds_by_status'],
    ['href' => $basePath.'services/training/program-bank-list.php?bank_status=draft',                   'icon' => 'bi-pencil-square',      'label_key' => 'ds_drafts'],
    ['href' => $basePath.'services/training/program-bank-list.php?bank_status=under_technical_review',  'icon' => 'bi-search',             'label_key' => 'ds_technical_review'],
    ['href' => $basePath.'services/training/program-bank-list.php?bank_status=under_admin_review',      'icon' => 'bi-person-check',       'label_key' => 'ds_admin_review'],
    ['href' => $basePath.'services/training/program-bank-list.php?bank_status=approved',                'icon' => 'bi-check-circle-fill',  'label_key' => 'ds_approved'],
    ['group_key' => 'ds_training'],
    ['href' => $basePath.'services/training/training-programs-list.php', 'icon' => 'bi-book-fill',        'label_key' => 'ds_training_programs'],
    ['href' => $basePath.'services/training/training-courses-list.php',  'icon' => 'bi-calendar-event',   'label_key' => 'ds_courses'],
  ],
  'project_services_manager' => [
    ['group' => 'الرئيسية'],
    ['href' => $basePath.'services/admin/project-services-manager-dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'لوحة التحكم'],
    ['group' => 'التدريب والتأهيل'],
    ['href' => $basePath.'services/training/training-programs-list.php',  'icon' => 'bi-book-fill',           'label' => 'البرامج التدريبية'],
    ['href' => $basePath.'services/training/training-courses-list.php',   'icon' => 'bi-calendar-event',      'label' => 'الدورات'],
    ['href' => $basePath.'services/training/training-trainers-list.php',  'icon' => 'bi-person-badge-fill',   'label' => 'المدربون'],
    ['href' => $basePath.'services/training/training-trainees-list.php',  'icon' => 'bi-people-fill',         'label' => 'المتدربون'],
    ['href' => $basePath.'services/training/training-certificates-list.php','icon' => 'bi-patch-check-fill',  'label' => 'الشهادات'],
    ['group' => 'المراكز التدريبية'],
    ['href' => $basePath.'services/training/training-centers-list.php',   'icon' => 'bi-buildings-fill',      'label' => 'المراكز التدريبية'],
    ['href' => $basePath.'services/training/registration-requests-review.php','icon' => 'bi-inbox-fill',      'label' => 'طلبات التسجيل'],
    ['group' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-bags-hub.php',         'icon' => 'bi-briefcase-fill',     'label' => 'الحقائب التدريبية'],
    ['href' => $basePath.'services/training/training-kits-list.php',        'icon' => 'bi-list-ul',            'label' => 'جميع الحقائب'],
    ['href' => $basePath.'services/training/training-bag-form.php',         'icon' => 'bi-plus-circle-fill',   'label' => 'إضافة حقيبة'],
    ['href' => $basePath.'services/training/training-categories-manage.php','icon' => 'bi-diagram-3-fill',      'label' => 'تصنيفات الحقائب'],
    ['href' => $basePath.'services/training/program-bank-dashboard.php',  'icon' => 'bi-collection-fill',     'label' => 'بنك البرامج'],
    ['href' => $basePath.'services/training/program-bank-list.php',       'icon' => 'bi-journal-bookmark',    'label' => 'برامج بنك الحقائب'],
    ['href' => $basePath.'services/training/program-bank-form.php',       'icon' => 'bi-file-earmark-plus',   'label' => 'برنامج جديد'],
    ['group' => 'الخريطة والاحتياجات'],
    ['href' => $basePath.'services/gis/needs-map.php',                    'icon' => 'bi-map-fill',            'label' => 'خريطة الاحتياجات'],
    ['href' => $basePath.'services/gis/needs-dashboard.php',              'icon' => 'bi-bar-chart-fill',      'label' => 'لوحة الاحتياجات'],
    ['href' => $basePath.'services/gis/data-entry-dashboard.php',         'icon' => 'bi-pencil-square',       'label' => 'إدخال البيانات'],
    ['href' => $basePath.'services/gis/data-reviewer-dashboard.php',      'icon' => 'bi-clipboard-check',     'label' => 'تدقيق البيانات'],
    ['group' => 'الفروع والحسابات'],
    ['href' => $basePath.'services/admin/my-children.php',                'icon' => 'bi-people-fill',         'label' => 'حسابات المحافظين والمدخلين'],
    ['href' => $basePath.'services/admin/admin-branches.php',             'icon' => 'bi-building',            'label' => 'المحافظات والفروع'],
    ['group' => 'الاستشارات'],
    ['href' => '#ai-chat', 'icon' => 'bi-robot', 'label' => 'المستشار الذكي', 'open_ai_chat' => true],
    ['href' => $basePath.'services/consulting/consulting-admin-dashboard.php','icon' => 'bi-clipboard-data-fill','label' => 'لوحة الاستشارات'],
    ['href' => $basePath.'services/consulting/consulting-requests-list.php',  'icon' => 'bi-chat-left-text-fill','label' => 'طلبات الاستشارة'],
    ['href' => $basePath.'services/consulting/consulting-offices-list.php',   'icon' => 'bi-building-fill',    'label' => 'المكاتب الاستشارية'],
  ],
];

// تعميم قائمة المدير العام الكاملة على أدوار الإدارة العليا (نائب/أدمن)
foreach (['admin', 'super_admin', 'system_admin', 'deputy_general_director', 'deputy_director'] as $adminRole) {
    if (!isset($menus[$adminRole]) && isset($menus['general_director'])) {
        $menus[$adminRole] = $menus['general_director'];
    }
}

$currentMenu = $menus[$dr] ?? $menus['branch_manager'];
$title       = $sidebarTitle ?? 'SMEDC';
$roleKeys = [
  'branch_manager' => 'role_branch_manager',
  'incubator_manager' => 'role_incubator_manager',
  'media_manager' => 'role_media_manager',
  'entrepreneur_manager' => 'role_entrepreneur_manager',
  'general_director' => 'role_general_director',
  'training_manager' => 'role_training_manager',
  'project_services_manager' => 'role_project_services_manager',
];
$roleLabel = __($roleKeys[$dr] ?? 'role_supervisor');
?>
<style>:root{<?php echo $themeVars; ?>}</style>

<aside class="ds-sidebar" id="dsSidebar">
  <div class="ds-sidebar-inner">

    <!-- Brand -->
    <div class="ds-brand">
      <div class="ds-brand-logo"><i class="bi <?php echo $roleIcons[$dr]??'bi-grid-fill'; ?>"></i></div>
      <div class="ds-brand-text">
        <div class="ds-brand-title"><?php echo htmlspecialchars($title); ?></div>
        <div class="ds-brand-sub"><?php echo $roleLabel; ?></div>
      </div>
    </div>

    <!-- User -->
    <div class="ds-user">
      <div class="ds-user-avatar" id="dsUserAvatar">م</div>
      <div class="ds-user-info">
        <div class="ds-user-name" id="dsUserName" data-i18n="ds_loading"><?php echo htmlspecialchars(__('ds_loading')); ?></div>
        <div class="ds-user-role" id="dsUserRole"><?php echo $roleLabel; ?></div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="ds-nav">
      <?php foreach ($currentMenu as $item): ?>
        <?php if (isset($item['group_key']) || isset($item['group'])): ?>
          <div class="ds-nav-label"><?php echo htmlspecialchars(isset($item['group_key']) ? __($item['group_key']) : ($item['group'] ?? '')); ?></div>
        <?php else: ?>
          <a href="<?php echo htmlspecialchars($item['href']); ?>"
             class="ds-nav-item <?php echo ($activePage ?? '') === basename($item['href'], '.php') ? 'active' : ''; ?>"
             <?php if (!empty($item['open_ai_chat'])): ?>data-open-ai-chat="1"<?php endif; ?>>
            <i class="bi <?php echo $item['icon']; ?>"></i>
            <?php echo htmlspecialchars(ds_menu_label($item)); ?>
            <?php if (!empty($item['badge'])): ?>
              <span class="ds-nav-badge"><?php echo $item['badge']; ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="ds-nav-divider"></div>

    <!-- Footer -->
    <div class="ds-sidebar-footer">
      <?php include __DIR__ . '/lang-switch.php'; ?>
      <a href="<?php echo $basePath; ?>" class="ds-nav-item">
        <i class="bi bi-house-fill"></i> <?php echo htmlspecialchars(__('ds_home')); ?>
      </a>
      <button class="ds-logout-btn" type="button">
        <i class="bi bi-box-arrow-right"></i> <?php echo htmlspecialchars(__('ds_logout')); ?>
      </button>
    </div>

  </div>
</aside>

<!-- Mobile overlay -->
<div class="ds-overlay" id="dsOverlay"></div>
