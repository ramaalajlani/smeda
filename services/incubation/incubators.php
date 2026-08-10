<?php
$basePath  = '../../';
$activePage = 'incubation';
require_once __DIR__ . '/../../includes/i18n/bootstrap.php';
global $siteTranslations, $siteLang, $siteIsRtl;
$siteTranslations = array_merge($siteTranslations, include __DIR__ . '/../../includes/i18n/incubators.php');
$t = fn(string $k) => __($k);
$pageTitle = __('page_title');

$govOptions = [
    ['ar' => 'دمشق', 'en' => 'Damascus'],
    ['ar' => 'ريف دمشق', 'en' => 'Rif Dimashq'],
    ['ar' => 'حلب', 'en' => 'Aleppo'],
    ['ar' => 'حمص', 'en' => 'Homs'],
    ['ar' => 'حماة', 'en' => 'Hama'],
    ['ar' => 'اللاذقية', 'en' => 'Latakia'],
    ['ar' => 'طرطوس', 'en' => 'Tartus'],
    ['ar' => 'درعا', 'en' => 'Daraa'],
    ['ar' => 'السويداء', 'en' => 'As-Suwayda'],
    ['ar' => 'القنيطرة', 'en' => 'Quneitra'],
    ['ar' => 'دير الزور', 'en' => 'Deir ez-Zor'],
    ['ar' => 'الرقة', 'en' => 'Raqqa'],
    ['ar' => 'الحسكة', 'en' => 'Al-Hasakah'],
    ['ar' => 'إدلب', 'en' => 'Idlib'],
];

$jsI18n = [
    'results' => $t('results'),
    'no_desc' => $t('no_desc'),
    'undetermined' => $t('undetermined'),
    'general' => $t('general'),
    'empty' => $t('empty'),
    'load_fail' => $t('load_fail'),
    'modal_loading' => $t('modal_loading'),
    'modal_fail' => $t('modal_fail'),
    'apply_inc' => $t('apply_inc'),
    'apply_now' => $t('apply_now'),
    'capacity' => $t('capacity'),
    'projects' => $t('projects'),
    'programs' => $t('programs'),
    'lbl_sector' => $t('lbl_sector'),
    'lbl_gov' => $t('lbl_gov'),
    'lbl_capacity' => $t('lbl_capacity'),
    'lbl_active_proj' => $t('lbl_active_proj'),
    'lbl_programs' => $t('lbl_programs'),
    'lbl_status' => $t('lbl_status'),
    'lbl_manager' => $t('lbl_manager'),
    'lbl_email' => $t('lbl_email'),
    'lbl_phone' => $t('lbl_phone'),
    'status_active' => $t('status_active'),
    'status_inactive' => $t('status_inactive'),
    'capacity_unit' => $t('capacity_unit'),
    'sector_label' => [
        'tech' => $t('sector_label_tech'),
        'industrial' => $t('sector_label_industrial'),
        'agricultural' => $t('sector_label_agricultural'),
        'services' => $t('sector_label_services'),
        'creative' => $t('sector_label_creative'),
    ],
];
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root {
      --c-dark: #062824; --c-deep: #0F4F47; --c-primary: #17947B; --c-accent: #06AA89;
      --c-soft: #f4faf8; --c-text: #1a3530; --c-body: #5f6b67; --c-muted: #8b9591;
      --c-border: rgba(23,148,123,.10); --ease: cubic-bezier(.22, 1, .36, 1);
    }
    * { box-sizing: border-box; }
    body {
      background: #fafcfb;
      color: var(--c-text);
      overflow-x: hidden;
    }
    a { text-decoration: none; color: inherit; }
    .pw { max-width: 1180px; margin: auto; padding: 0 20px 72px; }

    /* ── Hero — airy SSF feel ── */
    .inc-hero {
      position: relative; overflow: hidden;
      margin: 28px 0 36px; border-radius: 28px;
      min-height: 340px; display: flex; align-items: center;
      padding: 56px 40px; color: #fff;
      background: linear-gradient(145deg, #0a3d36 0%, var(--c-deep) 45%, #127a64 100%);
    }
    .inc-hero-bg {
      position: absolute; inset: 0;
      background: url('https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1400&q=85&fit=crop') center/cover no-repeat;
      transform: scale(1.06);
      animation: heroKen 18s ease-in-out infinite alternate;
    }
    .inc-hero-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(6,40,36,.88) 0%, rgba(15,79,71,.72) 55%, rgba(23,148,123,.45) 100%);
    }
    .inc-orb {
      position: absolute; border-radius: 50%; filter: blur(40px); opacity: .35;
      animation: floatOrb 8s ease-in-out infinite;
    }
    .inc-orb-1 { width: 220px; height: 220px; background: var(--c-accent); top: -40px; <?= $siteIsRtl ? 'left' : 'right' ?>: 8%; }
    .inc-orb-2 { width: 160px; height: 160px; background: #fff; bottom: -30px; <?= $siteIsRtl ? 'right' : 'left' ?>: 12%; animation-delay: -3s; }
    .inc-hero-inner { position: relative; z-index: 3; width: 100%; text-align: center; }
    .inc-top-row {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
    }
    .inc-back {
      display: inline-flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,.82); font-weight: 700; font-size: .88rem;
      transition: color .25s, transform .25s var(--ease);
    }
    .inc-back:hover { color: #fff; transform: translateX(<?= $siteIsRtl ? '4px' : '-4px' ?>); }
    .inc-hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.14); backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.22); border-radius: 999px;
      padding: 8px 18px; font-size: .84rem; font-weight: 800; margin-bottom: 16px;
      animation: fadeUp .8s var(--ease) .1s both;
    }
    .inc-hero h1 {
      font-size: clamp(1.55rem, 3.8vw, 2.35rem); font-weight: 900;
      margin-bottom: 14px; line-height: 1.35; letter-spacing: -.02em;
      animation: fadeUp .85s var(--ease) .18s both;
    }
    .inc-hero p {
      font-size: 1.02rem; color: rgba(255,255,255,.88); max-width: 580px;
      margin: 0 auto 32px; line-height: 1.9; font-weight: 500;
      animation: fadeUp .9s var(--ease) .26s both;
    }
    .hero-stats {
      display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;
      animation: fadeUp .95s var(--ease) .34s both;
    }
    .hero-stat {
      min-width: 120px; padding: 18px 22px; text-align: center;
      background: rgba(255,255,255,.10); backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.16); border-radius: 20px;
      transition: transform .35s var(--ease), background .35s;
    }
    .hero-stat:hover { transform: translateY(-4px); background: rgba(255,255,255,.16); }
    .hero-stat-val { font-size: 1.9rem; font-weight: 900; color: #fff; line-height: 1; }
    .hero-stat-lbl { font-size: .78rem; color: rgba(255,255,255,.78); font-weight: 600; margin-top: 6px; }

    /* ── Controls ── */
    .controls-wrap {
      animation: fadeUp .7s var(--ease) .4s both;
    }
    .sector-tabs {
      display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 16px;
      scrollbar-width: none;
    }
    .sector-tabs::-webkit-scrollbar { display: none; }
    .sector-tab {
      display: flex; align-items: center; gap: 6px;
      padding: 10px 18px; border-radius: 999px;
      border: 1px solid var(--c-border); background: #fff;
      font-size: .84rem; font-weight: 800; color: var(--c-muted);
      cursor: pointer; white-space: nowrap;
      transition: all .3s var(--ease);
    }
    .sector-tab:hover { border-color: rgba(23,148,123,.28); color: var(--c-primary); transform: translateY(-2px); }
    .sector-tab.active {
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; border-color: transparent;
      box-shadow: 0 8px 22px rgba(23,148,123,.22);
    }
    .filters-bar {
      background: #fff; border-radius: 20px; padding: 14px 18px;
      display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
      margin-bottom: 28px; border: 1px solid var(--c-border);
      box-shadow: 0 12px 36px rgba(6,40,36,.04);
    }
    .fi, .fs {
      padding: 12px 16px; border: 1px solid var(--c-border); border-radius: 14px;
      font-size: .9rem; color: var(--c-text); background: #fafcfb; flex: 1; min-width: 150px;
      transition: border-color .25s, box-shadow .25s, background .25s;
    }
    .fi:focus, .fs:focus {
      outline: none; border-color: var(--c-primary);
      background: #fff; box-shadow: 0 0 0 4px rgba(23,148,123,.10);
    }
    .results-count { font-size: .84rem; color: var(--c-primary); font-weight: 800; white-space: nowrap; }

    /* ── Cards ── */
    .inc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 22px; }
    .inc-card {
      background: #fff; border: 1px solid var(--c-border); border-radius: 22px;
      overflow: hidden; cursor: pointer;
      box-shadow: 0 8px 30px rgba(6,40,36,.04);
      transition: transform .45s var(--ease), box-shadow .45s var(--ease), border-color .35s;
      opacity: 0; transform: translateY(28px);
    }
    .inc-card.is-visible { opacity: 1; transform: none; }
    .inc-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 22px 50px rgba(6,40,36,.10);
      border-color: rgba(23,148,123,.22);
    }
    .inc-card-header { padding: 22px 22px 16px; border-bottom: 1px solid var(--c-border); position: relative; }
    .inc-card-badge {
      display: inline-flex; align-items: center; gap: 5px;
      background: var(--c-soft); color: var(--c-primary);
      border-radius: 999px; padding: 5px 12px;
      font-size: .76rem; font-weight: 800; margin-bottom: 10px;
    }
    .inc-card-name { font-size: 1.08rem; font-weight: 900; color: var(--c-dark); margin-bottom: 6px; }
    .inc-card-gov { font-size: .82rem; color: var(--c-muted); display: flex; align-items: center; gap: 5px; }
    .inc-status {
      position: absolute; top: 18px; <?= $siteIsRtl ? 'left' : 'right' ?>: 18px;
      width: 9px; height: 9px; border-radius: 50%;
      background: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.18);
    }
    .inc-status.inactive { background: #cbd5e1; box-shadow: none; }
    .inc-card-body { padding: 16px 22px; }
    .inc-card-desc {
      font-size: .88rem; color: var(--c-body); line-height: 1.7; margin-bottom: 16px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .inc-stats {
      display: flex; border: 1px solid var(--c-border); border-radius: 14px;
      overflow: hidden; background: #fafcfb;
    }
    .inc-stat {
      flex: 1; padding: 12px 8px; text-align: center;
      border-<?= $siteIsRtl ? 'left' : 'right' ?>: 1px solid var(--c-border);
    }
    .inc-stat:last-child { border: none; }
    .inc-stat-val { font-size: 1.12rem; font-weight: 900; color: var(--c-primary); }
    .inc-stat-lbl { font-size: .68rem; color: var(--c-muted); font-weight: 700; margin-top: 2px; }
    .inc-card-footer { padding: 14px 18px 18px; display: flex; gap: 10px; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      padding: 11px 16px; border-radius: 13px; border: none;
      font-size: .86rem; font-weight: 800; cursor: pointer;
      transition: all .3s var(--ease);
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--c-primary), var(--c-accent));
      color: #fff; flex: 1; box-shadow: 0 6px 18px rgba(23,148,123,.2);
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(23,148,123,.28); color: #fff; }
    .btn-outline { background: var(--c-soft); color: var(--c-primary); border: 1px solid var(--c-border); min-width: 46px; }
    .btn-outline:hover { background: #e6f7f2; }

    /* ── Modal ── */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(6,40,36,.45);
      backdrop-filter: blur(6px); z-index: 1050;
      display: none; align-items: center; justify-content: center; padding: 16px;
      opacity: 0; transition: opacity .35s var(--ease);
    }
    .modal-overlay.open { display: flex; opacity: 1; }
    .modal-box {
      background: #fff; border-radius: 24px; width: 100%; max-width: 680px;
      max-height: 90vh; overflow-y: auto;
      box-shadow: 0 28px 80px rgba(6,40,36,.18);
      transform: scale(.94) translateY(16px);
      transition: transform .4s var(--ease);
    }
    .modal-overlay.open .modal-box { transform: scale(1) translateY(0); }
    .modal-head {
      position: sticky; top: 0; background: rgba(255,255,255,.96);
      backdrop-filter: blur(10px);
      padding: 20px 24px; border-bottom: 1px solid var(--c-border);
      display: flex; align-items: center; justify-content: space-between; z-index: 1;
    }
    .modal-head h3 { font-size: 1.08rem; font-weight: 900; color: var(--c-dark); }
    .modal-close {
      background: var(--c-soft); border: none; font-size: 1rem;
      color: var(--c-muted); cursor: pointer; padding: 8px 11px; border-radius: 12px;
      transition: all .25s;
    }
    .modal-close:hover { background: #e6f7f2; color: var(--c-primary); }
    .modal-body { padding: 24px; }
    .detail-row {
      display: flex; justify-content: space-between; gap: 12px;
      padding: 11px 0; border-bottom: 1px dashed var(--c-border); font-size: .9rem;
    }
    .detail-row:last-child { border: none; }
    .detail-label { color: var(--c-muted); font-weight: 700; }
    .detail-val { font-weight: 800; color: var(--c-dark); text-align: <?= $siteIsRtl ? 'left' : 'right' ?>; }
    .modal-hero-box {
      background: linear-gradient(135deg, var(--c-soft), #e8f7f2);
      border: 1px solid var(--c-border); border-radius: 18px;
      padding: 22px; margin-bottom: 18px; text-align: center;
    }
    .modal-desc-box {
      background: #fafcfb; border-radius: 14px; padding: 16px;
      font-size: .9rem; line-height: 1.8; margin-bottom: 16px; color: var(--c-body);
    }

    .loading, .empty { text-align: center; padding: 72px 20px; color: var(--c-muted); }
    .empty i { font-size: 2.8rem; opacity: .2; display: block; margin-bottom: 12px; color: var(--c-primary); }
    .spin {
      display: inline-block; width: 38px; height: 38px;
      border: 3px solid rgba(23,148,123,.12); border-top-color: var(--c-primary);
      border-radius: 50%; animation: spin .75s linear infinite;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to { opacity: 1; transform: none; }
    }
    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-12px); }
      to { opacity: 1; transform: none; }
    }
    @keyframes heroKen {
      from { transform: scale(1.06); }
      to { transform: scale(1.12); }
    }
    @keyframes floatOrb {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(0, -18px); }
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation: none !important; transition: none !important; }
      .inc-card { opacity: 1; transform: none; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>

<main>
<div class="pw">

  <section class="inc-hero">
    <div class="inc-hero-bg" aria-hidden="true"></div>
    <div class="inc-hero-overlay" aria-hidden="true"></div>
    <div class="inc-orb inc-orb-1" aria-hidden="true"></div>
    <div class="inc-orb inc-orb-2" aria-hidden="true"></div>
    <div class="inc-hero-inner">
      <div class="inc-top-row">
        <a href="<?= front_url('services/landing.php?slug=incubation&lang=' . $siteLang) ?>" class="inc-back">
          <i class="bi bi-arrow-<?= $siteIsRtl ? 'right' : 'left' ?>"></i> <?= htmlspecialchars($t('back_guide')) ?>
        </a>
      </div>
      <span class="inc-hero-badge"><i class="bi bi-buildings-fill"></i> <?= htmlspecialchars($t('hero_badge')) ?></span>
      <h1><?= htmlspecialchars($t('hero_title')) ?></h1>
      <p><?= htmlspecialchars($t('hero_lead')) ?></p>
      <div class="hero-stats">
        <div class="hero-stat"><div class="hero-stat-val" id="hTotal" data-count="0">0</div><div class="hero-stat-lbl"><?= htmlspecialchars($t('stat_active')) ?></div></div>
        <div class="hero-stat"><div class="hero-stat-val" id="hProjects" data-count="0">0</div><div class="hero-stat-lbl"><?= htmlspecialchars($t('stat_projects')) ?></div></div>
        <div class="hero-stat"><div class="hero-stat-val" id="hSectors" data-count="0">0</div><div class="hero-stat-lbl"><?= htmlspecialchars($t('stat_sectors')) ?></div></div>
      </div>
    </div>
  </section>

  <div class="controls-wrap">
    <div class="sector-tabs" id="sectorTabs">
      <div class="sector-tab active" data-sector="" onclick="filterSector(this,'')"><i class="bi bi-grid-fill"></i> <?= htmlspecialchars($t('sector_all')) ?></div>
      <div class="sector-tab" data-sector="tech" onclick="filterSector(this,'tech')"><i class="bi bi-cpu-fill"></i> <?= htmlspecialchars($t('sector_tech')) ?></div>
      <div class="sector-tab" data-sector="industrial" onclick="filterSector(this,'industrial')"><i class="bi bi-gear-fill"></i> <?= htmlspecialchars($t('sector_industrial')) ?></div>
      <div class="sector-tab" data-sector="agricultural" onclick="filterSector(this,'agricultural')"><i class="bi bi-tree-fill"></i> <?= htmlspecialchars($t('sector_agricultural')) ?></div>
      <div class="sector-tab" data-sector="services" onclick="filterSector(this,'services')"><i class="bi bi-briefcase-fill"></i> <?= htmlspecialchars($t('sector_services')) ?></div>
      <div class="sector-tab" data-sector="creative" onclick="filterSector(this,'creative')"><i class="bi bi-palette-fill"></i> <?= htmlspecialchars($t('sector_creative')) ?></div>
    </div>

    <div class="filters-bar">
      <input type="text" id="fSearch" class="fi" placeholder="<?= htmlspecialchars($t('search_ph')) ?>" oninput="debounceFilter()">
      <select id="fGov" class="fs" onchange="renderFiltered()">
        <option value=""><?= htmlspecialchars($t('gov_all')) ?></option>
        <?php foreach ($govOptions as $g): ?>
        <option value="<?= htmlspecialchars($g['ar']) ?>"><?= htmlspecialchars($g[$siteLang]) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="results-count" id="resultsCount"></div>
    </div>
  </div>

  <div class="inc-grid" id="incGrid">
    <div style="grid-column:1/-1;text-align:center;padding:72px"><div class="spin"></div></div>
  </div>

</div>

<div class="modal-overlay" id="detailModal" role="dialog" aria-modal="true">
  <div class="modal-box">
    <div class="modal-head">
      <h3 id="detailTitle"><?= htmlspecialchars($t('modal_title')) ?></h3>
      <button class="modal-close" onclick="closeModal()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="detailBody"></div>
  </div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
window.INC_I18N = <?= json_encode($jsI18n, JSON_UNESCAPED_UNICODE) ?>;
window.INC_BASE = <?= json_encode($basePath) ?>;
window.INC_LANG = <?= json_encode($siteLang) ?>;

document.addEventListener('DOMContentLoaded', async () => {
  const I = window.INC_I18N;
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => {
    const t = window.AppAuth?.getToken?.();
    return t ? { Authorization: 'Bearer ' + t, Accept: 'application/json' } : { Accept: 'application/json' };
  };
  const E = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

  const SECTOR_ICON = { tech:'bi-cpu-fill', industrial:'bi-gear-fill', agricultural:'bi-tree-fill', services:'bi-briefcase-fill', creative:'bi-palette-fill' };

  function animateCount(el, target) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { el.textContent = target; return; }
    const dur = 1200; const start = performance.now();
    function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const ease = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * ease);
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  function revealCards() {
    document.querySelectorAll('.inc-card:not(.is-visible)').forEach((card, i) => {
      setTimeout(() => card.classList.add('is-visible'), i * 70);
    });
  }

  await window.AppBootstrapAuth.init({ requireAuth: false });

  let allIncs = [];
  let activeSector = '';

  const res = await fetch(BASE + '/incubators?per_page=100', { headers: H() });
  if (!res.ok) {
    document.getElementById('incGrid').innerHTML = '<div style="grid-column:1/-1" class="empty"><i class="bi bi-exclamation-circle"></i>' + E(I.load_fail) + '</div>';
    return;
  }
  const data = await res.json();
  allIncs = data.data || data || [];

  const sectors = new Set(allIncs.map(i => i.sector).filter(Boolean));
  const totalActive = allIncs.filter(i => i.status === 'active' || !i.status).length;
  const totalProjects = allIncs.reduce((s, i) => s + (i.active_projects_count || i.projects_count || 0), 0);
  const totalSectors = sectors.size || 6;

  animateCount(document.getElementById('hTotal'), totalActive);
  animateCount(document.getElementById('hProjects'), totalProjects);
  animateCount(document.getElementById('hSectors'), totalSectors);

  window.filterSector = function (el, sector) {
    document.querySelectorAll('.sector-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    activeSector = sector;
    renderFiltered();
  };

  window.renderFiltered = function () {
    const search = document.getElementById('fSearch').value.trim().toLowerCase();
    const gov = document.getElementById('fGov').value;
    const filtered = allIncs.filter(i => {
      if (activeSector && i.sector !== activeSector) return false;
      if (gov && i.governorate !== gov) return false;
      if (search && !i.name?.toLowerCase().includes(search) && !i.description?.toLowerCase().includes(search)) return false;
      return true;
    });
    document.getElementById('resultsCount').textContent = filtered.length + ' ' + I.results;
    if (!filtered.length) {
      document.getElementById('incGrid').innerHTML = '<div style="grid-column:1/-1" class="empty"><i class="bi bi-search"></i>' + E(I.empty) + '</div>';
      return;
    }
    document.getElementById('incGrid').innerHTML = filtered.map(inc => `
      <div class="inc-card" onclick="viewDetail(${inc.id})">
        <div class="inc-card-header">
          <div class="inc-status ${inc.status === 'inactive' ? 'inactive' : ''}"></div>
          <div class="inc-card-badge"><i class="bi ${SECTOR_ICON[inc.sector] || 'bi-building'}"></i> ${E(I.sector_label[inc.sector] || inc.sector || I.general)}</div>
          <div class="inc-card-name">${E(inc.name)}</div>
          <div class="inc-card-gov"><i class="bi bi-geo-alt-fill" style="color:var(--c-primary)"></i>${E(inc.governorate || I.undetermined)}</div>
        </div>
        <div class="inc-card-body">
          <div class="inc-card-desc">${E(inc.description || I.no_desc)}</div>
          <div class="inc-stats">
            <div class="inc-stat"><div class="inc-stat-val">${inc.capacity || '—'}</div><div class="inc-stat-lbl">${I.capacity}</div></div>
            <div class="inc-stat"><div class="inc-stat-val">${inc.active_projects_count || inc.projects_count || 0}</div><div class="inc-stat-lbl">${I.projects}</div></div>
            <div class="inc-stat"><div class="inc-stat-val">${inc.programs_count || '—'}</div><div class="inc-stat-lbl">${I.programs}</div></div>
          </div>
        </div>
        <div class="inc-card-footer">
          <a href="${INC_BASE}services/incubation/incubation-apply.php?inc=${inc.id}&lang=${INC_LANG}" class="btn btn-primary" onclick="event.stopPropagation()">
            <i class="bi bi-send-fill"></i> ${E(I.apply_now)}
          </a>
          <button class="btn btn-outline" onclick="event.stopPropagation();viewDetail(${inc.id})"><i class="bi bi-eye-fill"></i></button>
        </div>
      </div>`).join('');
    revealCards();
  };

  window.viewDetail = async function (id) {
    const modal = document.getElementById('detailModal');
    document.getElementById('detailTitle').textContent = I.modal_loading;
    document.getElementById('detailBody').innerHTML = '<div class="loading"><div class="spin"></div></div>';
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    const r = await fetch(BASE + '/incubators/' + id, { headers: H() });
    if (!r.ok) {
      document.getElementById('detailBody').innerHTML = '<div class="empty">' + E(I.modal_fail) + '</div>';
      return;
    }
    const inc = await r.json();
    document.getElementById('detailTitle').textContent = inc.name;

    document.getElementById('detailBody').innerHTML = `
      <div class="modal-hero-box">
        <div style="font-size:2.5rem;margin-bottom:8px"><i class="bi ${SECTOR_ICON[inc.sector] || 'bi-building'}" style="color:var(--c-primary)"></i></div>
        <div style="font-size:1.1rem;font-weight:900;color:var(--c-dark)">${E(inc.name)}</div>
        <div style="font-size:.85rem;color:var(--c-muted);margin-top:4px">${E(inc.governorate || '')} ${inc.address ? '— ' + E(inc.address) : ''}</div>
      </div>
      ${inc.description ? '<div class="modal-desc-box">' + E(inc.description) + '</div>' : ''}
      <div style="border:1px solid var(--c-border);border-radius:14px;overflow:hidden;margin-bottom:18px">
        ${[
          [I.lbl_sector, I.sector_label[inc.sector] || inc.sector || '—'],
          [I.lbl_gov, inc.governorate || '—'],
          [I.lbl_capacity, inc.capacity ? inc.capacity + ' ' + I.capacity_unit : '—'],
          [I.lbl_active_proj, inc.active_projects_count || inc.projects_count || 0],
          [I.lbl_programs, inc.programs_count || '—'],
          [I.lbl_status, inc.status === 'active' || !inc.status ? I.status_active : I.status_inactive],
          [I.lbl_manager, inc.manager?.name || '—'],
          [I.lbl_email, inc.email || '—'],
          [I.lbl_phone, inc.phone || '—'],
        ].map(([l, v]) => '<div class="detail-row"><div class="detail-label">' + l + '</div><div class="detail-val">' + E(v) + '</div></div>').join('')}
      </div>
      <a href="${INC_BASE}services/incubation/incubation-apply.php?inc=${inc.id}&lang=${INC_LANG}" class="btn btn-primary" style="width:100%;padding:14px">
        <i class="bi bi-send-fill"></i> ${E(I.apply_inc)}
      </a>`;
  };

  window.closeModal = function () {
    document.getElementById('detailModal').classList.remove('open');
    document.body.style.overflow = '';
  };
  document.getElementById('detailModal').addEventListener('click', e => {
    if (e.target.id === 'detailModal') closeModal();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  let debT;
  window.debounceFilter = () => { clearTimeout(debT); debT = setTimeout(renderFiltered, 280); };

  renderFiltered();
});
</script>
</body>
</html>
