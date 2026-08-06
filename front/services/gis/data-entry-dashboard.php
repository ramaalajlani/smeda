<?php
$basePath = '../../';
$activePage = 'needs-map';
$pageTitle = 'لوحة إدخال الاحتياجات';
$dashboardRoles = ['data_entry'];
$dashboardApiCall = 'needsDataEntryWorkspace';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?></head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<?php include $basePath . 'includes/sections/role-dashboard-shell.php'; ?>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/modules/role-dashboard.js?v=1.1"></script>
</body>
</html>
