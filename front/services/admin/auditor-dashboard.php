<?php
$basePath = '../../';
$activePage = 'admin';
$pageTitle = 'لوحة التدقيق';
$dashboardRoles = ['auditor'];
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include $basePath . 'includes/layout/head.php'; ?></head>
<body>
<?php include $basePath . 'includes/layout/header.php'; ?>
<main>
<?php include $basePath . 'includes/sections/role-dashboard-shell.php'; ?>
</main>
<?php include $basePath . 'includes/layout/footer.php'; ?>
<?php include $basePath . 'includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/modules/role-dashboard.js?v=1.0"></script>
</body>
</html>
