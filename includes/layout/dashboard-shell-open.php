<?php
/**
 * فتح قوقعة الداشبورد (ds-layout + sidebar + topbar + content).
 * يُستدعى من app-shell-open عند وجود authority_shell_role.
 */
$basePath = isset($basePath) ? $basePath : '';
if (empty($dashboardRole)) {
    require_once __DIR__ . '/shell-preference.php';
    $dashboardRole = authority_shell_role() ?: 'project_services_manager';
}

$dsDefaultTitles = [
    'project_services_manager' => 'SMEDA — خدمات المشروعات',
    'training_manager'         => 'SMEDA — بنك الحقائب',
    'branch_manager'           => 'SMEDA — إدارة الفرع',
    'incubator_manager'        => 'SMEDA — إدارة الحاضنة',
    'media_manager'            => 'SMEDA — الإعلام',
    'entrepreneur_manager'     => 'SMEDA — ريادة الأعمال',
    'general_director'         => 'SMEDA — الإدارة العامة',
];
if (empty($sidebarTitle)) {
    $sidebarTitle = $dsDefaultTitles[$dashboardRole] ?? 'SMEDC';
}

$pageTitleHint = isset($pageTitle) ? (string) $pageTitle : '';
?>
<style>
  /* جسر توافق: صفحات app-shell تعتمد .app-main بهامش يمين — مع ds-main لا نكرّره */
  .ds-layout {
    --sidebar-w: 260px;
    --topbar-h: 58px;
  }
  .ds-layout .ds-content.app-main,
  .ds-layout .app-main {
    margin-right: 0 !important;
    margin-left: 0 !important;
  }
  .ds-layout .ds-content {
    min-width: 0;
  }
</style>
<div class="ds-layout" id="appShellRoot" data-shell="dashboard">
<?php include __DIR__ . '/../partials/dashboard-sidebar.php'; ?>

<div class="ds-main">
  <div class="ds-topbar">
    <button class="ds-hamburger" id="dsHamburger" type="button" aria-label="القائمة">
      <i class="bi bi-list"></i>
    </button>
    <div class="ds-topbar-title" id="dsTopbarTitle"><?php echo htmlspecialchars($pageTitleHint, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ds-topbar-right">
      <div class="ds-topbar-user">
        <span id="dsTopbarName"></span>
        <div class="ds-topbar-avatar" id="dsTopbarAvatar">م</div>
      </div>
    </div>
  </div>

  <div class="ds-content app-main">
