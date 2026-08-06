<?php
$basePath = '../../';
$activePage = 'needs-dashboard';
$pageTitle = 'لوحة الاحتياجات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <?php include $basePath . 'includes/layout/app-shell-styles.php'; ?>
  <style>
    /* ══ صفوف التوزيع القابلة للنقر ══ */
    .nd-row{display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:10px;text-decoration:none;color:#334155;transition:background .15s;border-bottom:1px solid #f1f5f9}
    .nd-row:last-child{border-bottom:0}
    .nd-row:hover{background:#f0fdf9}
    .nd-row:hover .nd-chevron{opacity:1;transform:translateX(-3px)}
    .nd-rank{flex:0 0 auto;width:22px;height:22px;border-radius:7px;background:#eef2f5;color:#64748b;font-size:.7rem;font-weight:800;display:flex;align-items:center;justify-content:center}
    .nd-label{flex:0 0 auto;min-width:92px;font-weight:700;font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .nd-bar{flex:1 1 auto;height:8px;background:#eef2f5;border-radius:20px;overflow:hidden;min-width:36px}
    .nd-bar-fill{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,#17947B,#06AA89)}
    .nd-count{flex:0 0 auto;min-width:34px;text-align:center;font-weight:900;color:#0f172a}
    .nd-chevron{flex:0 0 auto;color:#94a3b8;opacity:.45;transition:.15s;font-size:.8rem}
    .nd-empty{color:#9ca3af;padding:12px 4px;font-size:.86rem}
    .card .card-body h5{font-weight:800;font-size:.98rem;color:#0f172a}
  </style>
</head>
<body>
<?php include $basePath . 'includes/layout/app-shell-open.php'; ?>
<section class="services-hero">
    <div class="container">
      <h1 class="fw-bold mb-2">لوحة مؤشرات الاحتياجات</h1>
      <p class="section-subtitle mb-0">إحصائيات موحّدة لاحتياجات المواطن والدولة حسب نطاق صلاحياتك.</p>
    </div>
  </section>
  <section class="section pt-0">
    <div class="container">
      <div id="needsDashboardLoading" class="alert alert-light border">جاري تحميل المؤشرات...</div>
      <div class="row g-3" id="needsDashboardKpis"></div>
      <div class="row g-3 mt-2">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب المحافظة</h5>
              <div id="needsByGovernorate"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب القطاع</h5>
              <div id="needsBySector"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب نوع المنشأة</h5>
              <div id="needsByFacilityType"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب نوع الاستهداف</h5>
              <div id="needsByTargetingType"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب المنطقة</h5>
              <div id="needsByDistrict"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">حسب القطاع المرجعي</h5>
              <div id="needsBySectorRef"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php include $basePath . 'includes/layout/app-shell-close.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-dashboard.js?v=2.1"></script>
</body>
</html>
