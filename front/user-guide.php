<?php
$basePath = '';
$pageTitle = 'دليل المستخدم — من A إلى Z';
$activePage = 'user-guide';
require_once __DIR__ . '/includes/layout/paths.php';

$IMG = 'https://images.unsplash.com';

$personas = [
  [
    'id' => 'trainee', 'icon' => 'bi-person-badge-fill', 'title' => 'المتدرب',
    'image' => "$IMG/photo-1434030216411-0b793f4b4173?w=800&q=85&fit=crop",
    'summary' => 'تعلّم، احصل على شهادة معتمدة، وتحقّق منها إلكترونياً.',
    'register' => 'register.php', 'registerNote' => 'اختر «متدرب» في الخطوة الأولى من التسجيل',
    'steps' => [
      ['title' => 'أنشئ حسابك', 'desc' => 'سجّل واختر «متدرب». أكمل البيانات الشخصية: الهوية، الهاتف، المحافظة، المؤهل.', 'link' => 'register.php', 'linkText' => 'إنشاء حساب'],
      ['title' => 'أكمل طلب التسجيل', 'desc' => 'قدّم طلب تسجيل متدرب رسمي لاعتماد ملفك لدى الهيئة.', 'link' => 'services/training/trainee-registration-request.php', 'linkText' => 'طلب تسجيل متدرب'],
      ['title' => 'تصفّح الدورات', 'desc' => 'استعرض البرامج المعتمدة واختر ما يناسبك.', 'link' => 'services/training/training-programs-list.php', 'linkText' => 'البرامج التدريبية'],
      ['title' => 'سجّل في دورة', 'desc' => 'قدّم طلب تسجيل. إذا كنت قاصراً، يُطلب تأكيد ولي الأمر.', 'link' => 'services/training/course-registration-request.php', 'linkText' => 'طلب تسجيل دورة'],
      ['title' => 'احصل على شهادتك', 'desc' => 'تُصدر شهادتك إلكترونياً ويمكن التحقق منها فوراً.', 'link' => 'services/training/training-verification.php', 'linkText' => 'التحقق من الشهادات'],
    ],
  ],
  [
    'id' => 'trainer', 'icon' => 'bi-person-workspace', 'title' => 'المدرب',
    'image' => "$IMG/photo-1507003211169-0a1dd7228f2d?w=800&q=85&fit=crop",
    'summary' => 'انضم لشبكة المدربين المعتمدين وقدّم دوراتك عبر مراكز معتمدة.',
    'register' => 'register.php', 'registerNote' => 'اختر «مدرب» في الخطوة الأولى من التسجيل',
    'steps' => [
      ['title' => 'أنشئ حسابك', 'desc' => 'سجّل واختر «مدرب» مع تخصصك وبيانات ToT.', 'link' => 'register.php', 'linkText' => 'إنشاء حساب'],
      ['title' => 'قدّم طلب اعتماد', 'desc' => 'أرسل طلب تسجيل مدرب لمراجعة الهيئة.', 'link' => 'services/training/trainer-registration-request.php', 'linkText' => 'طلب تسجيل مدرب'],
      ['title' => 'حدّث ملفك المهني', 'desc' => 'أكمل سيرتك وتخصصاتك في ملف المدرب.', 'link' => 'services/training/my-trainer-profile.php', 'linkText' => 'ملفي كمدرب'],
      ['title' => 'اربط بمركز تدريبي', 'desc' => 'تعاون مع مركز معتمد لتقديم دوراتك.', 'link' => 'services/training/training-centers-list.php', 'linkText' => 'المراكز التدريبية'],
      ['title' => 'رشّح حقائب تدريبية', 'desc' => 'رشّح حقائب للاعتماد وتابع حالة الترشيح.', 'link' => 'services/training/training-kit-nomination.php', 'linkText' => 'ترشيح حقيبة'],
    ],
  ],
  [
    'id' => 'center', 'icon' => 'bi-building-add', 'title' => 'مركز التدريب',
    'image' => "$IMG/photo-1580582932707-520aed937b7b?w=800&q=85&fit=crop",
    'summary' => 'سجّل مركزك، اعتمد برامجك، وأصدر شهادات إلكترونية.',
    'register' => 'register.php', 'registerNote' => 'اختر «مركز تدريبي» في التسجيل',
    'steps' => [
      ['title' => 'أنشئ حساب المركز', 'desc' => 'الاسم، العنوان، الإحداثيات، وارفق الترخيص.', 'link' => 'register.php', 'linkText' => 'إنشاء حساب'],
      ['title' => 'قدّم طلب اعتماد', 'desc' => 'أرسل طلب تسجيل مركز رسمي.', 'link' => 'services/training/center-registration-request.php', 'linkText' => 'طلب تسجيل مركز'],
      ['title' => 'أدر برامجك', 'desc' => 'أضف البرامج والدورات والحقائب من لوحة التحكم.', 'link' => 'services/training/training-programs-list.php', 'linkText' => 'البرامج التدريبية'],
      ['title' => 'أصدر الشهادات', 'desc' => 'أصدر شهادات للمتدربين عبر سلسلة اعتماد.', 'link' => 'services/training/training-certificates-list.php', 'linkText' => 'الشهادات'],
      ['title' => 'فعّل التوقيع الإلكتروني', 'desc' => 'ESIG لاعتماد الشهادات رقمياً.', 'link' => 'services/training/my-electronic-signature.php', 'linkText' => 'التوقيع الإلكتروني'],
    ],
  ],
  [
    'id' => 'project_owner', 'icon' => 'bi-rocket-takeoff-fill', 'title' => 'صاحب مشروع / رائد أعمال',
    'image' => "$IMG/photo-1559136555-9303baea8ebd?w=800&q=85&fit=crop",
    'summary' => 'تمويل، احتضان، وإدارة مشروعك — من حساب واحد بصلاحيات صاحب مشروع.',
    'register' => 'register.php?type=project_owner', 'registerNote' => 'اختر «صاحب مشروع / رائد أعمال» في التسجيل',
    'steps' => [
      ['title' => 'أنشئ حسابك', 'desc' => 'سجّل واختر «صاحب مشروع / رائد أعمال».', 'link' => 'register.php?type=project_owner', 'linkText' => 'إنشاء حساب'],
      ['title' => 'قدّم طلب تمويل', 'desc' => 'املأ طلب التمويل وتابع مراحل المراجعة.', 'link' => 'services/finance/finance-apply.php', 'linkText' => 'طلب تمويل'],
      ['title' => 'عبّئ استبيان المشروع', 'desc' => '14 محوراً: السوق، التمويل، الفريق، التقنية.', 'link' => 'services/incubation/entrepreneur-profile.php', 'linkText' => 'استبيان المشروع'],
      ['title' => 'قدّم طلب احتضان', 'desc' => 'اختر الحاضنة واملأ بيانات مشروعك.', 'link' => 'services/incubation/incubation-apply.php', 'linkText' => 'التقدّم للاحتضان'],
      ['title' => 'تابع طلباتك', 'desc' => 'التمويل والاحتضان من لوحة صاحب المشروع.', 'link' => 'services/finance/project-owner-dashboard.php', 'linkText' => 'لوحة التمويل'],
    ],
  ],
  [
    'id' => 'consulting', 'icon' => 'bi-briefcase-fill', 'title' => 'مكتب الاستشارات',
    'image' => "$IMG/photo-1521791136064-7986c2920216?w=800&q=85&fit=crop",
    'summary' => 'سجّل مكتبك وقدّم استشارات للمشاريع.',
    'register' => 'services/consulting/consulting-office-create.php', 'registerNote' => 'التسجيل عبر صفحة إنشاء مكتب استشاري',
    'steps' => [
      ['title' => 'أنشئ حسابك', 'desc' => 'سجّل حساباً عاماً أولاً.', 'link' => 'register.php', 'linkText' => 'إنشاء حساب'],
      ['title' => 'سجّل مكتبك', 'desc' => 'بيانات المكتب والتخصصات والتراخيص.', 'link' => 'services/consulting/consulting-office-create.php', 'linkText' => 'تسجيل مكتب'],
      ['title' => 'انتظر الاعتماد', 'desc' => 'تراجع الهيئة طلبك وتُدرج مكتبك.', 'link' => 'services/consulting/consulting-offices-list.php', 'linkText' => 'مكاتب الاستشارات'],
      ['title' => 'استقبل الطلبات', 'desc' => 'طلبات الاستشارة تصلك عبر المنصة.', 'link' => 'services/consulting/consulting-requests-list.php', 'linkText' => 'طلبات الاستشارة'],
    ],
  ],
  [
    'id' => 'jobseeker', 'icon' => 'bi-person-lines-fill', 'title' => 'باحث عن عمل',
    'image' => "$IMG/photo-1486312338219-ce68d2c6f44d?w=800&q=85&fit=crop",
    'summary' => 'أنشئ ملفك المهني وتقدّم لفرص العمل.',
    'register' => 'services/workforce/job-request.php', 'registerNote' => 'أنشئ حساباً ثم أكمل ملفك المهني',
    'steps' => [
      ['title' => 'أنشئ حسابك', 'desc' => 'سجّل للوصول لخدمات القوى العاملة.', 'link' => 'register.php', 'linkText' => 'إنشاء حساب'],
      ['title' => 'أكمل ملفك', 'desc' => 'سيرتك الذاتية ومجالات خبرتك.', 'link' => 'services/workforce/job-request.php', 'linkText' => 'ملفي المهني'],
      ['title' => 'تصفّح الفرص', 'desc' => 'فرص العمل من المشاريع والمؤسسات.', 'link' => 'services/workforce/jobs-list.php', 'linkText' => 'فرص العمل'],
      ['title' => 'قدّم طلباً', 'desc' => 'اختر الفرصة وقدّم مباشرة.', 'link' => 'services/workforce/jobs-list.php', 'linkText' => 'تقديم طلب'],
    ],
  ],
];

$quickSteps = [
  ['num' => '١', 'title' => 'تعرّف على المنصة', 'desc' => 'تصفّح الصفحة الرئيسية واقرأ عن الخدمات.', 'img' => "$IMG/photo-1497366216548-37526070297c?w=600&q=85&fit=crop", 'link' => 'index.php'],
  ['num' => '٢', 'title' => 'أنشئ حسابك', 'desc' => 'حساب واحد يفتح لك كل الخدمات مجاناً.', 'img' => "$IMG/photo-1432888498266-38ffec3eaf0a?w=600&q=85&fit=crop", 'link' => 'register.php'],
  ['num' => '٣', 'title' => 'اختر خدمتك', 'desc' => 'تدريب، تمويل، حاضنة، استشارة، أو عمل.', 'img' => "$IMG/photo-1551288049-bebda4e38f71?w=600&q=85&fit=crop", 'link' => 'services/index.php'],
  ['num' => '٤', 'title' => 'تابع من لوحتك', 'desc' => 'لوحة التحكم تعرض طلباتك خطوة بخطوة.', 'img' => "$IMG/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop", 'link' => 'dashboard.php'],
];

$modules = [
  ['id' => 'mod-training', 'icon' => 'bi-mortarboard-fill', 'title' => 'التدريب والشهادات',
    'image' => "$IMG/photo-1516321318423-f06f85e504b3?w=700&q=85&fit=crop",
    'intro' => 'اعتماد المراكز والمدربين والمتدربين، مع شهادات إلكترونية قابلة للتحقق.',
    'steps' => ['التسجيل → طلب اعتماد → مراجعة الهيئة → الموافقة', 'التسجيل في دورة → حضور → شهادة → اعتماد', 'التحقق العام برقم الشهادة'],
    'links' => [['text' => 'المراكز التدريبية', 'url' => 'services/training/training-centers-list.php'], ['text' => 'التحقق من الشهادات', 'url' => 'services/training/training-verification.php']]],
  ['id' => 'mod-finance', 'icon' => 'bi-bank2', 'title' => 'التمويل',
    'image' => "$IMG/photo-1554224155-6726b3ff858f?w=700&q=85&fit=crop",
    'intro' => '9 خطوات لطلب التمويل، ثم مراجعة الهيئة والسحابة التمويلية.',
    'steps' => ['١–٣: تعريف الطلب وبيانات مقدمه والتمويل', '٤–٦: المكاتب الاستشارية والنشاط والبيانات المالية', '٧–٩: العمالة والتدريب والمراجعة والإرسال'],
    'links' => [['text' => 'قدّم طلب تمويل', 'url' => 'services/finance/finance-apply.php'], ['text' => 'سحابة القروض', 'url' => 'services/finance/finance-cloud.php']]],
  ['id' => 'mod-incubation', 'icon' => 'bi-buildings-fill', 'title' => 'ريادة الأعمال والحاضنات',
    'image' => "$IMG/photo-1497366811353-6870744d04b2?w=700&q=85&fit=crop",
    'intro' => 'بوابة للأفكار والمشاريع الناشئة مع حاضنات في كل المحافظات.',
    'steps' => ['استبيان 14 محوراً', 'اكتشاف → اختيار → تقديم → قبول → احتضان', 'إرشاد + قصص نجاح'],
    'links' => [['text' => 'بوابة ريادة الأعمال', 'url' => 'services/incubation/entrepreneurship-hub.php'], ['text' => 'الحاضنات', 'url' => 'services/incubation/incubators.php']]],
  ['id' => 'mod-consulting', 'icon' => 'bi-headset', 'title' => 'الاستشارات',
    'image' => "$IMG/photo-1521791136064-7986c2920216?w=700&q=85&fit=crop",
    'intro' => 'اطلب استشارة أو سجّل مكتبك.',
    'steps' => ['تصفّح المكاتب المعتمدة', 'أنشئ طلب استشارة', 'تابع حتى الرد'],
    'links' => [['text' => 'مكاتب الاستشارات', 'url' => 'services/consulting/consulting-offices-list.php'], ['text' => 'طلب استشارة', 'url' => 'services/consulting/consulting-request-create.php']]],
  ['id' => 'mod-workforce', 'icon' => 'bi-people-fill', 'title' => 'القوى العاملة',
    'image' => "$IMG/photo-1522071820081-009f0129c71c?w=700&q=85&fit=crop",
    'intro' => 'ربط الباحثين عن عمل بفرص المشاريع.',
    'steps' => ['الباحث: ملف → تصفّح → تقديم', 'صاحب المشروع: نشر → استقبال → اختيار'],
    'links' => [['text' => 'فرص العمل', 'url' => 'services/workforce/jobs-list.php'], ['text' => 'الكفاءات', 'url' => 'services/workforce/candidates-list.php']]],
  ['id' => 'mod-gis', 'icon' => 'bi-geo-alt-fill', 'title' => 'خريطة الاحتياجات',
    'image' => "$IMG/photo-1551288049-bebda4e38f71?w=700&q=85&fit=crop",
    'intro' => 'سجّل احتياجات مشروعك على خريطة تفاعلية.',
    'steps' => ['سجّل الدخول', 'حدّد الموقع وصفّ الاحتياج', 'تابع من لوحة المؤشرات'],
    'links' => [['text' => 'الخريطة', 'url' => 'services/gis/needs-map.php'], ['text' => 'تسجيل احتياج', 'url' => 'services/gis/need-create.php']]],
];

$faqs = [
  ['q' => 'هل التسجيل مجاني؟', 'a' => 'نعم. إنشاء الحساب ومعظم الخدمات الأساسية مجانية.'],
  ['q' => 'هل أحتاج حساباً لكل خدمة؟', 'a' => 'لا. حساب واحد يكفي — الخدمات تظهر حسب صلاحياتك.'],
  ['q' => 'كيف أتابع طلباتي؟', 'a' => 'من لوحة التحكم أو صفحات «طلباتي» في كل قسم.'],
  ['q' => 'نسيت كلمة المرور؟', 'a' => 'تواصل مع الدعم عبر «تواصل معنا» أو +963 60701377.'],
  ['q' => 'ما الفرق بين التسجيل وطلب الاعتماد؟', 'a' => 'التسجيل ينشئ حسابك. طلب الاعتماد يرسل ملفك للمراجعة الرسمية.'],
  ['q' => 'أين دليل تصنيف المشروعات؟', 'a' => 'في الصفحة الرئيسية → قسم الوثائق: SYRSIC ودليل SME.'],
];
?>
<?php include __DIR__ . '/includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/includes/layout/head.php'; ?>
  <style>
    :root {
      --c-dark:#062824; --c-deep:#0F4F47; --c-primary:#17947B; --c-accent:#06AA89;
      --c-soft:#f2fbf8; --c-soft2:#f7fcfa; --c-line:rgba(23,148,123,.14);
      --c-text:#1a3530; --c-body:#4b5563; --c-muted:#6b7280;
      --shadow-sm:0 8px 24px rgba(6,40,36,.07);
      --shadow-md:0 16px 40px rgba(6,40,36,.10);
    }
    *,*::before,*::after{box-sizing:border-box}
    body{background:#fff;color:var(--c-text);overflow-x:hidden}
    a{text-decoration:none;color:inherit}

    [data-reveal]{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
    [data-reveal].visible{opacity:1;transform:none}
    [data-reveal-delay="1"]{transition-delay:.1s}
    [data-reveal-delay="2"]{transition-delay:.2s}
    [data-reveal-delay="3"]{transition-delay:.32s}

    /* ── HERO ── */
    .ug-hero{
      position:relative;min-height:88vh;display:flex;align-items:center;
      background:var(--c-dark) url('<?= $IMG ?>/photo-1522071820081-009f0129c71c?w=1400&q=85&fit=crop') center/cover no-repeat;
      overflow:hidden;
    }
    .ug-hero::before{
      content:'';position:absolute;inset:0;
      background:linear-gradient(135deg,rgba(6,40,36,.93) 0%,rgba(15,79,71,.82) 50%,rgba(23,148,123,.55) 100%);
    }
    .ug-hero-inner{
      position:relative;z-index:2;max-width:1100px;margin:0 auto;padding:80px 24px;
      display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;
    }
    @media(max-width:900px){.ug-hero-inner{grid-template-columns:1fr;gap:32px;padding:60px 20px}.ug-hero{min-height:auto}}
    .ug-hero-badge{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);
      border-radius:30px;padding:7px 18px;font-size:.83rem;font-weight:700;
      color:rgba(255,255,255,.9);margin-bottom:18px;
      animation:ugFadeDown .7s .1s ease both;
    }
    .ug-hero h1{
      font-size:clamp(1.9rem,5vw,2.9rem);font-weight:900;color:#fff;
      line-height:1.2;margin-bottom:16px;
      animation:ugFadeDown .7s .22s ease both;
    }
    .ug-hero h1 span{color:var(--c-accent)}
    .ug-hero>p{
      font-size:1.05rem;color:rgba(255,255,255,.82);line-height:1.85;
      margin-bottom:28px;max-width:480px;
      animation:ugFadeDown .7s .34s ease both;
    }
    .ug-hero-btns{display:flex;gap:12px;flex-wrap:wrap;animation:ugFadeDown .7s .46s ease both}
    .ug-btn-white{
      display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--c-primary);
      font-weight:800;font-size:.95rem;padding:13px 28px;border-radius:14px;
      box-shadow:0 8px 24px rgba(0,0,0,.2);transition:transform .2s,box-shadow .2s;
    }
    .ug-btn-white:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,.25);color:var(--c-primary)}
    .ug-btn-ghost{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(255,255,255,.1);color:#fff;font-weight:700;font-size:.93rem;
      padding:13px 24px;border-radius:14px;border:1.5px solid rgba(255,255,255,.3);transition:background .2s;
    }
    .ug-btn-ghost:hover{background:rgba(255,255,255,.18);color:#fff}
    .ug-stats-card{
      background:rgba(255,255,255,.09);backdrop-filter:blur(14px);
      border:1px solid rgba(255,255,255,.15);border-radius:24px;
      padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:20px;
      animation:ugFadeDown .8s .55s ease both;
    }
    .ug-stat{text-align:center}
    .ug-stat-val{font-size:2rem;font-weight:900;color:#fff}
    .ug-stat-lbl{font-size:.78rem;color:rgba(255,255,255,.65);font-weight:600;margin-top:4px}
    .ug-scroll-cue{
      position:absolute;bottom:28px;left:50%;transform:translateX(-50%);z-index:2;
      display:flex;flex-direction:column;align-items:center;gap:6px;
      color:rgba(255,255,255,.5);font-size:.75rem;font-weight:600;
      animation:ugBounce 2s ease infinite;
    }
    @keyframes ugFadeDown{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
    @keyframes ugBounce{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(6px)}}

    /* ── LAYOUT ── */
    .ug-body-wrap{padding:0 0 90px}
    .ug-layout{display:grid;grid-template-columns:250px 1fr;gap:32px;padding-top:60px;align-items:start}
    @media(max-width:991px){.ug-layout{grid-template-columns:1fr}.ug-sidebar{display:none}}
    .ug-sidebar{
      position:sticky;top:96px;background:var(--c-soft);border:1px solid var(--c-line);
      border-radius:20px;padding:20px 14px;
    }
    .ug-sidebar h4{font-size:.85rem;font-weight:900;color:var(--c-muted);margin-bottom:10px;padding:0 8px}
    .ug-sidebar a{
      display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;
      color:var(--c-text);font-weight:700;font-size:.88rem;transition:background .2s,transform .2s;
    }
    .ug-sidebar a:hover,.ug-sidebar a.active{background:#fff;color:var(--c-primary);box-shadow:var(--shadow-sm);transform:translateX(-3px)}
    .ug-sidebar a i{color:var(--c-primary)}

    .ug-section{margin-bottom:64px;scroll-margin-top:100px}
    .ug-label{
      display:inline-flex;align-items:center;gap:8px;
      background:var(--c-soft);color:var(--c-primary);border-radius:30px;
      padding:6px 16px;font-size:.8rem;font-weight:800;margin-bottom:12px;
    }
    .ug-section-title{font-size:clamp(1.4rem,3vw,1.85rem);font-weight:900;color:var(--c-dark);margin-bottom:10px}
    .ug-section-sub{font-size:1rem;color:var(--c-body);line-height:1.75;max-width:600px;margin-bottom:28px}

    /* ── QUICK JOURNEY CARDS ── */
    .ug-journey-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px}
    .ug-journey-card{
      background:#fff;border:1.5px solid var(--c-line);border-radius:20px;overflow:hidden;
      box-shadow:var(--shadow-sm);transition:transform .25s,box-shadow .25s;display:block;
    }
    .ug-journey-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-md);color:var(--c-text)}
    .ug-journey-card-img{height:140px;overflow:hidden;position:relative}
    .ug-journey-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
    .ug-journey-card:hover .ug-journey-card-img img{transform:scale(1.06)}
    .ug-journey-card-img::after{
      content:'';position:absolute;inset:0;
      background:linear-gradient(to top,rgba(6,40,36,.5),transparent 60%);
    }
    .ug-journey-num{
      position:absolute;bottom:12px;right:14px;z-index:2;
      width:36px;height:36px;border-radius:50%;
      background:linear-gradient(135deg,var(--c-primary),var(--c-accent));
      color:#fff;font-weight:900;font-size:.95rem;
      display:flex;align-items:center;justify-content:center;
    }
    .ug-journey-body{padding:18px 20px 22px}
    .ug-journey-body h3{font-size:.97rem;font-weight:900;margin-bottom:6px;color:var(--c-dark)}
    .ug-journey-body p{font-size:.82rem;color:var(--c-muted);line-height:1.65;margin:0}

    /* ── PERSONA TABS ── */
    .ug-tabs{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px}
    .ug-tab{
      padding:10px 18px;border-radius:999px;border:1.5px solid var(--c-line);
      background:#fff;color:var(--c-text);font-weight:800;font-size:.88rem;cursor:pointer;
      transition:all .22s;display:inline-flex;align-items:center;gap:8px;
    }
    .ug-tab:hover{border-color:rgba(23,148,123,.35);color:var(--c-primary);transform:translateY(-2px)}
    .ug-tab.active{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));border-color:transparent;color:#fff;box-shadow:0 6px 20px rgba(23,148,123,.28)}
    .ug-persona-panel{display:none}
    .ug-persona-panel.active{display:block;animation:ugPanelIn .35s ease}
    @keyframes ugPanelIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

    .ug-persona-banner{
      border-radius:22px;overflow:hidden;margin-bottom:24px;
      display:grid;grid-template-columns:280px 1fr;min-height:180px;
      border:1px solid var(--c-line);box-shadow:var(--shadow-sm);
    }
    @media(max-width:640px){.ug-persona-banner{grid-template-columns:1fr}}
    .ug-persona-banner-img{position:relative;min-height:180px;overflow:hidden}
    .ug-persona-banner-img img{width:100%;height:100%;object-fit:cover}
    .ug-persona-banner-body{
      padding:24px;background:var(--c-soft);
      display:flex;flex-direction:column;justify-content:center;
    }
    .ug-persona-banner-body h3{font-size:1.15rem;font-weight:900;margin-bottom:8px;color:var(--c-dark)}
    .ug-persona-banner-body p{font-size:.92rem;color:var(--c-body);line-height:1.7;margin:0 0 12px}
    .ug-hint{font-size:.85rem;font-weight:700;color:var(--c-primary);display:flex;align-items:center;gap:6px;flex-wrap:wrap}

    .ug-steps{list-style:none;padding:0;margin:0}
    .ug-step{
      display:flex;gap:16px;padding:20px 0;border-bottom:1px solid var(--c-line);
      transition:background .2s;border-radius:12px;margin:0 -8px;padding-inline:8px;
    }
    .ug-step:hover{background:var(--c-soft2)}
    .ug-step:last-child{border-bottom:none}
    .ug-step-num{
      width:40px;height:40px;border-radius:12px;flex-shrink:0;
      background:#eaf8f4;border:1px solid rgba(23,148,123,.18);
      color:var(--c-deep);font-weight:900;display:flex;align-items:center;justify-content:center;
    }
    .ug-step-body h4{font-size:1rem;font-weight:900;margin-bottom:6px;color:var(--c-dark)}
    .ug-step-body p{font-size:.9rem;color:var(--c-body);line-height:1.8;margin:0 0 10px}
    .ug-step-link{
      display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:800;
      color:var(--c-primary);padding:7px 16px;border-radius:999px;
      background:rgba(23,148,123,.08);border:1px solid rgba(23,148,123,.18);transition:all .2s;
    }
    .ug-step-link:hover{background:rgba(23,148,123,.14);transform:translateX(-3px);color:var(--c-primary)}

    /* ── MODULE CARDS with image ── */
    .ug-module{
      display:grid;grid-template-columns:220px 1fr;gap:0;
      background:#fff;border:1px solid var(--c-line);border-radius:22px;
      overflow:hidden;margin-bottom:16px;box-shadow:var(--shadow-sm);
      transition:transform .25s,box-shadow .25s;
    }
    .ug-module:hover{transform:translateY(-4px);box-shadow:var(--shadow-md)}
    @media(max-width:640px){.ug-module{grid-template-columns:1fr}}
    .ug-module-img{position:relative;min-height:200px;overflow:hidden}
    @media(max-width:640px){.ug-module-img{height:160px;min-height:0}}
    .ug-module-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
    .ug-module:hover .ug-module-img img{transform:scale(1.05)}
    .ug-module-body{padding:24px}
    .ug-module-body h3{font-size:1.05rem;font-weight:900;margin-bottom:8px;display:flex;align-items:center;gap:10px;color:var(--c-dark)}
    .ug-module-body h3 i{color:var(--c-primary)}
    .ug-module-body>p{font-size:.92rem;color:var(--c-body);line-height:1.8;margin-bottom:14px}
    .ug-flow-list{list-style:none;padding:0;margin:0 0 14px}
    .ug-flow-list li{
      font-size:.86rem;font-weight:700;color:var(--c-text);padding:7px 12px;
      border-radius:10px;background:var(--c-soft);margin-bottom:5px;
    }
    .ug-module-links{display:flex;flex-wrap:wrap;gap:8px}
    .ug-module-links a{
      font-size:.82rem;font-weight:800;color:var(--c-primary);padding:7px 14px;
      border-radius:999px;border:1px solid rgba(23,148,123,.22);transition:background .2s;
    }
    .ug-module-links a:hover{background:rgba(23,148,123,.08)}

    /* ── FAQ ── */
    .ug-faq-item{border:1px solid var(--c-line);border-radius:16px;margin-bottom:10px;overflow:hidden;background:#fff;transition:box-shadow .2s}
    .ug-faq-item.open{box-shadow:var(--shadow-sm)}
    .ug-faq-q{
      width:100%;text-align:right;padding:16px 20px;background:none;border:none;cursor:pointer;
      font-weight:800;font-size:.95rem;color:var(--c-dark);
      display:flex;align-items:center;justify-content:space-between;gap:12px;
    }
    .ug-faq-q i{color:var(--c-primary);transition:transform .25s}
    .ug-faq-item.open .ug-faq-q i{transform:rotate(180deg)}
    .ug-faq-a{display:none;padding:0 20px 16px;font-size:.9rem;color:var(--c-body);line-height:1.85}
    .ug-faq-item.open .ug-faq-a{display:block;animation:ugPanelIn .25s ease}

    /* ── CTA BAND ── */
    .ug-cta-band{
      background:linear-gradient(135deg,var(--c-primary),var(--c-accent));
      border-radius:24px;padding:56px 32px;text-align:center;color:#fff;
      position:relative;overflow:hidden;margin-top:20px;
    }
    .ug-cta-band::before{
      content:'';position:absolute;inset:0;
      background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
    }
    .ug-cta-band h3{font-weight:900;font-size:1.5rem;margin-bottom:10px;position:relative}
    .ug-cta-band p{color:rgba(255,255,255,.88);margin-bottom:28px;line-height:1.7;position:relative}
    .ug-cta-btns{display:flex;justify-content:center;gap:14px;flex-wrap:wrap;position:relative}
    .ug-btn-primary{
      display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:14px;
      background:#fff;color:var(--c-primary);font-weight:800;
      box-shadow:0 8px 24px rgba(0,0,0,.15);transition:transform .2s;
    }
    .ug-btn-primary:hover{transform:translateY(-2px);color:var(--c-primary)}
    .ug-btn-outline{
      display:inline-flex;align-items:center;gap:8px;padding:13px 24px;border-radius:14px;
      border:1.5px solid rgba(255,255,255,.4);color:#fff;font-weight:700;transition:background .2s;
    }
    .ug-btn-outline:hover{background:rgba(255,255,255,.12);color:#fff}

    .ug-section--soft{background:var(--c-soft);margin-inline:calc(50% - 50vw);padding:64px calc(50vw - 50%);margin-bottom:64px}
    @media(prefers-reduced-motion:reduce){
      [data-reveal],.ug-hero-badge,.ug-hero h1,.ug-hero>p,.ug-hero-btns,.ug-stats-card{animation:none;opacity:1;transform:none}
      .ug-scroll-cue{animation:none}
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/includes/layout/header.php'; ?>

<section class="ug-hero">
  <div class="ug-hero-inner">
    <div>
      <div class="ug-hero-badge"><i class="bi bi-book-fill"></i> دليل المستخدم — SMEDC</div>
      <h1>كل ما تحتاجه<br><span>من A إلى Z</span></h1>
      <p>شرح واضح لكل خطوة: التسجيل، اختيار دورك، استخدام الخدمات، ومتابعة طلباتك — تجربة بسيطة بدون تعقيد.</p>
      <div class="ug-hero-btns">
        <a href="#quick-start" class="ug-btn-white"><i class="bi bi-lightning-fill"></i> ابدأ الآن</a>
        <a href="#choose-role" class="ug-btn-ghost"><i class="bi bi-person-check-fill"></i> اختر دورك</a>
      </div>
    </div>
    <div class="ug-stats-card">
      <div class="ug-stat"><div class="ug-stat-val">6</div><div class="ug-stat-lbl">أدوار مستخدم</div></div>
      <div class="ug-stat"><div class="ug-stat-val">6+</div><div class="ug-stat-lbl">خدمات رئيسية</div></div>
      <div class="ug-stat"><div class="ug-stat-val">مجاناً</div><div class="ug-stat-lbl">التسجيل</div></div>
      <div class="ug-stat"><div class="ug-stat-val">100%</div><div class="ug-stat-lbl">رقمياً</div></div>
    </div>
  </div>
  <div class="ug-scroll-cue"><span>اكتشف الدليل</span><i class="bi bi-chevron-double-down"></i></div>
</section>

<div class="container ug-body-wrap">
  <div class="ug-layout">

    <aside class="ug-sidebar">
      <h4>محتويات الدليل</h4>
      <nav>
        <a href="#quick-start"><i class="bi bi-lightning-fill"></i> البداية السريعة</a>
        <a href="#choose-role"><i class="bi bi-people-fill"></i> اختر دورك</a>
        <a href="#services"><i class="bi bi-grid-3x3-gap-fill"></i> الخدمات</a>
        <a href="#dashboard"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
        <a href="#faq"><i class="bi bi-question-circle-fill"></i> أسئلة شائعة</a>
        <a href="<?= front_url('index.php#contact') ?>"><i class="bi bi-headset"></i> الدعم</a>
      </nav>
    </aside>

    <div class="ug-main">

      <section class="ug-section" id="quick-start">
        <div data-reveal>
          <span class="ug-label"><i class="bi bi-lightning-fill"></i> البداية السريعة</span>
          <h2 class="ug-section-title">4 خطوات للجميع — بغضّ النظر عن دورك</h2>
          <p class="ug-section-sub">اضغط على أي بطاقة للانتقال مباشرة.</p>
        </div>
        <div class="ug-journey-grid">
          <?php foreach ($quickSteps as $i => $qs): ?>
          <a href="<?= front_url($qs['link']) ?>" class="ug-journey-card" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
            <div class="ug-journey-card-img">
              <img src="<?= $qs['img'] ?>" alt="<?= htmlspecialchars($qs['title']) ?>" loading="lazy">
              <span class="ug-journey-num"><?= $qs['num'] ?></span>
            </div>
            <div class="ug-journey-body">
              <h3><?= $qs['title'] ?></h3>
              <p><?= $qs['desc'] ?></p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="ug-section ug-section--soft" id="choose-role">
        <div data-reveal>
          <span class="ug-label"><i class="bi bi-people-fill"></i> اختر دورك</span>
          <h2 class="ug-section-title">مسارك خطوة بخطوة</h2>
          <p class="ug-section-sub">اضغط على دورك لترى بالضبط ماذا تفعل.</p>
        </div>

        <div class="ug-tabs" role="tablist" data-reveal data-reveal-delay="1">
          <?php foreach ($personas as $i => $p): ?>
          <button type="button" class="ug-tab<?= $i === 0 ? ' active' : '' ?>" data-persona="<?= $p['id'] ?>" role="tab">
            <i class="bi <?= $p['icon'] ?>"></i> <?= $p['title'] ?>
          </button>
          <?php endforeach; ?>
        </div>

        <?php foreach ($personas as $i => $p): ?>
        <div class="ug-persona-panel<?= $i === 0 ? ' active' : '' ?>" id="persona-<?= $p['id'] ?>" role="tabpanel">
          <div class="ug-persona-banner" data-reveal>
            <div class="ug-persona-banner-img">
              <img src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
            </div>
            <div class="ug-persona-banner-body">
              <h3><?= $p['title'] ?></h3>
              <p><?= $p['summary'] ?></p>
              <div class="ug-hint">
                <i class="bi bi-info-circle-fill"></i>
                <?= $p['registerNote'] ?> —
                <a href="<?= front_url($p['register']) ?>" style="color:inherit;text-decoration:underline">ابدأ من هنا</a>
              </div>
            </div>
          </div>
          <ol class="ug-steps">
            <?php foreach ($p['steps'] as $si => $step): ?>
            <li class="ug-step" data-reveal data-reveal-delay="<?= min($si + 1, 3) ?>">
              <div class="ug-step-num"><?= $si + 1 ?></div>
              <div class="ug-step-body">
                <h4><?= $step['title'] ?></h4>
                <p><?= $step['desc'] ?></p>
                <?php if (!empty($step['link'])): ?>
                <a href="<?= front_url($step['link']) ?>" class="ug-step-link">
                  <?= $step['linkText'] ?? 'انتقل' ?> <i class="bi bi-arrow-left"></i>
                </a>
                <?php endif; ?>
              </div>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>
        <?php endforeach; ?>
      </section>

      <section class="ug-section" id="services">
        <div data-reveal>
          <span class="ug-label"><i class="bi bi-grid-3x3-gap-fill"></i> الخدمات</span>
          <h2 class="ug-section-title">شرح كل وحدة خدمية</h2>
          <p class="ug-section-sub">ملخص المسار مع روابط مباشرة.</p>
        </div>
        <?php foreach ($modules as $i => $mod): ?>
        <div class="ug-module" id="<?= $mod['id'] ?>" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
          <div class="ug-module-img">
            <img src="<?= $mod['image'] ?>" alt="<?= htmlspecialchars($mod['title']) ?>" loading="lazy">
          </div>
          <div class="ug-module-body">
            <h3><i class="bi <?= $mod['icon'] ?>"></i> <?= $mod['title'] ?></h3>
            <p><?= $mod['intro'] ?></p>
            <ul class="ug-flow-list">
              <?php foreach ($mod['steps'] as $flow): ?><li><?= $flow ?></li><?php endforeach; ?>
            </ul>
            <div class="ug-module-links">
              <?php foreach ($mod['links'] as $lnk): ?>
              <a href="<?= front_url($lnk['url']) ?>"><?= $lnk['text'] ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </section>

      <section class="ug-section" id="dashboard">
        <div data-reveal>
          <span class="ug-label"><i class="bi bi-speedometer2"></i> المتابعة</span>
          <h2 class="ug-section-title">لوحة التحكم والإشعارات</h2>
        </div>
        <div class="ug-module" data-reveal data-reveal-delay="1">
          <div class="ug-module-img">
            <img src="<?= $IMG ?>/photo-1551288049-bebda4e38f71?w=700&q=85&fit=crop" alt="لوحة التحكم" loading="lazy">
          </div>
          <div class="ug-module-body">
            <h3><i class="bi bi-house-door-fill"></i> لوحة التحكم الرئيسية</h3>
            <p>تظهر الخدمات حسب صلاحياتك. كل دور يرى قائمة مختلفة.</p>
            <ul class="ug-flow-list">
              <li>تسجيل الدخول → توجيه تلقائي للوحة مناسبة</li>
              <li>القائمة الجانبية: خدماتك فقط</li>
              <li>الإشعارات عند تغيير حالة الطلب</li>
              <li>صندوق الوارد للمراسلات الرسمية</li>
            </ul>
            <div class="ug-module-links">
              <a href="<?= front_url('dashboard.php') ?>">لوحة التحكم</a>
              <a href="<?= front_url('my-profile.php') ?>">حسابي</a>
              <a href="<?= front_url('notifications/notifications-list.php') ?>">الإشعارات</a>
            </div>
          </div>
        </div>
        <div class="ug-module" data-reveal data-reveal-delay="2">
          <div class="ug-module-img">
            <img src="<?= $IMG ?>/photo-1454165804606-c3d57bc86b40?w=700&q=85&fit=crop" alt="حالات الطلب" loading="lazy">
          </div>
          <div class="ug-module-body">
            <h3><i class="bi bi-bell-fill"></i> حالات الطلب</h3>
            <ul class="ug-flow-list">
              <li><strong>قيد المراجعة</strong> — الهيئة تفحص بياناتك</li>
              <li><strong>ناقص</strong> — استكمل مستنداً أو بياناً</li>
              <li><strong>مقبول / مرفوض</strong> — قرار نهائي</li>
              <li><strong>قيد التنفيذ</strong> — مرحلة تالية (مثلاً السحابة التمويلية)</li>
            </ul>
          </div>
        </div>
      </section>

      <section class="ug-section" id="faq">
        <div data-reveal>
          <span class="ug-label"><i class="bi bi-question-circle-fill"></i> أسئلة شائعة</span>
          <h2 class="ug-section-title">إجابات سريعة</h2>
        </div>
        <?php foreach ($faqs as $fi => $faq): ?>
        <div class="ug-faq-item<?= $fi === 0 ? ' open' : '' ?>" data-reveal data-reveal-delay="<?= ($fi % 3) + 1 ?>">
          <button type="button" class="ug-faq-q" aria-expanded="<?= $fi === 0 ? 'true' : 'false' ?>">
            <?= $faq['q'] ?> <i class="bi bi-chevron-down"></i>
          </button>
          <div class="ug-faq-a"><?= $faq['a'] ?></div>
        </div>
        <?php endforeach; ?>
      </section>

      <div class="ug-cta-band" data-reveal>
        <h3>جاهز للبدء؟</h3>
        <p>أنشئ حسابك واختر الخدمة — المنصة ترافقك في كل خطوة.</p>
        <div class="ug-cta-btns">
          <a href="<?= front_url('register.php') ?>" class="ug-btn-primary"><i class="bi bi-person-plus-fill"></i> إنشاء حساب</a>
          <a href="<?= front_url('index.php') ?>" class="ug-btn-outline"><i class="bi bi-house-fill"></i> الرئيسية</a>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/layout/footer.php'; ?>
<?php include __DIR__ . '/includes/layout/scripts.php'; ?>

<script>
(function () {
  /* Scroll reveal */
  var revealEls = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window) {
    var revObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('visible'); revObs.unobserve(e.target); }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
    revealEls.forEach(function (el) { revObs.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('visible'); });
  }

  /* Persona tabs */
  document.querySelectorAll('.ug-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      var id = tab.getAttribute('data-persona');
      document.querySelectorAll('.ug-tab').forEach(function (t) { t.classList.remove('active'); });
      document.querySelectorAll('.ug-persona-panel').forEach(function (p) { p.classList.remove('active'); });
      tab.classList.add('active');
      var panel = document.getElementById('persona-' + id);
      if (panel) {
        panel.classList.add('active');
        panel.querySelectorAll('[data-reveal]').forEach(function (el) {
          if (!el.classList.contains('visible')) el.classList.add('visible');
        });
      }
    });
  });

  /* FAQ */
  document.querySelectorAll('.ug-faq-q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.ug-faq-item');
      var open = item.classList.contains('open');
      document.querySelectorAll('.ug-faq-item').forEach(function (i) {
        i.classList.remove('open');
        i.querySelector('.ug-faq-q').setAttribute('aria-expanded', 'false');
      });
      if (!open) { item.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
    });
  });

  /* Sidebar scroll spy */
  var sideLinks = document.querySelectorAll('.ug-sidebar a[href^="#"]');
  var secs = [];
  sideLinks.forEach(function (a) {
    var el = document.getElementById(a.getAttribute('href').slice(1));
    if (el) secs.push({ link: a, el: el });
  });
  if (secs.length && 'IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          sideLinks.forEach(function (l) { l.classList.remove('active'); });
          var m = secs.find(function (s) { return s.el === entry.target; });
          if (m) m.link.classList.add('active');
        }
      });
    }, { rootMargin: '-20% 0px -55% 0px', threshold: 0 });
    secs.forEach(function (s) { spy.observe(s.el); });
  }
})();
</script>
</body>
</html>
