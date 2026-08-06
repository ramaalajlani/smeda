<?php
/**
 * Service landing page — expects $landing array and $basePath.
 */
if (empty($landing) || !is_array($landing)) {
    http_response_code(404);
    exit('Not found');
}

if (!function_exists('front_url')) {
    require_once __DIR__ . '/../layout/paths.php';
}

$L = $landing;
$themeClass = 'sl-theme--' . ($L['theme'] ?? 'soft');
$stepCount = count($L['steps']);
?>
<?php include __DIR__ . '/../layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../layout/head.php'; ?>
  <style>
    :root {
      --c-dark: #062824; --c-deep: #0F4F47; --c-primary: #17947B; --c-accent: #06AA89;
      --c-soft: #f2fbf8; --c-body: #4b5563; --c-muted: #6b7280;
      --shadow-lg: 0 24px 60px rgba(6,40,36,.13);
    }
    body { background: #fff; color: #1a3530; overflow-x: hidden; }

    .sl-hero {
      position: relative; min-height: 72vh; display: flex; align-items: center;
      overflow: hidden; padding: 100px 0 80px;
      background: linear-gradient(135deg, var(--c-dark), var(--c-deep));
    }
    .sl-hero.has-img::before {
      content: ''; position: absolute; inset: 0; z-index: 0;
      background: url('<?= htmlspecialchars($L['hero_image']) ?>') center/cover no-repeat;
    }
    .sl-hero.has-img::after {
      content: ''; position: absolute; inset: 0; z-index: 1;
      background: linear-gradient(135deg, rgba(6,40,36,.92), rgba(15,79,71,.82));
    }
    .sl-hero .container { position: relative; z-index: 2; }
    .sl-back {
      display: inline-flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,.75); font-weight: 700; font-size: .88rem;
      margin-bottom: 20px; transition: color .2s;
    }
    .sl-back:hover { color: #fff; }
    .sl-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
      border-radius: 999px; padding: 8px 16px;
      font-size: .85rem; font-weight: 800; color: #fff; margin-bottom: 16px;
    }
    .sl-hero h1 {
      font-size: clamp(1.75rem, 4vw, 2.65rem); font-weight: 900;
      color: #fff; line-height: 1.25; margin-bottom: 16px; max-width: 720px;
    }
    .sl-hero-lead {
      font-size: 1.05rem; color: rgba(255,255,255,.85);
      line-height: 1.9; max-width: 620px; margin-bottom: 28px;
    }
    .sl-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
    .sl-btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 24px; border-radius: 12px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; font-weight: 800; font-size: .95rem;
      box-shadow: 0 8px 24px rgba(23,148,123,.35);
      transition: transform .2s, opacity .2s;
    }
    .sl-btn-primary:hover { transform: translateY(-2px); opacity: .95; color: #fff; }
    .sl-btn-outline {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 22px; border-radius: 12px;
      border: 1.5px solid rgba(255,255,255,.35); color: #fff;
      font-weight: 700; font-size: .93rem; transition: background .2s;
    }
    .sl-btn-outline:hover { background: rgba(255,255,255,.12); color: #fff; }

    .sl-progress-bar {
      position: sticky; top: 0; z-index: 100;
      background: rgba(255,255,255,.96); backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(23,148,123,.12);
      padding: 12px 0; display: none;
    }
    .sl-progress-bar.is-visible { display: block; }
    .sl-progress-inner {
      display: flex; align-items: center; gap: 16px;
    }
    .sl-progress-track {
      flex: 1; height: 6px; background: var(--c-soft);
      border-radius: 999px; overflow: hidden;
    }
    .sl-progress-fill {
      height: 100%; width: 0;
      background: linear-gradient(90deg, var(--c-primary), var(--c-accent));
      border-radius: 999px; transition: width .35s ease;
    }
    .sl-progress-label {
      font-size: .82rem; font-weight: 800; color: var(--c-primary);
      white-space: nowrap;
    }

    .sl-section { padding: 72px 0; }
    .sl-theme--soft { background: linear-gradient(180deg, var(--c-soft) 0%, #fff 100%); }
    .sl-theme--white { background: #fff; }
    .sl-theme--dark {
      background: linear-gradient(140deg, var(--c-dark), var(--c-deep));
      color: #fff;
    }
    .sl-theme--dark .sec-title { color: #fff; }

    .sl-benefits {
      list-style: none; padding: 0; margin: 0 0 24px;
      display: flex; flex-direction: column; gap: 10px;
    }
    .sl-benefits li {
      display: flex; align-items: flex-start; gap: 10px;
      font-weight: 700; font-size: .95rem; line-height: 1.7;
    }
    .sl-benefits li i { color: var(--c-primary); flex-shrink: 0; margin-top: 3px; }
    .sl-theme--dark .sl-benefits li { color: rgba(255,255,255,.9); }
    .sl-theme--dark .sl-benefits li i { color: var(--c-accent); }

    .sl-split-img {
      border-radius: 22px; overflow: hidden; box-shadow: var(--shadow-lg);
      aspect-ratio: 4/3;
    }
    .sl-split-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .sl-roadmap-shell {
      background: #fff; border: 1px solid rgba(23,148,123,.10);
      border-radius: 22px; padding: 28px 24px;
      box-shadow: 0 18px 45px rgba(6,40,36,.07);
    }
    .sl-theme--dark .sl-roadmap-shell {
      background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12);
    }
    .sl-roadmap-head { text-align: center; margin-bottom: 28px; }
    .sl-roadmap-head h2 {
      font-size: 1.25rem; font-weight: 900; color: var(--c-dark); margin-bottom: 8px;
      display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .sl-theme--dark .sl-roadmap-head h2 { color: #fff; }
    .sl-roadmap-head p { font-size: .92rem; color: var(--c-muted); margin: 0; line-height: 1.75; }
    .sl-theme--dark .sl-roadmap-head p { color: rgba(255,255,255,.75); }

    .sl-roadmap-layout {
      display: grid; grid-template-columns: 280px 1fr; gap: 28px; align-items: start;
    }
    @media (max-width: 991.98px) {
      .sl-roadmap-layout { grid-template-columns: 1fr; }
      .sl-roadmap-nav { position: relative !important; top: 0 !important; }
    }

    .sl-roadmap-nav {
      position: sticky; top: 80px;
      display: flex; flex-direction: column; gap: 6px;
    }
    .sl-nav-step {
      display: flex; align-items: flex-start; gap: 12px;
      padding: 12px 14px; border-radius: 14px; border: 1px solid transparent;
      background: transparent; cursor: pointer; text-align: right;
      width: 100%; transition: background .2s, border-color .2s;
    }
    .sl-nav-step:hover { background: var(--c-soft); }
    .sl-nav-step.is-active {
      background: var(--c-soft); border-color: rgba(23,148,123,.25);
      box-shadow: 0 4px 14px rgba(23,148,123,.12);
    }
    .sl-nav-num {
      width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
      background: #eaf8f4; color: var(--c-deep);
      font-size: .85rem; font-weight: 900;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid rgba(23,148,123,.18);
      transition: background .2s, color .2s;
    }
    .sl-nav-step.is-active .sl-nav-num,
    .sl-nav-step.is-done .sl-nav-num {
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; border-color: transparent;
    }
    .sl-nav-text strong {
      display: block; font-size: .88rem; font-weight: 800; color: var(--c-dark);
      line-height: 1.4;
    }
    .sl-nav-text span { font-size: .75rem; color: var(--c-muted); }

    .sl-step-panels { display: flex; flex-direction: column; gap: 16px; }
    .sl-step-panel {
      padding: 24px; border-radius: 18px;
      border: 1px solid rgba(23,148,123,.12);
      background: #fbfefd;
      opacity: .55; transform: scale(.98);
      transition: opacity .35s, transform .35s, border-color .35s, box-shadow .35s;
      scroll-margin-top: 100px;
    }
    .sl-step-panel.is-active {
      opacity: 1; transform: none;
      border-color: rgba(23,148,123,.28);
      box-shadow: 0 12px 32px rgba(6,40,36,.08);
    }
    .sl-step-panel-head {
      display: flex; align-items: center; gap: 14px; margin-bottom: 10px;
    }
    .sl-step-panel-icon {
      width: 48px; height: 48px; border-radius: 14px;
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; display: flex; align-items: center; justify-content: center;
      font-size: 1.25rem; flex-shrink: 0;
    }
    .sl-step-panel-head h3 {
      font-size: 1.05rem; font-weight: 900; color: var(--c-dark); margin: 0;
    }
    .sl-step-panel-head .sl-step-num {
      font-size: .78rem; font-weight: 800; color: var(--c-primary);
    }
    .sl-step-panel p {
      margin: 0; font-size: .95rem; color: var(--c-body); line-height: 1.85;
      padding-right: 62px;
    }
    @media (max-width: 575.98px) { .sl-step-panel p { padding-right: 0; } }

    .sl-mini-cards {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px; margin-top: 40px;
    }
    .sl-mini-card {
      background: #fff; border: 1px solid rgba(23,148,123,.10);
      border-radius: 18px; overflow: hidden; text-decoration: none; color: inherit;
      transition: transform .25s, box-shadow .25s;
      box-shadow: 0 2px 12px rgba(6,40,36,.05);
    }
    .sl-mini-card:hover {
      transform: translateY(-6px); box-shadow: var(--shadow-lg); color: inherit;
    }
    .sl-mini-card img { width: 100%; height: 130px; object-fit: cover; display: block; }
    .sl-mini-card-body { padding: 16px 18px 18px; }
    .sl-mini-card-body h4 { font-size: .97rem; font-weight: 900; margin-bottom: 6px; }
    .sl-mini-card-body p { font-size: .85rem; color: var(--c-body); margin: 0; line-height: 1.65; }

    .sl-cta-band {
      text-align: center; padding: 48px 24px;
      background: var(--c-soft); border-radius: 22px; margin-top: 48px;
    }
    .sl-cta-band h3 { font-size: 1.15rem; font-weight: 900; margin-bottom: 8px; }
    .sl-cta-band p { color: var(--c-muted); margin-bottom: 20px; font-size: .92rem; }
    .sl-cta-band .sl-btn-outline-light {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 22px; border-radius: 12px;
      border: 1.5px solid rgba(23,148,123,.3); color: var(--c-primary);
      font-weight: 700; background: #fff; transition: background .2s;
    }
    .sl-cta-band .sl-btn-outline-light:hover { background: var(--c-soft); color: var(--c-deep); }

    .sec-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--c-soft); color: var(--c-primary);
      font-size: .82rem; font-weight: 800;
      padding: 7px 16px; border-radius: 999px;
      border: 1px solid rgba(23,148,123,.18);
    }
    .sec-title { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 900; color: var(--c-dark); }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../layout/header.php'; ?>

  <div class="sl-progress-bar" id="slProgressBar" aria-hidden="true">
    <div class="container">
      <div class="sl-progress-inner">
        <span class="sl-progress-label" id="slProgressLabel">الخطوة 1 من <?= $stepCount ?></span>
        <div class="sl-progress-track">
          <div class="sl-progress-fill" id="slProgressFill"></div>
        </div>
      </div>
    </div>
  </div>

  <main>
    <header class="sl-hero has-img">
      <div class="container">
        <a href="<?= front_url('index.php#services') ?>" class="sl-back">
          <i class="bi bi-arrow-right"></i> العودة إلى الخدمات
        </a>
        <span class="sl-badge"><i class="bi <?= htmlspecialchars($L['badge_icon']) ?>"></i> <?= htmlspecialchars($L['badge']) ?></span>
        <h1><?= htmlspecialchars($L['hero_title']) ?></h1>
        <p class="sl-hero-lead"><?= htmlspecialchars($L['hero_text']) ?></p>
        <div class="sl-hero-actions">
          <a href="<?= front_url($L['cta_primary']['url']) ?>" class="sl-btn-primary">
            <i class="bi <?= htmlspecialchars($L['cta_primary']['icon']) ?>"></i>
            <?= htmlspecialchars($L['cta_primary']['label']) ?>
          </a>
          <a href="<?= front_url($L['cta_secondary']['url']) ?>" class="sl-btn-outline">
            <i class="bi <?= htmlspecialchars($L['cta_secondary']['icon']) ?>"></i>
            <?= htmlspecialchars($L['cta_secondary']['label']) ?>
          </a>
        </div>
      </div>
    </header>

    <section class="sl-section sl-theme--<?= htmlspecialchars($L['theme'] === 'dark' ? 'soft' : ($L['theme'] ?? 'soft')) ?>">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="sec-badge"><i class="bi bi-check2-circle"></i> لماذا هذه الخدمة؟</span>
            <h2 class="sec-title mt-2">ما الذي تحصل عليه؟</h2>
            <ul class="sl-benefits mt-4">
              <?php foreach ($L['benefits'] as $b): ?>
              <li><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($b) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="col-lg-6">
            <div class="sl-split-img">
              <img src="<?= htmlspecialchars($L['hero_image']) ?>" alt="<?= htmlspecialchars($L['badge']) ?>" loading="lazy">
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="sl-section sl-theme--<?= htmlspecialchars($L['theme'] ?? 'soft') ?>" id="slRoadmap">
      <div class="container">
        <div class="sl-roadmap-shell">
          <div class="sl-roadmap-head">
            <h2><i class="bi bi-map-fill" style="color:var(--c-primary)"></i> <?= htmlspecialchars($L['roadmap_title']) ?></h2>
            <p><?= htmlspecialchars($L['roadmap_note']) ?></p>
          </div>

          <div class="sl-roadmap-layout">
            <nav class="sl-roadmap-nav" aria-label="خطوات الرحلة">
              <?php foreach ($L['steps'] as $i => $step): ?>
              <button type="button" class="sl-nav-step<?= $i === 0 ? ' is-active' : '' ?>" data-sl-step="<?= $i ?>">
                <span class="sl-nav-num"><?= $i + 1 ?></span>
                <span class="sl-nav-text">
                  <strong><?= htmlspecialchars($step['title']) ?></strong>
                  <span>الخطوة <?= $i + 1 ?></span>
                </span>
              </button>
              <?php endforeach; ?>
            </nav>

            <div class="sl-step-panels">
              <?php foreach ($L['steps'] as $i => $step): ?>
              <article class="sl-step-panel<?= $i === 0 ? ' is-active' : '' ?>" id="slStep<?= $i ?>" data-sl-panel="<?= $i ?>">
                <div class="sl-step-panel-head">
                  <div class="sl-step-panel-icon"><i class="bi <?= htmlspecialchars($step['icon']) ?>"></i></div>
                  <div>
                    <div class="sl-step-num">الخطوة <?= $i + 1 ?> من <?= $stepCount ?></div>
                    <h3><?= htmlspecialchars($step['title']) ?></h3>
                  </div>
                </div>
                <p><?= htmlspecialchars($step['desc']) ?></p>
              </article>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($L['cards'])): ?>
        <div class="sl-mini-cards">
          <?php foreach ($L['cards'] as $card): ?>
          <a href="<?= front_url($card['url']) ?>" class="sl-mini-card">
            <img src="<?= htmlspecialchars($card['img']) ?>" alt="<?= htmlspecialchars($card['title']) ?>" loading="lazy">
            <div class="sl-mini-card-body">
              <h4><?= htmlspecialchars($card['title']) ?></h4>
              <p><?= htmlspecialchars($card['desc']) ?></p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="sl-cta-band">
          <h3>جاهز للبدء؟</h3>
          <p>ابدأ من الخطوة الأولى في خارطة الرحلة أو انتقل مباشرة إلى الخدمة.</p>
          <div class="sl-hero-actions justify-content-center">
            <a href="<?= front_url($L['cta_primary']['url']) ?>" class="sl-btn-primary">
              <i class="bi <?= htmlspecialchars($L['cta_primary']['icon']) ?>"></i>
              <?= htmlspecialchars($L['cta_primary']['label']) ?>
            </a>
            <a href="<?= front_url('index.php#faq') ?>" class="sl-btn-outline-light">
              <i class="bi bi-question-circle-fill"></i> أسئلة شائعة
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../layout/footer.php'; ?>
  <?php include __DIR__ . '/../layout/scripts.php'; ?>

  <script>
  (function () {
    var panels = document.querySelectorAll('[data-sl-panel]');
    var navBtns = document.querySelectorAll('[data-sl-step]');
    var total = panels.length;
    var progressBar = document.getElementById('slProgressBar');
    var progressFill = document.getElementById('slProgressFill');
    var progressLabel = document.getElementById('slProgressLabel');
    var roadmap = document.getElementById('slRoadmap');

    function setActive(index) {
      index = Math.max(0, Math.min(index, total - 1));
      navBtns.forEach(function (btn, i) {
        btn.classList.toggle('is-active', i === index);
        btn.classList.toggle('is-done', i < index);
      });
      panels.forEach(function (panel, i) {
        panel.classList.toggle('is-active', i === index);
      });
      if (progressFill) {
        progressFill.style.width = ((index + 1) / total * 100) + '%';
      }
      if (progressLabel) {
        progressLabel.textContent = 'الخطوة ' + (index + 1) + ' من ' + total;
      }
    }

    navBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var idx = parseInt(btn.getAttribute('data-sl-step'), 10);
        setActive(idx);
        var panel = document.getElementById('slStep' + idx);
        if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });

    if (roadmap && panels.length) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var idx = parseInt(entry.target.getAttribute('data-sl-panel'), 10);
            setActive(idx);
          }
        });
      }, { rootMargin: '-30% 0px -30% 0px', threshold: 0.2 });

      panels.forEach(function (panel) { observer.observe(panel); });

      var barObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (progressBar) progressBar.classList.toggle('is-visible', entry.isIntersecting);
        });
      }, { threshold: 0 });
      barObserver.observe(roadmap);
    }
  })();
  </script>
</body>
</html>
