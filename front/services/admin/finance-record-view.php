<?php

$basePath = '../../';

$pageTitle = 'تفاصيل السجل المالي';

$activePage = 'services';

?>

<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include '../../includes/layout/head.php'; ?></head>

<body>

<?php include '../../includes/layout/header.php'; ?>

<main>

  <section class="services-hero">

    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">

      <div>

        <h1 class="fw-bold mb-2" id="financeRecordTitle">تفاصيل السجل المالي</h1>

        <p class="section-subtitle mb-0"><a href="finance.php">← العودة للملفات المالية</a></p>

      </div>

      <div class="d-flex gap-2" id="financeRecordActions"></div>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="financeRecordViewMessage" class="alert d-none"></div>

      <div id="financeRecordViewLoading" class="courses-loading-box">جاري التحميل...</div>

      <div id="financeRecordViewContent" class="d-none data-table-wrap p-4">

        <div class="row g-3" id="financeRecordDetailsGrid"></div>

      </div>

    </div>

  </section>

</main>

<?php include '../../includes/layout/footer.php'; ?>

<?php include '../../includes/layout/scripts.php'; ?>

<script src="<?= $basePath ?>assets/js/pages/finance-record-view.js?v=1.0"></script>

</body>

</html>

