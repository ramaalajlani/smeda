<?php
$basePath = isset($basePath) ? $basePath : '';
?>

<style>





#incubators-window .incubator-card-link {
  display: block;
  text-decoration: none;
  color: inherit;
  height: 100%;
}

#incubators-window .card {
  height: 100%;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 16px;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  background: #fff;
  overflow: hidden;
}

#incubators-window .incubator-card-link:hover .card,
#incubators-window .incubator-card-link:focus .card {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border-color: rgba(0,0,0,0.12);
}

#incubators-window .card-img-wrap {
  width: 100%;
  height: 160px;
  overflow: hidden;
  border-radius: 12px;
  margin-bottom: 15px;
}

#incubators-window .card-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.35s ease;
}

#incubators-window .incubator-card-link:hover .card-img-wrap img,
#incubators-window .incubator-card-link:focus .card-img-wrap img {
  transform: scale(1.05);
}

#incubators-window .card h5 {
  margin-bottom: 10px;
  font-weight: 700;
  font-size: 1.08rem;
}

#incubators-window .card p {
  margin-bottom: 0;
  color: #6c757d;
  line-height: 1.8;
}

@media (max-width: 767.98px) {
  #incubators-window {
    padding: 45px 0;
  }

  #incubators-window .card-img-wrap {
    height: 145px;
  }
}
</style>

<section class="section" id="incubators-window">
  <div class="container">

    <div class="text-center mb-5">
      <h2 class="section-title">حاضنات الأعمال</h2>
      <p >دعم المشاريع الناشئة من الفكرة حتى الانطلاق</p>
    </div>

    <div class="row g-4">

      <div class="col-md-6 col-lg-4">
        <a class="incubator-card-link" href="<?php echo $basePath; ?>services/incubation/incubation-apply.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/j1.jpg" alt="الاحتضان">
            </div>
            <h5>الاحتضان</h5>
            <p>دعم المشاريع في مراحلها الأولى</p>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a class="incubator-card-link" href="<?php echo $basePath; ?>services/training/training-programs-list.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/j2.jpg" alt="التدريب">
            </div>
            <h5>التدريب</h5>
            <p>برامج تدريبية متخصصة</p>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a class="incubator-card-link" href="<?php echo $basePath; ?>services/consulting/consulting-offices-list.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/j3.jpg" alt="الاستشارات">
            </div>
            <h5>الاستشارات</h5>
            <p>إرشاد وخطط تطوير المشاريع</p>
          </div>
        </a>
      </div>

    </div>

  </div>
</section>