<?php
$basePath = '../../';
$pageTitle = 'إدارة الحقيبة';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
  <style>
    .kit-manage-card{background:#fff;border:1px solid #e9eef5;border-radius:18px;padding:22px;box-shadow:0 10px 30px rgba(15,23,42,.04);margin-bottom:20px}
    .kit-chips{max-height:280px;overflow:auto;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#f8fafc}
    .kit-chip{display:flex;align-items:flex-start;gap:10px;padding:8px 6px;border-bottom:1px solid #eef2f7}
    .kit-chip:last-child{border-bottom:0}
    .kit-chip input{margin-top:4px}
  </style>
</head>
<body>
<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">
  <div class="container">
    <div class="breadcrumb-soft">
      <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
      <span>/</span>
      <a href="<?php echo $basePath; ?>services/training/training-kits-list.php">الحقائب التدريبية</a>
      <span>/</span>
      <span>إدارة الحقيبة</span>
    </div>
    <div class="row g-4 align-items-center">
      <div class="col-lg-9">
        <span class="section-badge">قسم التدريب</span>
        <h1 class="fw-bold mb-2" id="kitTitle">إدارة الحقيبة</h1>
        <p class="section-subtitle mb-0" id="kitSub">تكليف المراكز والمدربين بهذه الحقيبة.</p>
      </div>
      <div class="col-lg-3 text-lg-end">
        <a class="soft-btn" href="training-kits-list.php">رجوع للقائمة</a>
      </div>
    </div>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div id="msg" class="alert d-none"></div>
    <div id="loading" class="text-center text-muted py-5">جاري التحميل...</div>
    <div id="formWrap" class="d-none">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="kit-manage-card">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-building"></i> المراكز المكلَّفة</h2>
            <div class="kit-chips" id="centersBox"></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="kit-manage-card">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-person-workspace"></i> المدربون المكلَّفون</h2>
            <div class="kit-chips" id="trainersBox"></div>
          </div>
        </div>
      </div>
      <div class="text-center mt-2 mb-4">
        <button type="button" class="soft-btn border-0 px-5" id="saveBtn">حفظ التكليف</button>
      </div>
    </div>
  </div>
</section>

<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/training-kit-manage.js?v=1.0"></script>
</body>
</html>
