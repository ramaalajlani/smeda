<?php
$basePath = '../';
$pageTitle = 'نافذة خدمات المشروعات';
$activePage = 'services';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include '../includes/layout/head.php'; ?>
</head>
<body>

  <?php include '../includes/layout/header.php'; ?>

  <main>
    <section class="services-hero">
      <div class="container">
        <div class="breadcrumb-soft">
          <a href="<?php echo $basePath; ?>index.php">الرئيسية</a>
          <span>/</span>
          <span>خدمات المشروعات</span>
        </div>

        <div class="row g-4 align-items-center">
          <div class="col-lg-7">
            <span class="section-badge">نافذة خدمات المشروعات</span>
            <h1 class="fw-bold mb-3">بوابة رقمية موحدة لتطوير المشروعات واعتمادها</h1>
            <p class="section-subtitle">
              تضم هذه النافذة كافة الخدمات المتعلقة بالتدريب، التراخيص، الاستشارات،
              البرامج، الدراسات، والمستشار الذكي، ضمن نفس الهوية المؤسسية وبأسلوب
              منظم وقابل للتوسع.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <a href="#servicesModules" class="btn btn-brand">استعراض الأقسام</a>
              <a href="<?php echo $basePath; ?>index.php#contact" class="soft-btn">تواصل معنا</a>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="page-intro-card">
              <h3 class="mb-3">ماذا تتضمن هذه النافذة؟</h3>
              <ul class="feature-list mb-0">
                <li>إدارة التدريب والاعتمادات والتحقق من الشهادات</li>
                <li>التسجيل على التراخيص المؤقتة ومتابعة مؤشراتها</li>
                <li>طلب الاستشارات حسب الاختصاص</li>
                <li>استعراض البرامج والتسجيل فيها</li>
                <li>إعداد الدراسات ومراجعتها وعرضها</li>
                <li>مستشار ذكي تفاعلي لمساعدة المستخدم</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section stats-section">
      <div class="container">
        <div class="section-heading text-center">
          <h2>مؤشرات سريعة</h2>
          <p>لمحة عامة عن حجم الخدمات والاعتمادات والطلبات ضمن نافذة خدمات المشروعات.</p>
        </div>

        <div class="row g-4 justify-content-center metrics-row">
          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="1250">0</h2>
              <p>متدرب موثق</p>
            </div>
          </div>

          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="186">0</h2>
              <p>مدرب معتمد</p>
            </div>
          </div>

          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="74">0</h2>
              <p>ترخيص مؤقت</p>
            </div>
          </div>

          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="482">0</h2>
              <p>طلب استشارة</p>
            </div>
          </div>

          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="53">0</h2>
              <p>برنامج معتمد</p>
            </div>
          </div>

          <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
              <h2 class="counter" data-target="97">0</h2>
              <p>دراسة منشورة</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="servicesModules">
      <div class="container">
        <div class="section-heading text-center">
          <h2>أقسام نافذة خدمات المشروعات</h2>
          <p>اختر القسم المطلوب للانتقال إلى صفحاته التفصيلية بنفس التصميم والهوية العامة للموقع.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-mortarboard"></i></div>
              <h3>قسم التدريب</h3>
              <p>
                يضم المتدربين الموثقين، المدربين المعتمدين، الحقائب التدريبية،
                البرامج التدريبية، المراكز التدريبية، ومركز التوثيق والتحقق.
              </p>
              <div class="meta">
                <span>اعتماد</span>
                <span>توثيق</span>
                <span>QR</span>
              </div>
              <div class="actions">
                <a href="<?php echo $basePath; ?>services/training/training-trainees-list.php" class="btn btn-brand">فتح القسم</a>
                <a href="<?php echo $basePath; ?>services/training/training-verification.php" class="soft-btn">التحقق من الشهادات</a>
                <a href="<?php echo $basePath; ?>services/training/signature-verification.php" class="soft-btn">التحقق من التوقيع ESIG</a>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-patch-check"></i></div>
              <h3>قسم التراخيص</h3>
              <p>
                بدء طلب ترخيص مؤقت، الاطلاع على المعايير المعتمدة، متابعة
                التراخيص الممنوحة، واستعراض مؤشرات التراخيص.
              </p>
              <div class="meta">
                <span>طلبات</span>
                <span>معايير</span>
                <span>مؤشرات</span>
              </div>
              <div class="actions">
                <a href="<?php echo $basePath; ?>index.php#licensing-window" class="btn btn-brand">فتح القسم</a>
                <span class="soft-btn" style="opacity:.65;cursor:default" title="قريباً">مؤشرات — قريباً</span>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-briefcase"></i></div>
              <h3>قسم الاستشارات</h3>
              <p>
                استشارات مصنفة حسب التخصصات، مع إمكانية تقديم طلب استشارة
                ومتابعة المؤشرات الخاصة بالخدمة والمستفيدين منها.
              </p>
              <div class="meta">
                <span>تخصصات</span>
                <span>طلبات</span>
                <span>متابعة</span>
              </div>
              <div class="actions">
                <a href="<?php echo $basePath; ?>services/consulting/consulting-offices-list.php" class="btn btn-brand">فتح القسم</a>
                <a href="<?php echo $basePath; ?>services/consulting/consulting-request-create.php" class="soft-btn">طلب استشارة</a>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-diagram-3"></i></div>
              <h3>قسم البرامج</h3>
              <p>
                استعراض البرامج المعتمدة بالتعاون مع الجهات الشريكة، التسجيل
                فيها عند الإتاحة، ومتابعة مؤشرات كل برنامج على حدة.
              </p>
              <div class="meta">
                <span>برامج</span>
                <span>شركاء</span>
                <span>تسجيل</span>
              </div>
              <div class="actions">
                <a href="<?php echo $basePath; ?>services/training/program-bank-list.php" class="btn btn-brand">فتح القسم</a>
                <a href="<?php echo $basePath; ?>services/training/program-bank-dashboard.php" class="soft-btn">مؤشرات البرامج</a>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-journal-richtext"></i></div>
              <h3>قسم الدراسات</h3>
              <p>
                يشمل إعداد الدراسات، مراجعتها، عرضها، ومتابعة المؤشرات التابعة
                لها ضمن إطار منظم وقابل للتطوير.
              </p>
              <div class="meta">
                <span>إعداد</span>
                <span>مراجعة</span>
                <span>عرض</span>
              </div>
              <div class="actions">
                <span class="btn btn-brand" style="opacity:.65;cursor:default" title="قريباً">قريباً</span>
                <span class="soft-btn" style="opacity:.65;cursor:default" title="قريباً">مؤشرات — قريباً</span>
              </div>
            </article>
          </div>

          <div class="col-md-6 col-xl-4">
            <article class="services-grid-card">
              <div class="service-icon"><i class="bi bi-robot"></i></div>
              <h3>المستشار الذكي</h3>
              <p>
                عميل ذكاء اصطناعي يساعد المستخدم في فهم الخدمات، التدريب
                والتوجيه، وتقديم توصيات أولية بناءً على خبرات الهيئة.
              </p>
              <div class="meta">
                <span>AI</span>
                <span>إرشاد</span>
                <span>تدريب</span>
              </div>
              <div class="actions">
                <span class="btn btn-brand" style="opacity:.65;cursor:default" title="قريباً">قريباً</span>
                <a href="<?php echo $basePath; ?>login.php" class="soft-btn">تسجيل الدخول</a>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section light-alt-section">
      <div class="container">
        <div class="section-heading text-center">
          <h2>روابط سريعة لأهم الصفحات</h2>
          <p>اختصارات مباشرة إلى أكثر الصفحات استخداماً ضمن هذه النافذة.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-xl-3">
            <div class="mini-card h-100 text-center">
              <h3>المتدربون الموثقون</h3>
              <p>الوصول إلى قائمة الشهادات والاعتمادات التدريبية.</p>
              <a href="<?php echo $basePath; ?>services/training/training-trainees-list.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="mini-card h-100 text-center">
              <h3>طلب ترخيص مؤقت</h3>
              <p>بدء تعبئة نموذج الترخيص المؤقت للمشروع.</p>
              <a href="<?php echo $basePath; ?>index.php#licensing-window" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="mini-card h-100 text-center">
              <h3>طلب استشارة</h3>
              <p>تقديم طلب استشارة حسب الاختصاص المطلوب.</p>
              <a href="<?php echo $basePath; ?>services/consulting/consulting-request-create.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="mini-card h-100 text-center">
              <h3>التحقق من شهادة</h3>
              <p>التحقق من صحة الشهادة أو الوثيقة عبر QR أو رقم مرجعي.</p>
              <a href="<?php echo $basePath; ?>services/training/training-verification.php" class="soft-btn mt-3">الدخول</a>
              <a href="<?php echo $basePath; ?>services/training/signature-verification.php" class="soft-btn mt-2 d-inline-block">تحقق ESIG</a>
            </div>
          </div>
        </div>

        <!-- أدوات المدرب والإدارة -->
        <div id="trainerToolsBlock" class="row g-4 d-none mt-2">
          <div class="col-md-6 col-xl-3" id="myProfileCardWrap">
            <div class="mini-card h-100 text-center">
              <h3>ملفي المهني</h3>
              <p>إدارة الملف المهني الخاص بالمدرب وتحديث المهارات والخبرات.</p>
              <a href="<?php echo $basePath; ?>services/training/my-trainer-profile.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3" id="nominateKitCardWrap">
            <div class="mini-card h-100 text-center">
              <h3>رشح حقيبة</h3>
              <p>إرسال ترشيح حقيبة تدريبية جديدة أو اقتراح تطوير حقيبة قائمة.</p>
              <a href="<?php echo $basePath; ?>services/training/training-kit-nomination.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3" id="myNominationsCardWrap">
            <div class="mini-card h-100 text-center">
              <h3>ترشيحاتي</h3>
              <p>عرض جميع ترشيحات الحقائب التدريبية التي قمت بإرسالها ومتابعة حالتها.</p>
              <a href="<?php echo $basePath; ?>services/training/my-training-kit-nominations.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>

          <div class="col-md-6 col-xl-3 d-none" id="reviewNominationsCardWrap">
            <div class="mini-card h-100 text-center">
              <h3>مراجعة الترشيحات</h3>
              <p>مراجعة ترشيحات الحقائب التدريبية واتخاذ قرار القبول أو الرفض أو الإبقاء للمراجعة.</p>
              <a href="<?php echo $basePath; ?>services/training/training-kit-nominations-review.php" class="soft-btn mt-3">الدخول</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include '../includes/layout/footer.php'; ?>
  <?php include '../includes/layout/scripts.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
      if (!ok) return;

      const trainerToolsBlock = document.getElementById('trainerToolsBlock');
      const myProfileCardWrap = document.getElementById('myProfileCardWrap');
      const nominateKitCardWrap = document.getElementById('nominateKitCardWrap');
      const myNominationsCardWrap = document.getElementById('myNominationsCardWrap');
      const reviewNominationsCardWrap = document.getElementById('reviewNominationsCardWrap');

      const canViewTrainerProfile =
        window.AppAuth.hasPermission(window.AppPermissions.VIEW_TRAINER_PROFILES) ||
        window.AppAuth.hasPermission(window.AppPermissions.EDIT_OWN_TRAINER_PROFILE);

      const canNominateKit =
        window.AppAuth.hasPermission(window.AppPermissions.NOMINATE_TRAINING_KITS);

      const canReviewNominations =
        window.AppAuth.hasPermission(window.AppPermissions.REVIEW_TRAINING_KIT_NOMINATIONS);

      const shouldShowTrainerTools =
        canViewTrainerProfile || canNominateKit || canReviewNominations;

      if (!shouldShowTrainerTools) return;

      trainerToolsBlock?.classList.remove('d-none');

      if (!canViewTrainerProfile) {
        myProfileCardWrap?.classList.add('d-none');
      }

      if (!canNominateKit) {
        nominateKitCardWrap?.classList.add('d-none');
        myNominationsCardWrap?.classList.add('d-none');
      }

      if (canReviewNominations) {
        reviewNominationsCardWrap?.classList.remove('d-none');
      }
    });
  </script>

</body>
</html>