<?php
$basePath = isset($basePath) ? $basePath : '';
?>

<style>
  /* ─── LANDING JOURNEY — يستخدم توكنات index.php ─── */
  .lp-journey { padding: 90px 0; position: relative; overflow: hidden; }
  .lp-journey--soft  { background: linear-gradient(180deg, #f4faf8 0%, #fff 100%); }
  .lp-journey--white { background: #fff; }
  .lp-journey--dark  {
    background:
      radial-gradient(circle at top right, rgba(6,170,137,.14), transparent 28%),
      linear-gradient(140deg, var(--c-dark), var(--c-deep));
    color: #fff;
  }
  .lp-journey--dark .sec-title { color: #fff; }

  .lp-split-img {
    border-radius: 24px; overflow: hidden;
    box-shadow: var(--shadow-lg);
    aspect-ratio: 4/3; position: relative;
    background: linear-gradient(135deg, var(--c-deep), var(--c-primary));
  }
  .lp-split-img img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .6s cubic-bezier(.22,.68,0,1.1);
  }
  .lp-split-img:hover img { transform: scale(1.04); }
  .lp-split-img-badge {
    position: absolute; bottom: 16px; right: 16px; z-index: 2;
    background: rgba(255,255,255,.92); color: var(--c-dark);
    font-size: .78rem; font-weight: 800;
    padding: 6px 14px; border-radius: 999px;
    display: flex; align-items: center; gap: 6px;
  }

  .lp-explainer {
    font-size: 1.05rem; line-height: 2; color: var(--c-body); margin-bottom: 24px;
  }
  .lp-journey--dark .lp-explainer { color: rgba(255,255,255,.88); }

  .lp-benefit-list {
    list-style: none; padding: 0; margin: 0 0 28px;
    display: flex; flex-direction: column; gap: 10px;
  }
  .lp-benefit-list li {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: .97rem; font-weight: 700; color: var(--c-text); line-height: 1.7;
  }
  .lp-journey--dark .lp-benefit-list li { color: rgba(255,255,255,.9); }
  .lp-benefit-list li i {
    color: var(--c-primary); font-size: 1rem; flex-shrink: 0; margin-top: 3px;
  }
  .lp-journey--dark .lp-benefit-list li i { color: var(--c-accent); }

  /* ─── how-steps — نفس entrepreneurship-hub.php بألوان الهيئة ─── */
  .lp-journey .how-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0; position: relative;
  }
  .lp-journey .how-step {
    padding: 32px 24px; text-align: center; position: relative;
  }
  .lp-journey .how-step:not(:last-child)::after {
    content: ''; position: absolute;
    top: 52px; left: 0; width: 100%; height: 2px;
    background: linear-gradient(90deg, transparent, rgba(23,148,123,.20), transparent);
  }
  .lp-journey .how-num {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
    color: #fff; font-size: 1.2rem; font-weight: 900;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 6px 18px rgba(23,148,123,.30);
    position: relative; z-index: 1;
  }
  .lp-journey .how-icon {
    font-size: 1.6rem; color: var(--c-primary);
    margin-bottom: 12px; display: block;
  }
  .lp-journey .how-step h3 {
    font-size: .97rem; font-weight: 800; color: var(--c-dark); margin-bottom: 6px;
  }
  .lp-journey .how-step p {
    font-size: .82rem; color: var(--c-muted); line-height: 1.6; margin: 0;
  }

  .lp-how-shell {
    margin-top: 48px;
    background: #fff;
    border: 1px solid rgba(23,148,123,.10);
    border-radius: 22px;
    padding: 32px 24px;
    box-shadow: 0 18px 45px rgba(6,40,36,.07);
  }
  .lp-how-head { text-align: center; margin-bottom: 32px; }
  .lp-how-head h3 {
    font-size: 1.2rem; font-weight: 900; color: var(--c-dark); margin-bottom: 8px;
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  .lp-how-head p { font-size: .92rem; color: var(--c-muted); margin: 0; line-height: 1.7; }

  /* ─── roadmap — نفس finance-apply.php ─── */
  #finance-window .roadmap-card {
    margin-top: 48px;
    border: 1px solid rgba(23,148,123,.10);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 18px 45px rgba(6,40,36,.07);
    padding: 1.35rem 1.5rem;
  }
  #finance-window .roadmap-title {
    font-size: 1.08rem; font-weight: 800; color: #062824; margin-bottom: .95rem;
  }
  #finance-window .roadmap-note {
    color: #64748b; font-size: .88rem; line-height: 1.85; margin-bottom: 1rem;
  }
  #finance-window .roadmap-list {
    list-style: none; margin: 0; padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
  }
  #finance-window .roadmap-item {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 12px;
    border: 1px solid rgba(23,148,123,.10);
    border-radius: 16px;
    background: #fbfefd;
  }
  #finance-window .roadmap-badge {
    min-width: 36px; width: 36px; height: 36px;
    border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .92rem; font-weight: 800;
    color: #0F4F47; background: #eaf8f4;
    border: 1px solid rgba(23,148,123,.18);
    flex-shrink: 0;
  }
  #finance-window .roadmap-text strong {
    display: block; color: #062824;
    font-size: .96rem; font-weight: 800; margin-bottom: 2px;
  }
  #finance-window .roadmap-text span {
    display: block; color: #64748b;
    font-size: .845rem; line-height: 1.7;
  }

  /* ─── بطاقات فرعية ─── */
  .lp-mini-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px; margin-top: 32px;
  }
  .lp-mini-card {
    background: #fff; border: 1px solid rgba(23,148,123,.10);
    border-radius: 18px; overflow: hidden;
    text-decoration: none; color: var(--c-text);
    transition: transform .28s, box-shadow .28s;
    box-shadow: 0 2px 12px rgba(6,40,36,.05); display: block;
  }
  .lp-mini-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); color: var(--c-text); }
  .lp-mini-card-img { height: 130px; overflow: hidden; }
  .lp-mini-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
  .lp-mini-card:hover .lp-mini-card-img img { transform: scale(1.06); }
  .lp-mini-card-body { padding: 16px 18px 18px; }
  .lp-mini-card-body h5 { font-size: .97rem; font-weight: 900; margin-bottom: 6px; }
  .lp-mini-card-body p  { font-size: .85rem; color: var(--c-body); line-height: 1.7; margin: 0; }

  .lp-cta-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
  .lp-cta-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 26px; border-radius: 12px;
    background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
    color: #fff; font-weight: 800; font-size: .95rem;
    text-decoration: none; transition: transform .22s, opacity .22s;
    box-shadow: 0 8px 24px rgba(23,148,123,.28);
  }
  .lp-cta-primary:hover { transform: translateY(-2px); opacity: .9; color: #fff; }
  .lp-cta-outline {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 24px; border-radius: 12px;
    background: transparent; color: var(--c-primary);
    font-weight: 700; font-size: .93rem;
    text-decoration: none; border: 1.5px solid rgba(23,148,123,.30);
    transition: background .22s, transform .22s;
  }
  .lp-cta-outline:hover { background: rgba(23,148,123,.08); transform: translateY(-2px); color: var(--c-primary); }
  .lp-journey--dark .lp-cta-outline { color: #fff; border-color: rgba(255,255,255,.35); }
  .lp-journey--dark .lp-cta-outline:hover { background: rgba(255,255,255,.10); color: #fff; }

  .lp-finance-links {
    list-style: none; padding: 0; margin: 20px 0 0;
    display: flex; flex-direction: column; gap: 10px;
  }
  .lp-finance-links a {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px; border-radius: 14px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    color: #fff; text-decoration: none; font-weight: 700; font-size: .93rem;
    transition: background .22s, transform .22s;
  }
  .lp-finance-links a:hover { background: rgba(255,255,255,.14); transform: translateX(-4px); color: #fff; }
  .lp-finance-links a i { color: var(--c-accent); font-size: 1.1rem; }

  @media (max-width: 767.98px) {
    .lp-journey { padding: 60px 0; }
    .lp-how-shell, #finance-window .roadmap-card { padding: 24px 18px; }
    .lp-journey .how-step::after { display: none; }
  }
  @media (max-width: 640px) {
    .lp-journey .how-step::after { display: none; }
  }
</style>

<!-- ══════════════════════════════════════
     ريادة الأعمال
══════════════════════════════════════ -->
<section class="lp-journey lp-journey--soft" id="entrepreneurs-window">
  <div class="container">

    <div class="row align-items-center g-5 mb-2">
      <div class="col-lg-6" data-reveal>
        <span class="sec-badge"><i class="bi bi-rocket-takeoff-fill"></i> رواد الأعمال</span>
        <h2 class="sec-title mt-2">من الفكرة إلى مشروع ريادي مرخّص</h2>
        <p class="lp-explainer">
          بوابة ريادة الأعمال هي نقطة انطلاقك الرقمية. تبدأ بتسجيل فكرتك أو مشروعك التقني
          عبر استبيان شامل، ثم تنتقل لمراحل الاحتضان والإرشاد حتى تحصل على الموافقات
          وتصبح مشروعاً ريادياً فعلياً على أرض الواقع.
        </p>
        <ul class="lp-benefit-list">
          <li><i class="bi bi-check-circle-fill"></i> استبيان تقني من 14 محوراً لتقييم جاهزية مشروعك</li>
          <li><i class="bi bi-check-circle-fill"></i> مساحة لمناقشة الأفكار وتطويرها مع مجتمع روّاد الأعمال</li>
          <li><i class="bi bi-check-circle-fill"></i> متابعة المشاريع المحتضنة والمرخّصة عبر لوحة حسابك</li>
          <li><i class="bi bi-check-circle-fill"></i> الوصول لقصص نجاح حقيقية ونماذج ملهمة</li>
        </ul>
        <div class="lp-cta-row">
          <a href="<?= $basePath ?>services/incubation/entrepreneurship-hub.php" class="lp-cta-primary">
            <i class="bi bi-door-open-fill"></i> ادخل بوابة ريادة الأعمال
          </a>
          <a href="<?= $basePath ?>services/incubation/entrepreneur-profile.php" class="lp-cta-outline">
            <i class="bi bi-clipboard2-check-fill"></i> ابدأ الاستبيان
          </a>
        </div>
      </div>
      <div class="col-lg-6" data-reveal data-reveal-delay="2">
        <div class="lp-split-img">
          <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=900&q=85&fit=crop"
               alt="رواد أعمال يعملون على مشروعهم" loading="lazy">
          <span class="lp-split-img-badge"><i class="bi bi-lightbulb-fill"></i> بيئة رقمية للابتكار</span>
        </div>
      </div>
    </div>

    <div class="lp-how-shell" data-reveal data-reveal-delay="1">
      <div class="lp-how-head">
        <h3><i class="bi bi-map-fill" style="color:var(--c-primary)"></i> كيف تعمل المنظومة</h3>
        <p>أربع خطوات من الفكرة إلى الاحتضان — كما في بوابة ريادة الأعمال</p>
      </div>
      <div class="how-steps">
        <div class="how-step">
          <div class="how-num">١</div>
          <i class="bi bi-cloud-upload-fill how-icon"></i>
          <h3>ارفع مشروعك</h3>
          <p>أرسل طلب احتضان بسيط يصف مشروعك وفكرتك — الهيئة تتكفل بالباقي</p>
        </div>
        <div class="how-step">
          <div class="how-num">٢</div>
          <i class="bi bi-search how-icon"></i>
          <h3>التقييم والمراجعة</h3>
          <p>فريق متخصص يراجع طلبك ويجري مقابلة تقييمية خلال 5-7 أيام عمل</p>
        </div>
        <div class="how-step">
          <div class="how-num">٣</div>
          <i class="bi bi-building how-icon"></i>
          <h3>الحاضنة المناسبة</h3>
          <p>الهيئة تختار لك الحاضنة والمرشد الأنسب بناءً على قطاعك ومرحلتك</p>
        </div>
        <div class="how-step">
          <div class="how-num">٤</div>
          <i class="bi bi-graph-up-arrow how-icon"></i>
          <h3>ابدأ رحلة النمو</h3>
          <p>تتبع مراحل مشروعك، اجلس مع مرشدك، وانمُ داخل منظومة الدعم الكاملة</p>
        </div>
      </div>
    </div>

    <div class="lp-mini-cards" data-reveal data-reveal-delay="2">
      <a href="<?= $basePath ?>services/incubation/entrepreneur-profile.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop" alt="استبيان المشروع" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>استبيان المشروع التقني</h5>
          <p>14 محوراً لتقييم فكرتك: السوق، التمويل، الفريق، والتقنية</p>
        </div>
      </a>
      <a href="<?= $basePath ?>services/incubation/incubation-apply.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&fit=crop" alt="التقدّم للاحتضان" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>التقدّم للاحتضان</h5>
          <p>قدّم طلبك إلكترونياً وتابع حالة الطلب من حسابك</p>
        </div>
      </a>
      <a href="<?= $basePath ?>services/incubation/success-stories.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=85&fit=crop" alt="قصص النجاح" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>قصص النجاح</h5>
          <p>تعرّف على مشاريع نجحت عبر برامج الهيئة واستلهم من تجاربها</p>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     حاضنات الأعمال
══════════════════════════════════════ -->
<section class="lp-journey lp-journey--white" id="incubators-window">
  <div class="container">

    <div class="row align-items-center g-5 flex-lg-row-reverse mb-2">
      <div class="col-lg-6" data-reveal>
        <span class="sec-badge"><i class="bi bi-buildings-fill"></i> حاضنات الأعمال</span>
        <h2 class="sec-title mt-2">بيئة داعمة تُسرّع نمو مشروعك الناشئ</h2>
        <p class="lp-explainer">
          حاضنات الأعمال التابعة للهيئة توفر مساحات عمل، إرشاداً متخصصاً، وبرامج تدريب
          واستشارات — كل ذلك في مكان واحد. اختر الحاضنة الأنسب لقطاعك وقدّم طلبك
          إلكترونياً دون زيارة مكتبية.
        </p>
        <ul class="lp-benefit-list">
          <li><i class="bi bi-check-circle-fill"></i> استعراض جميع الحاضنات المتاحة مع تفاصيل القطاع والموقع</li>
          <li><i class="bi bi-check-circle-fill"></i> برامج احتضان وتدريب واستشارات متكاملة تحت سقف واحد</li>
          <li><i class="bi bi-check-circle-fill"></i> متابعة طلبات الانضمام وحالة مشروعك من لوحة حسابك</li>
          <li><i class="bi bi-check-circle-fill"></i> إرشاد وتوجيه مستمر من خبراء ومدربين معتمدين</li>
        </ul>
        <div class="lp-cta-row">
          <a href="<?= $basePath ?>services/incubation/incubators.php" class="lp-cta-primary">
            <i class="bi bi-search"></i> استعرض الحاضنات
          </a>
          <a href="<?= $basePath ?>services/incubation/incubation-apply.php" class="lp-cta-outline">
            <i class="bi bi-send-fill"></i> قدّم طلب احتضان
          </a>
        </div>
      </div>
      <div class="col-lg-6" data-reveal data-reveal-delay="2">
        <div class="lp-split-img">
          <img src="https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=900&q=85&fit=crop"
               alt="مساحة عمل في حاضنة أعمال" loading="lazy">
          <span class="lp-split-img-badge"><i class="bi bi-building"></i> حاضنات في كل المحافظات</span>
        </div>
      </div>
    </div>

    <div class="lp-how-shell" data-reveal data-reveal-delay="1">
      <div class="lp-how-head">
        <h3><i class="bi bi-map-fill" style="color:var(--c-primary)"></i> خارطة رحلة الاحتضان</h3>
        <p>5 خطوات من اكتشاف الحاضنة حتى المتابعة بعد القبول</p>
      </div>
      <div class="how-steps">
        <div class="how-step">
          <div class="how-num">١</div>
          <i class="bi bi-compass-fill how-icon"></i>
          <h3>اكتشف الحاضنات</h3>
          <p>تصفّح قائمة الحاضنات وفلتر حسب القطاع والمحافظة</p>
        </div>
        <div class="how-step">
          <div class="how-num">٢</div>
          <i class="bi bi-funnel-fill how-icon"></i>
          <h3>اختر الأنسب</h3>
          <p>قارن البرامج والخدمات وحدّد الحاضنة المناسبة لمشروعك</p>
        </div>
        <div class="how-step">
          <div class="how-num">٣</div>
          <i class="bi bi-file-earmark-text-fill how-icon"></i>
          <h3>قدّم طلبك</h3>
          <p>املأ نموذج التقديم وارفق المستندات المطلوبة</p>
        </div>
        <div class="how-step">
          <div class="how-num">٤</div>
          <i class="bi bi-hourglass-split how-icon"></i>
          <h3>انتظر القبول</h3>
          <p>تُراجع طلبك وتصلك النتيجة عبر حسابك وإشعارات المنصة</p>
        </div>
        <div class="how-step">
          <div class="how-num">٥</div>
          <i class="bi bi-graph-up-arrow how-icon"></i>
          <h3>ابدأ الاحتضان</h3>
          <p>انضم للبرنامج واستفد من الإرشاد والموارد والتدريب</p>
        </div>
      </div>
    </div>

    <div class="lp-mini-cards" data-reveal data-reveal-delay="2">
      <a href="<?= $basePath ?>services/incubation/incubation-apply.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=85&fit=crop" alt="الاحتضان" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>برنامج الاحتضان</h5>
          <p>دعم شامل للمشاريع في مراحلها الأولى حتى تثبت نموذجها</p>
        </div>
      </a>
      <a href="<?= $basePath ?>services/training/training-programs-list.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=85&fit=crop" alt="التدريب" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>برامج التدريب</h5>
          <p>دورات متخصصة في ريادة الأعمال والإدارة والتسويق</p>
        </div>
      </a>
      <a href="<?= $basePath ?>services/consulting/consulting-offices-list.php" class="lp-mini-card">
        <div class="lp-mini-card-img">
          <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=600&q=85&fit=crop" alt="الاستشارات" loading="lazy">
        </div>
        <div class="lp-mini-card-body">
          <h5>الاستشارات والإرشاد</h5>
          <p>مكاتب استشارية معتمدة لمساعدتك في التخطيط والتطوير</p>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     التمويل
══════════════════════════════════════ -->
<section class="lp-journey lp-journey--dark" id="finance-window">
  <div class="container">

    <div class="row align-items-center g-5 mb-2">
      <div class="col-lg-6" data-reveal>
        <span class="sec-badge"><i class="bi bi-bank2"></i> منظومة التمويل</span>
        <h2 class="sec-title mt-2">ربط رقمي بين مشروعك والجهات التمويلية</h2>
        <p class="lp-explainer">
          تتيح نافذة التمويل لصاحب الطلب تقديم طلب التمويل واستكمال
          بياناته والتحقق منه من قبل الهيئة، ثم طرحه ضمن سحابة رقمية
          مخصصة للبنوك والمؤسسات التمويلية وشركات التأمين والجهات الضامنة.
        </p>
        <ul class="lp-benefit-list">
          <li><i class="bi bi-check-circle-fill"></i> تقديم طلب تمويل كامل عبر الإنترنت دون زيارة مكتبية</li>
          <li><i class="bi bi-check-circle-fill"></i> متابعة حالة الطلب خطوة بخطوة عبر خارطة الرحلة</li>
          <li><i class="bi bi-check-circle-fill"></i> الوصول لسحابة القروض غير الممولة من جهات متعددة</li>
          <li><i class="bi bi-check-circle-fill"></i> مؤشرات وإحصاءات شفافة عن التمويل على مستوى الهيئة</li>
        </ul>
        <div class="lp-cta-row">
          <a href="<?= $basePath ?>services/finance/finance-apply.php" class="lp-cta-primary">
            <i class="bi bi-cash-stack"></i> قدّم طلب تمويل
          </a>
          <a href="<?= $basePath ?>services/finance/finance.php" class="lp-cta-outline">
            <i class="bi bi-grid-fill"></i> منظومة التمويل
          </a>
        </div>
        <ul class="lp-finance-links">
          <li>
            <a href="<?= $basePath ?>services/finance/finance-cloud.php">
              <i class="bi bi-cloud-fill"></i> سحابة القروض غير الممولة
            </a>
          </li>
          <li>
            <a href="<?= $basePath ?>services/finance/finance-metrics.php">
              <i class="bi bi-bar-chart-fill"></i> مؤشرات التمويل
            </a>
          </li>
        </ul>
      </div>
      <div class="col-lg-6" data-reveal data-reveal-delay="2">
        <div class="lp-split-img">
          <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=900&q=85&fit=crop"
               alt="التمويل والاستثمار في المشاريع" loading="lazy">
          <span class="lp-split-img-badge"><i class="bi bi-shield-check"></i> تحقق رسمي من الهيئة</span>
        </div>
      </div>
    </div>

    <aside class="roadmap-card" data-reveal data-reveal-delay="1">
      <div class="roadmap-title">خارطة الرحلة</div>
      <p class="roadmap-note">
        كل مرحلة تشرح المطلوب من مقدم الطلب فقط. أما إحالة الملف للمكتب الاستشاري وتحديد عرض السعر فتتم لاحقاً بعد الإرسال والمراجعة.
      </p>
      <ul class="roadmap-list">
        <li class="roadmap-item">
          <div class="roadmap-badge">1</div>
          <div class="roadmap-text">
            <strong>تعريف الطلب</strong>
            <span>نوع التمويل، حالة المشروع، وقطاع النشاط.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">2</div>
          <div class="roadmap-text">
            <strong>بيانات مقدم الطلب</strong>
            <span>الاسم، الصفة القانونية، المهنة، وبيانات التواصل.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">3</div>
          <div class="roadmap-text">
            <strong>بيانات التمويل</strong>
            <span>الغاية، السقف، العملة، الهيكل المقترح للسداد.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">4</div>
          <div class="roadmap-text">
            <strong>المكاتب الاستشارية</strong>
            <span>بعد الإرسال تُحال المعاملة لمكتب استشاري مؤهل — دون اختيار يدوي.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">5</div>
          <div class="roadmap-text">
            <strong>النشاط والفواتير</strong>
            <span>إثبات النشاط وبيانات الشركة والفواتير والمستندات.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">6</div>
          <div class="roadmap-text">
            <strong>البيانات المالية</strong>
            <span>الميزانية وقائمة الدخل بشكل منظم وقابل للمراجعة.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">7</div>
          <div class="roadmap-text">
            <strong>العمالة والتأهيل</strong>
            <span>القوى العاملة، المؤهلات، والتوزيع العددي.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">8</div>
          <div class="roadmap-text">
            <strong>الاحتياجات التدريبية</strong>
            <span>الفجوات التدريبية المطلوبة للإداريين والفنيين والعمال.</span>
          </div>
        </li>
        <li class="roadmap-item">
          <div class="roadmap-badge">9</div>
          <div class="roadmap-text">
            <strong>المراجعة والإرسال</strong>
            <span>فحص اكتمال الملف قبل الإرسال الرسمي.</span>
          </div>
        </li>
      </ul>
      <div class="text-center mt-4">
        <a href="<?= $basePath ?>services/finance/finance-apply.php" class="lp-cta-primary">
          <i class="bi bi-arrow-left"></i> ابدأ طلب التمويل الآن
        </a>
      </div>
    </aside>
  </div>
</section>
