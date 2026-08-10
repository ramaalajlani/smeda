<?php
$basePath = '../../';
$pageTitle = 'التوقيع الإلكتروني';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>
  <style>
    .signature-card{background:#fff;border:1px solid #e9eef5;border-radius:18px;padding:24px;box-shadow:0 10px 30px rgba(15,23,42,.04);margin-bottom:24px}
    .signature-preview-box{min-height:120px;border:1px dashed #cbd5e1;border-radius:14px;display:flex;align-items:center;justify-content:center;background:#f8fafc;padding:16px}
    .signature-preview-box img{max-width:100%;max-height:100px;object-fit:contain}
    .signature-message{display:none;margin-bottom:16px;padding:12px 14px;border-radius:12px;font-weight:700}
    .signature-message.success{display:block;background:rgba(25,135,84,.08);color:#198754}
    .signature-message.error{display:block;background:rgba(220,53,69,.08);color:#dc3545}
  </style>
</head>
<body>
<?php include '../../includes/layout/app-shell-open.php'; ?>
<section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <span>التوقيع الإلكتروني</span>
      </div>
      <h1 class="fw-bold mb-3">التوقيع الإلكتروني</h1>
      <p class="section-subtitle">سيتم استخدام هذا التوقيع تلقائيًا عند اعتماد الشهادات والوثائق الخاصة بحسابك.</p>
    </div>
  </section>
  <section class="section pt-0">
    <div class="container">
      <div id="signatureMessage" class="signature-message"></div>
      <div class="signature-card">
        <h3 class="mb-3">التوقيع الحالي</h3>
        <div id="signaturePreview" class="signature-preview-box mb-3"><span class="text-muted">لا يوجد توقيع مفعّل</span></div>
        <form id="signatureUploadForm">
          <div class="mb-3">
            <label class="form-label">رفع توقيع جديد (PNG/JPG/WEBP — حتى 2MB)</label>
            <input type="file" id="signatureFile" class="form-control" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
          </div>
          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-brand" id="uploadSignatureBtn">رفع التوقيع</button>
            <button type="button" class="btn btn-outline-danger" id="deactivateSignatureBtn">تعطيل التوقيع</button>
          </div>
        </form>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/my-electronic-signature.js?v=1.0"></script>
</body>
</html>
