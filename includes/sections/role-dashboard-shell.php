<?php
/**
 * Shared role dashboard shell — set $pageTitle, $dashboardRoles, $dashboardPermissions before include.
 */
$dashboardRoles = $dashboardRoles ?? [];
$dashboardPermissions = $dashboardPermissions ?? [];
$dashboardApi = $dashboardApi ?? null;
$dashboardApiCall = $dashboardApiCall ?? null;
?>
<section class="services-hero">
  <div class="container">
    <h1 class="fw-bold" id="roleDashboardTitle"><?php echo htmlspecialchars($pageTitle ?? 'لوحة التحكم'); ?></h1>
    <p class="mb-0 text-muted">مؤشرات ضمن نطاق صلاحياتك فقط</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div id="roleDashboardLoading" class="text-center py-4">جاري التحميل...</div>
    <div class="row g-3" id="roleDashboardCards"></div>
    <div id="roleDashboardRecent"></div>
    <div id="roleDashboardLinks" class="mt-3 d-flex gap-2 flex-wrap"></div>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  window.AppRoleDashboard?.init({
    basePath: <?php echo json_encode($basePath ?? ''); ?>,
    requiredRoles: <?php echo json_encode(array_values($dashboardRoles)); ?>,
    requiredPermissions: <?php echo json_encode(array_values($dashboardPermissions)); ?>,
    apiUrl: <?php
      if (!empty($dashboardApi)) {
          echo json_encode($dashboardApi);
      } elseif (!empty($dashboardApiCall)) {
          echo "window.APP_ROUTES['" . addslashes($dashboardApiCall) . "']()";
      } else {
          echo 'undefined';
      }
    ?>,
  });
});
</script>
