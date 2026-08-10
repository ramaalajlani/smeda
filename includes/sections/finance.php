<?php
$basePath = isset($basePath) ? $basePath : '';
?>

<style>
  #finance-window {
    padding: 60px 0;
  }

  #finance-window .feature-image img {
    max-width: 100%;
    border-radius: 14px;
  }

  /* ===== Clickable List ===== */
  #finance-window .feature-list {
    list-style: none;
    padding: 0;
    margin: 24px 0 0;
    display: grid;
    gap: 12px;
  }

  #finance-window .feature-list li {
    margin: 0;
  }

  #finance-window .feature-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
    background: #fff;
    border: 1px solid rgba(13, 37, 63, 0.08);
    border-radius: 14px;
    padding: 14px 16px;
    transition:
      transform 0.25s ease,
      box-shadow 0.25s ease,
      border-color 0.25s ease,
      color 0.25s ease;
  }

  #finance-window .feature-link::before {
    content: "\F285";
    font-family: "bootstrap-icons";
    font-size: 1rem;
    transition: transform 0.25s ease;
  }

  #finance-window .feature-link:hover {
    transform: translateY(-4px);
    border-color: rgba(13, 110, 253, 0.18);
    box-shadow: 0 10px 24px rgba(13, 37, 63, 0.08);
    color: var(--primary, #0d6efd);
  }

  #finance-window .feature-link:hover::before {
    transform: translateX(-3px);
  }

  #finance-window .feature-link:focus {
    outline: none;
    box-shadow: 0 0 0 0.22rem rgba(13, 110, 253, 0.18);
  }

  @media (prefers-reduced-motion: reduce) {
    #finance-window .feature-link,
    #finance-window .feature-link::before {
      transition: none !important;
      transform: none !important;
    }
  }
</style>

<section class="section feature-split" id="finance-window">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6 order-2 order-lg-1">
        <div class="feature-image">
          <img src="<?php echo $basePath; ?>assets/images/finance.jpg" alt="نافذة التمويل" />
        </div>
      </div>

      <div class="col-lg-6 order-1 order-lg-2">
        <div class="section-copy">
          <span class="section-badge">التمويل</span>
          <h2>ربط رقمي متكامل بين أصحاب الطلبات والجهات التمويلية</h2>
          <p>
            تتيح نافذة التمويل لصاحب الطلب تقديم طلب التمويل واستكمال
            بياناته والتحقق منه من قبل الهيئة، ثم طرحه ضمن سحابة رقمية
            مخصصة للبنوك والمؤسسات التمويلية وشركات التأمين والجهات الضامنة.
          </p>

          <ul class="feature-list">

            <li id="finance-register">
              <a class="feature-link" href="<?php echo $basePath; ?>services/finance/finance-apply.php">
                التسجيل على التمويل
              </a>
            </li>

            <li id="finance-cloud">
              <a class="feature-link" href="<?php echo $basePath; ?>services/finance/finance-cloud.php">
                سحابة القروض غير الممولة
              </a>
            </li>

            <li id="finance-metrics">
              <a class="feature-link" href="<?php echo $basePath; ?>services/finance/finance-metrics.php">
                المؤشرات العامة والخاصة بالتمويل
              </a>
            </li>

          </ul>

          <a href="<?php echo $basePath; ?>index.php#contact" class="btn btn-outline-dark rounded-pill px-5 mt-3">
            طلب معلومات
          </a>
        </div>
      </div>

    </div>
  </div>
</section>
