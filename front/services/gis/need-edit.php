<?php
$basePath = '../../';
$activePage = 'need-edit';
$pageTitle = 'تعديل احتياج';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
</head>
<body>
<?php include $basePath . 'includes/layout/header.php'; ?>
<main>
  <section class="services-hero"><div class="container"><h1 class="fw-bold mb-2">تعديل احتياج</h1></div></section>
  <section class="section pt-0">
    <div class="container">
      <div id="needEditMessage" class="alert d-none"></div>
      <form id="needEditForm" class="row g-3 d-none">
        <div class="col-md-8"><label class="form-label">العنوان</label><input name="title" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">القطاع</label><input name="sector" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">نوع الاحتياج</label><input name="need_type" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">الأولوية</label><input name="priority" class="form-control"></div>
        <div class="col-12"><label class="form-label">الوصف</label><textarea name="description" class="form-control" rows="4"></textarea></div>
        <div class="col-12"><button type="submit" class="btn btn-brand">حفظ التعديلات</button></div>
      </form>
    </div>
  </section>
</main>
<?php include $basePath . 'includes/layout/footer.php'; ?>
<?php include $basePath . 'includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/need-edit.js?v=1.0"></script>
</body>
</html>
