<?php
require_once __DIR__ . '/../i18n/bootstrap.php';
require_once __DIR__ . '/faq-data.php';

if (!function_exists('front_url')) {
    require_once __DIR__ . '/../layout/paths.php';
}

$faqSections = landing_faq_sections();
$faqCategories = array_keys($faqSections);
?>

<style>
  .faq-section { background: linear-gradient(180deg, var(--c-soft) 0%, #fff 100%); }
  .faq-hub-intro { max-width: 680px; margin-inline: auto; }

  .faq-filters {
    display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
    margin-bottom: 32px;
  }
  .faq-filter-btn {
    padding: 9px 16px; border-radius: 999px; border: 1px solid rgba(23,148,123,.18);
    background: #fff; color: var(--c-body); font-size: .84rem; font-weight: 800;
    cursor: pointer; transition: all .22s;
    display: inline-flex; align-items: center; gap: 6px;
  }
  .faq-filter-btn i { font-size: .9rem; color: var(--c-primary); }
  .faq-filter-btn:hover { border-color: rgba(23,148,123,.35); color: var(--c-dark); }
  .faq-filter-btn.is-active {
    background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
    border-color: transparent; color: #fff; box-shadow: 0 6px 18px rgba(23,148,123,.28);
  }
  .faq-filter-btn.is-active i { color: #fff; }

  .faq-groups { display: flex; flex-direction: column; gap: 28px; }
  .faq-group { display: block; }
  .faq-group.is-hidden { display: none; }
  .faq-group-head {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 14px; padding-bottom: 12px;
    border-bottom: 2px solid rgba(23,148,123,.12);
  }
  .faq-group-head i {
    width: 42px; height: 42px; border-radius: 12px;
    background: var(--c-soft); color: var(--c-primary);
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
  }
  .faq-group-head h3 { font-size: 1.08rem; font-weight: 900; color: var(--c-dark); margin: 0; }
  .faq-group-head span { font-size: .82rem; color: var(--c-muted); font-weight: 600; }

  .faq-section .faq-list { display: flex; flex-direction: column; gap: 10px; }
  .faq-section .faq-item {
    background: #fff; border: 1px solid rgba(23,148,123,.12);
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 2px 12px rgba(6,40,36,.04);
    transition: box-shadow .28s, border-color .28s;
  }
  .faq-section .faq-item:hover { box-shadow: var(--shadow-sm); }
  .faq-section .faq-item.is-open {
    border-color: rgba(23,148,123,.28); box-shadow: var(--shadow-md);
  }
  .faq-section .faq-q {
    width: 100%; text-align: start; padding: 18px 20px;
    background: none; border: none; cursor: pointer;
    display: flex; align-items: flex-start; gap: 14px;
    font-weight: 800; font-size: .95rem; color: var(--c-dark);
    transition: color .2s;
  }
  .faq-section .faq-q:hover { color: var(--c-primary); }
  .faq-section .faq-q-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: var(--c-soft); color: var(--c-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; transition: background .25s, transform .25s;
  }
  .faq-section .faq-item.is-open .faq-q-icon {
    background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
    color: #fff; transform: scale(1.05);
  }
  .faq-section .faq-q-text { flex: 1; text-align: start; line-height: 1.55; padding-top: 8px; }
  .faq-section .faq-q-chevron {
    color: var(--c-primary); font-size: 1rem; flex-shrink: 0;
    margin-top: 10px; transition: transform .35s cubic-bezier(.22,.68,0,1.2);
  }
  .faq-section .faq-item.is-open .faq-q-chevron { transform: rotate(180deg); }
  .faq-section .faq-a-wrap {
    display: grid; grid-template-rows: 0fr;
    transition: grid-template-rows .35s cubic-bezier(.22,.68,0,1.1);
  }
  .faq-section .faq-item.is-open .faq-a-wrap { grid-template-rows: 1fr; }
  .faq-section .faq-a-inner { overflow: hidden; }
  .faq-section .faq-a {
    padding: 0 20px 18px 74px; font-size: .92rem;
    color: var(--c-body); line-height: 1.9;
    opacity: 0; transform: translateY(-6px);
    transition: opacity .3s ease .05s, transform .3s ease .05s;
  }
  .faq-section .faq-item.is-open .faq-a { opacity: 1; transform: none; }
  @media (max-width: 575.98px) { .faq-section .faq-a { padding-inline: 20px; } }

  .faq-footer-cta {
    margin-top: 36px; display: flex; flex-wrap: wrap; gap: 12px;
    align-items: center; justify-content: center;
  }
  .faq-footer-cta p {
    margin: 0; font-size: .9rem; color: var(--c-muted);
    font-weight: 600; flex: 1; min-width: 200px; text-align: center;
  }
</style>

<section class="section faq-section" id="faq">
  <div class="container">

    <div class="text-center mb-4" data-reveal>
      <span class="sec-badge"><i class="bi bi-question-circle-fill"></i> <?php echo htmlspecialchars(__('faq_badge')); ?></span>
      <h2 class="sec-title mt-2"><?php echo htmlspecialchars(__('faq_title')); ?></h2>
      <p class="sec-sub faq-hub-intro mt-2">
        <?php echo htmlspecialchars(__('faq_sub')); ?>
      </p>
    </div>

    <div class="faq-filters" data-reveal data-reveal-delay="1" role="tablist" aria-label="<?php echo htmlspecialchars(__('faq_badge')); ?>">
      <button type="button" class="faq-filter-btn is-active" data-faq-filter="all" role="tab" aria-selected="true">
        <i class="bi bi-grid-fill"></i> <?php echo htmlspecialchars(__('faq_filter_all')); ?>
      </button>
      <?php foreach ($faqSections as $key => $sec): ?>
      <button type="button" class="faq-filter-btn" data-faq-filter="<?= htmlspecialchars($key) ?>" role="tab" aria-selected="false">
        <i class="bi <?= htmlspecialchars($sec['icon']) ?>"></i>
        <?= htmlspecialchars($sec['label']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="faq-groups" id="faqGroups">
      <?php foreach ($faqSections as $key => $sec): ?>
      <div class="faq-group" data-faq-group="<?= htmlspecialchars($key) ?>" data-reveal>
        <div class="faq-group-head">
          <i class="bi <?= htmlspecialchars($sec['icon']) ?>"></i>
          <div>
            <h3><?= htmlspecialchars($sec['label']) ?></h3>
            <span><?= count($sec['items']) ?> <?php echo htmlspecialchars(__('faq_questions_count')); ?></span>
          </div>
        </div>
        <div class="faq-list">
          <?php foreach ($sec['items'] as $i => $faq): ?>
          <div class="faq-item<?= ($key === 'general' && $i === 0) ? ' is-open' : '' ?>">
            <button type="button" class="faq-q" aria-expanded="<?= ($key === 'general' && $i === 0) ? 'true' : 'false' ?>">
              <span class="faq-q-icon"><i class="bi <?= htmlspecialchars($faq['icon']) ?>"></i></span>
              <span class="faq-q-text"><?= htmlspecialchars($faq['q']) ?></span>
              <i class="bi bi-chevron-down faq-q-chevron"></i>
            </button>
            <div class="faq-a-wrap">
              <div class="faq-a-inner">
                <div class="faq-a"><?= htmlspecialchars($faq['a']) ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="faq-footer-cta" data-reveal>
      <p><?php echo htmlspecialchars(__('faq_cta_text')); ?></p>
      <a href="<?= front_url('user-guide.php') ?>" class="hero-btn-primary" style="display:inline-flex">
        <i class="bi bi-journal-text"></i> <?php echo htmlspecialchars(__('faq_cta_guide')); ?>
      </a>
      <a href="<?= front_url('index.php#contact') ?>" class="hero-btn-outline" style="display:inline-flex;color:var(--c-primary);border-color:rgba(23,148,123,.3)">
        <i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars(__('faq_cta_contact')); ?>
      </a>
    </div>

  </div>
</section>
