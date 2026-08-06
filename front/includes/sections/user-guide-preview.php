<?php
$basePath = isset($basePath) ? $basePath : '';
if (!function_exists('front_url')) {
  require_once __DIR__ . '/../layout/paths.php';
}

$guideCards = [
  [
    'icon' => 'bi-lightning-fill', 'title' => '١. ابدأ',
    'desc' => 'تعرّف على المنصة وأنشئ حسابك مجاناً',
    'anchor' => 'user-guide.php#quick-start',
    'img' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&fit=crop',
  ],
  [
    'icon' => 'bi-person-check-fill', 'title' => '٢. اختر دورك',
    'desc' => 'مدرب، متدرب، رائد أعمال، أو باحث عن عمل',
    'anchor' => 'user-guide.php#choose-role',
    'img' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=85&fit=crop',
  ],
  [
    'icon' => 'bi-grid-3x3-gap-fill', 'title' => '٣. استخدم الخدمة',
    'desc' => 'تدريب، تمويل، حاضنة، استشارة، خريطة',
    'anchor' => 'user-guide.php#services',
    'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=85&fit=crop',
  ],
  [
    'icon' => 'bi-speedometer2', 'title' => '٤. تابع طلباتك',
    'desc' => 'من لوحة التحكم والإشعارات',
    'anchor' => 'user-guide.php#dashboard',
    'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=85&fit=crop',
  ],
];
?>

<style>
  .ug-preview-section { background: linear-gradient(180deg, #fff 0%, var(--c-soft) 100%); }
  .ug-preview-cards {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px; margin-top: 32px;
  }
  .ug-preview-card {
    background: #fff; border: 1px solid rgba(23,148,123,.12);
    border-radius: 20px; overflow: hidden;
    text-decoration: none; color: var(--c-text);
    transition: transform .28s cubic-bezier(.22,.68,0,1.15), box-shadow .28s;
    box-shadow: 0 2px 12px rgba(6,40,36,.05); display: block;
  }
  .ug-preview-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 24px 60px rgba(6,40,36,.14);
    color: var(--c-text);
  }
  .ug-preview-card-img {
    height: 150px; overflow: hidden; position: relative;
    background: linear-gradient(135deg, var(--c-deep), var(--c-primary));
  }
  .ug-preview-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .55s cubic-bezier(.22,.68,0,1.1);
  }
  .ug-preview-card:hover .ug-preview-card-img img { transform: scale(1.07); }
  .ug-preview-card-img::before {
    content: ''; position: absolute; inset: 0; z-index: 1;
    background: linear-gradient(to top, rgba(6,40,36,.55), transparent 55%);
  }
  .ug-preview-card-icon {
    position: absolute; bottom: 14px; right: 14px; z-index: 2;
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
    color: #fff; font-size: 1.15rem;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(23,148,123,.35);
  }
  .ug-preview-card-body { padding: 18px 20px 22px; }
  .ug-preview-card-body h3 { font-size: 1rem; font-weight: 900; margin-bottom: 8px; }
  .ug-preview-card-body p { font-size: .88rem; color: var(--c-body); line-height: 1.7; margin: 0; }
  .ug-preview-cta { text-align: center; margin-top: 36px; }
</style>

<section class="section ug-preview-section" id="user-guide">
  <div class="container">

    <div class="text-center mb-2" data-reveal>
      <span class="sec-badge"><i class="bi bi-book-fill"></i> دليل المستخدم</span>
      <h2 class="sec-title mt-2">كيف تستخدم المنصة — من A إلى Z</h2>
      <p class="sec-sub mx-auto mt-2">
        لا تعرف من أين تبدأ؟ الدليل يشرح كل شيء خطوة بخطوة — تجربة واضحة بدون تعقيد.
      </p>
    </div>

    <div class="ug-preview-cards">
      <?php foreach ($guideCards as $i => $card): ?>
      <a href="<?= front_url($card['anchor']) ?>" class="ug-preview-card" data-reveal data-reveal-delay="<?= $i + 1 ?>">
        <div class="ug-preview-card-img">
          <img src="<?= $card['img'] ?>" alt="<?= htmlspecialchars($card['title']) ?>" loading="lazy">
          <span class="ug-preview-card-icon"><i class="bi <?= $card['icon'] ?>"></i></span>
        </div>
        <div class="ug-preview-card-body">
          <h3><?= $card['title'] ?></h3>
          <p><?= $card['desc'] ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="ug-preview-cta" data-reveal data-reveal-delay="2">
      <a href="<?= front_url('user-guide.php') ?>" class="hero-btn-primary" style="display:inline-flex">
        <i class="bi bi-journal-text"></i>
        افتح الدليل الكامل
      </a>
      <a href="<?= front_url('index.php#faq') ?>" class="hero-btn-outline" style="display:inline-flex;margin-right:12px;color:var(--c-primary);border-color:rgba(23,148,123,.3)">
        <i class="bi bi-question-circle-fill"></i>
        أسئلة شائعة
      </a>
    </div>
  </div>
</section>
