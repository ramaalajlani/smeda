<?php

$basePath = '../../';

$pageTitle = 'تفاصيل الاتفاقية';

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

        <h1 class="fw-bold mb-2" id="agreementTitle">تفاصيل الاتفاقية</h1>

        <p class="section-subtitle mb-0"><a href="agreements.php">← العودة للاتفاقيات</a></p>

      </div>

      <div class="d-flex gap-2" id="agreementActions"></div>

    </div>

  </section>

  <section class="section pt-0">

    <div class="container">

      <div id="agreementViewMessage" class="alert d-none"></div>

      <div id="agreementViewLoading" class="courses-loading-box">جاري التحميل...</div>

      <div id="agreementViewContent" class="d-none data-table-wrap p-4">

        <div class="row g-3" id="agreementDetailsGrid"></div>

      </div>

    </div>

  </section>

</main>

<?php include '../../includes/layout/footer.php'; ?>

<?php include '../../includes/layout/scripts.php'; ?>

<script src="<?= $basePath ?>assets/js/pages/agreement-view.js?v=1.0"></script>

</body>

</html>

