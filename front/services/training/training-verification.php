<?php
$basePath = '../../';
$pageTitle = 'التحقق من الشهادات';
$activePage = 'services';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

  <style>
    .verification-message{
      margin-top:16px;
      font-size:.95rem;
      font-weight:700;
    }

    .verification-message.success{
      color:#198754;
    }

    .verification-message.error{
      color:#dc3545;
    }

    .verification-placeholder{
      color:#6c757d;
    }

    .qr-placeholder-box{
      min-height:220px;
      display:flex;
      align-items:center;
      justify-content:center;
      border:1px dashed #d7dee8;
      border-radius:16px;
      background:#f8fafc;
      font-weight:800;
      color:#64748b;
      font-size:1.25rem;
      overflow:hidden;
    }

    .qr-placeholder-box img{
      max-width:100%;
      max-height:220px;
      object-fit:contain;
      display:block;
    }

    .verification-status-box{
      border-radius:18px;
      padding:16px 18px;
      margin-bottom:18px;
      border:1px solid #e5e7eb;
      background:#fff;
    }

    .verification-status-box.valid{
      border-color:rgba(25,135,84,.22);
      background:rgba(25,135,84,.05);
    }

    .verification-status-box.invalid{
      border-color:rgba(220,53,69,.22);
      background:rgba(220,53,69,.05);
    }

    .verification-status-title{
      font-size:1rem;
      font-weight:800;
      margin-bottom:6px;
    }

    .verification-status-text{
      margin:0;
      color:#6b7280;
      line-height:1.8;
    }

    .mini-card h3{
      font-size:1rem;
      font-weight:800;
      margin-bottom:10px;
    }

    .mini-card p,
    .mini-card .value-text{
      margin-bottom:0;
      color:#374151;
      line-height:1.9;
      word-break:break-word;
    }

    .verification-actions{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
    }

    .verification-actions .btn{
      min-width:120px;
    }

    .scan-panel{
      margin-top:14px;
      border:1px solid #e5e7eb;
      border-radius:14px;
      padding:12px;
      background:#f8fafc;
    }

    .scan-video-wrap{
      width:100%;
      border-radius:12px;
      overflow:hidden;
      background:#0f172a;
      min-height:220px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .scan-video-wrap video{
      width:100%;
      max-height:320px;
      object-fit:cover;
      display:block;
    }

    .scan-hint{
      margin-top:8px;
      font-size:.9rem;
      color:#475569;
    }
  </style>
</head>
<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">
    <div class="container">
      <div class="breadcrumb-soft">
        <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
        <span>/</span>
        <a href="<?php echo $basePath; ?>services/index.php">خدمات المشروعات</a>
        <span>/</span>
        <span>التحقق من الشهادات</span>
      </div>

      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="section-badge">قسم التدريب</span>
          <h1 class="fw-bold mb-3">مركز التحقق من الشهادات</h1>
          <p class="section-subtitle">
            التحقق من صحة الشهادات التدريبية من خلال رقم الشهادة أو الرقم المرجعي أو كود التحقق،
            مع عرض بيانات الشهادة وحالتها وروابط الطباعة والتحقق العام، ويظهر رمز QR فقط للشهادات المعتمدة نهائياً.
          </p>
        </div>

        <div class="col-lg-4">
          <div class="page-intro-card">
            <h3 class="mb-3">يدعم التحقق عبر</h3>
            <ul class="feature-list mb-0">
              <li>رقم الشهادة</li>
              <li>الرقم المرجعي</li>
              <li>كود التحقق</li>
              <li>رابط الطباعة</li>
              <li>رمز QR للشهادات المعتمدة</li>
              <li>التحقق عبر Scan QR بالكاميرا</li>
              <li><a href="signature-verification.php">التحقق من التوقيع الإلكتروني ESIG</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="form-panel">
        <form id="certificateVerificationForm">
          <div class="row g-3">
            <div class="col-lg-9">
              <label class="form-label">رقم الشهادة أو الرقم المرجعي أو كود التحقق</label>
              <input
                type="text"
                id="verificationValue"
                class="form-control"
                placeholder="مثال: TC001-TRN001-KIT001-CRS0001-TRA001 أو CERT-000001"
              />
            </div>

            <div class="col-lg-3 d-flex align-items-end">
              <button type="submit" id="verifyBtn" class="btn btn-brand w-100">تحقق الآن</button>
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-lg-3">
              <button type="button" id="scanQrBtn" class="btn btn-outline-dark w-100">Scan QR</button>
            </div>
            <div class="col-lg-3">
              <button type="button" id="uploadQrBtn" class="btn btn-outline-secondary w-100">رفع صورة QR</button>
              <input type="file" id="uploadQrInput" accept="image/*" class="d-none" />
            </div>
            <div class="col-lg-6 d-flex align-items-center">
              <small class="text-muted">يمكنك المسح بالكاميرا أو رفع صورة QR للتحقق مباشرة.</small>
            </div>
          </div>

          <div id="scanPanel" class="scan-panel d-none">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong>وضع المسح بالكاميرا</strong>
              <button type="button" id="stopScanBtn" class="btn btn-sm btn-outline-secondary">إيقاف</button>
            </div>
            <div class="scan-video-wrap">
              <video id="scanVideo" autoplay playsinline muted></video>
            </div>
            <div id="scanHint" class="scan-hint">وجّه الكاميرا نحو QR الخاص بالشهادة.</div>
          </div>

          <div id="verificationMessage" class="verification-message d-none"></div>
        </form>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-lg-8">
          <div class="services-grid-card h-100">
            <h3 class="mb-3">نتيجة التحقق</h3>

            <div id="verificationStatusBox" class="verification-status-box d-none">
              <div id="verificationStatusTitle" class="verification-status-title">—</div>
              <p id="verificationStatusText" class="verification-status-text">—</p>
            </div>

            <div class="row g-3" id="verificationResultGrid">
              <div class="col-12">
                <div class="mini-card h-100 verification-placeholder">
                  <h3>لا توجد نتيجة بعد</h3>
                  <p class="mb-0">أدخل بيانات الشهادة ثم اضغط على "تحقق الآن".</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="services-grid-card h-100 text-center">
            <h3 class="mb-3">رمز QR</h3>
            <div id="qrBox" class="qr-placeholder-box">QR</div>
            <p id="qrNote" class="mt-3 mb-0">يظهر رمز QR فقط بعد الاعتماد النهائي للشهادة.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/training-verification.js?v=1.5"></script>

</body>
</html>