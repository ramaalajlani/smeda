<?php

$basePath = '../../';

$pageTitle = 'سجل النشاط';

$activePage = 'services';

?>

<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>

  <?php include '../../includes/layout/head.php'; ?>
  <?php include '../../includes/layout/app-shell-styles.php'; ?>

</head>

<body>

<?php include '../../includes/layout/app-shell-open.php'; ?>

<section class="services-hero">

    <div class="container">

      <h1 class="fw-bold mb-2">سجل النشاط</h1>

      <p class="section-subtitle mb-0">مراقبة عمليات المنصة: من قام بماذا ومتى ومن أي IP.</p>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="activityLogsMessage" class="alert d-none"></div>

      <div class="filter-bar mb-3">

        <div class="row g-3">

          <div class="col-lg-3"><input type="text" id="logSearch" class="form-control" placeholder="بحث عام"></div>

          <div class="col-lg-2"><input type="text" id="logAction" class="form-control" placeholder="نوع العملية"></div>

          <div class="col-lg-2"><input type="text" id="logModule" class="form-control" placeholder="القسم"></div>

          <div class="col-lg-2"><input type="date" id="logDateFrom" class="form-control"></div>

          <div class="col-lg-2"><input type="date" id="logDateTo" class="form-control"></div>

          <div class="col-lg-1"><button type="button" id="logFilterBtn" class="btn btn-brand w-100">بحث</button></div>

          <div class="col-lg-2"><button type="button" id="logExportBtn" class="btn btn-outline-secondary w-100">تصدير CSV</button></div>

        </div>

      </div>

      <div id="activityLogsLoading" class="courses-loading-box">جاري التحميل...</div>

      <div class="data-table-wrap">

        <div class="table-responsive">

          <table class="table align-middle">

            <thead>

              <tr>

                <th>#</th>

                <th>التاريخ</th>

                <th>المستخدم</th>

                <th>العملية</th>

                <th>القسم</th>

                <th>الوصف</th>

                <th>IP</th>

                <th>تفاصيل</th>

              </tr>

            </thead>

            <tbody id="activityLogsTableBody"></tbody>

          </table>

        </div>

      </div>

    </div>

  </section>

</main>



<div class="modal fade" id="logDetailModal" tabindex="-1">

  <div class="modal-dialog modal-lg modal-dialog-scrollable">

    <div class="modal-content">

      <div class="modal-header"><h5 class="modal-title">تفاصيل العملية</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

      <div class="modal-body"><pre id="logDetailBody" class="bg-light p-3 rounded small mb-0"></pre></div>

    </div>

  </div>

</div>



<?php include '../../includes/layout/app-shell-close.php'; ?>

<script src="<?php echo $basePath; ?>assets/js/pages/admin-activity-logs.js?v=1.1"></script>

</body>

</html>

