<?php $basePath = isset($basePath) ? $basePath : ''; ?>

<style>
#success-stories-window {
  padding: 60px 0;
  position: relative;
  z-index: 1;
}

#success-stories-window .section-title {
  font-weight: 800;
  margin-bottom: 10px;
}

#success-stories-window .section-subtitle {
  color: #6c757d;
  margin-bottom: 0;
}

#success-stories-window .story-card-link {
  display: block;
  text-decoration: none;
  color: inherit;
  height: 100%;
}

#success-stories-window .card {
  height: 100%;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 16px;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  background: #fff;
  overflow: hidden;
}

#success-stories-window .story-card-link:hover .card,
#success-stories-window .story-card-link:focus .card {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border-color: rgba(0,0,0,0.12);
}

#success-stories-window .card-img-wrap {
  width: 100%;
  height: 160px;
  overflow: hidden;
  border-radius: 12px;
  margin-bottom: 15px;
}

#success-stories-window .card-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.35s ease;
}

#success-stories-window .story-card-link:hover .card-img-wrap img,
#success-stories-window .story-card-link:focus .card-img-wrap img {
  transform: scale(1.05);
}

#success-stories-window .card h5 {
  margin-bottom: 10px;
  font-weight: 700;
  font-size: 1.08rem;
}

#success-stories-window .card p {
  margin-bottom: 0;
  color: #6c757d;
  line-height: 1.8;
}

@media (max-width: 767.98px) {
  #success-stories-window {
    padding: 45px 0;
  }

  #success-stories-window .card-img-wrap {
    height: 145px;
  }
}
</style>

<section class="section" id="success-stories-window">
  <div class="container">

    <div class="text-center mb-5">
      <h2 class="section-title">قصص نجاح ريادية</h2>
      <p class="section-subtitle">نماذج ملهمة لمشاريع تحولت من أفكار إلى نجاحات واقعية</p>
    </div>

    <div class="row g-4">

      <div class="col-md-6 col-lg-4">
        <a class="story-card-link" href="<?php echo $basePath; ?>services/incubation/success-stories.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/success-1.jpg" alt="مشروع منتجات طبيعية">
            </div>
            <h5>مشروع منتجات طبيعية</h5>
            <p>بدأ كفكرة صغيرة وتحوّل إلى مشروع مرخص يقدّم منتجاته في السوق المحلي بثقة واستقرار.</p>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a class="story-card-link" href="<?php echo $basePath; ?>services/incubation/success-stories.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/success-2.jpg" alt="منصة تدريب إلكتروني">
            </div>
            <h5>منصة تدريب إلكتروني</h5>
            <p>فكرة ريادية تطورت إلى خدمة رقمية فعالة تخدم المتدربين ورواد الأعمال ضمن بيئة احترافية.</p>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a class="story-card-link" href="<?php echo $basePath; ?>services/incubation/success-stories.php">
          <div class="card p-4">
            <div class="card-img-wrap">
              <img src="<?php echo $basePath; ?>assets/images/success-3.jpg" alt="مشروع حرفي منتج">
            </div>
            <h5>مشروع حرفي منتج</h5>
            <p>انتقل من مرحلة الفكرة والتجربة إلى مشروع قائم يحقق مبيعات ويشارك في السوق بمنتجات مكودة.</p>
          </div>
        </a>
      </div>

    </div>

  </div>
</section>