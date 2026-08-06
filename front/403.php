<?php
$basePath = '';
$pageTitle = 'غير مصرح';
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800&display=swap" rel="stylesheet">
  <style>
    body{
      font-family:'Tajawal',sans-serif;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:linear-gradient(135deg,#f7fcfa 0%,#eef8f4 100%);
      color:#062824;
      padding:24px;
    }
    .forbidden-card{
      max-width:520px;
      width:100%;
      background:#fff;
      border:1px solid rgba(23,148,123,.12);
      border-radius:24px;
      padding:36px 28px;
      text-align:center;
      box-shadow:0 20px 50px rgba(15,79,71,.08);
    }
    .forbidden-code{
      font-size:4rem;
      font-weight:800;
      color:#17947B;
      line-height:1;
      margin-bottom:12px;
    }
    .forbidden-title{
      font-size:1.5rem;
      font-weight:800;
      margin-bottom:12px;
    }
    .forbidden-text{
      color:#6b7280;
      line-height:1.9;
      margin-bottom:24px;
    }
    .btn-brand{
      background:#17947B;
      color:#fff;
      border:none;
      border-radius:14px;
      padding:12px 18px;
      font-weight:700;
    }
    .btn-brand:hover{ background:#0f7d68; color:#fff; }
    .btn-outline-brand{
      background:#fff;
      color:#17947B;
      border:1px solid rgba(23,148,123,.28);
      border-radius:14px;
      padding:12px 18px;
      font-weight:700;
      text-decoration:none;
    }
    .actions{ display:grid; gap:12px; }
  </style>
</head>
<body>
  <div class="forbidden-card">
    <div class="forbidden-code">403</div>
    <h1 class="forbidden-title">غير مصرح لك بالوصول إلى هذه الصفحة</h1>
    <p class="forbidden-text">
      لا تملك الصلاحية اللازمة لعرض هذا المحتوى. إذا كنت تعتقد أن هذا خطأ،
      تواصل مع مسؤول النظام.
    </p>
    <div class="actions">
      <a href="<?php echo $basePath; ?>dashboard.php" class="btn btn-brand w-100">العودة للوحة التحكم</a>
      <button type="button" id="logoutBtn" class="btn btn-outline-brand w-100">تسجيل الخروج</button>
    </div>
  </div>

  <script src="<?php echo $basePath; ?>assets/js/core/config.js?v=2.0"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/routes.js?v=2.0"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/api.js?v=2.1"></script>
  <script src="<?php echo $basePath; ?>assets/js/core/auth.js?v=2.1"></script>
  <script>
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      window.AppAuth.logout();
    });
  </script>
</body>
</html>
