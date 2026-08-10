<?php
$basePath = '../../';
$activePage = 'need-view';
$pageTitle = 'تفاصيل احتياج';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include $basePath . 'includes/layout/head.php'; ?>
  <style>
    .linked-module-card {
      display: flex; align-items: center; gap: 14px;
      border: 1px solid #e5e7eb; border-radius: 12px;
      padding: 14px 16px; background: #fafafa;
    }
    .linked-finance  { border-color: #bfdbfe; background: #eff6ff; }
    .linked-training { border-color: #bbf7d0; background: #f0fdf4; }
    .lm-icon { font-size: 26px; flex-shrink: 0; }
    .linked-finance  .lm-icon { color: #1d4ed8; }
    .linked-training .lm-icon { color: #15803d; }
    .lm-body { flex: 1; min-width: 0; }
    .lm-label  { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; font-weight: 700; }
    .lm-title  { font-size: 14px; font-weight: 700; color: #111827; }
    .lm-status { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .lm-btn    { flex-shrink: 0; white-space: nowrap; }

    /* ══ شريط البروسس — ستيبر أفقي نظيف ══ */
    .nv-flow{display:flex;align-items:flex-start;gap:0;overflow-x:auto;padding:10px 4px 6px;scrollbar-width:thin}
    .nv-step{flex:1 1 0;min-width:92px;display:flex;flex-direction:column;align-items:center;position:relative;text-align:center}
    .nv-step::before,.nv-step::after{content:"";position:absolute;top:19px;height:3px;background:#e5e7eb;z-index:0}
    .nv-step::before{inset-inline-start:0;width:50%}
    .nv-step::after{inset-inline-end:0;width:50%}
    .nv-step:first-child::before{display:none}
    .nv-step:last-child::after{display:none}
    .nv-step.done::before,.nv-step.done::after,.nv-step.current::before{background:#22c55e}
    .nv-dot{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.05rem;background:#f1f5f9;color:#94a3b8;border:2px solid #e5e7eb;position:relative;z-index:1}
    .nv-step.done .nv-dot{background:#22c55e;border-color:#22c55e;color:#fff}
    .nv-step.current .nv-dot{background:#fff;border-color:#3b82f6;color:#3b82f6;box-shadow:0 0 0 4px rgba(59,130,246,.15)}
    .nv-step-lbl{margin-top:8px;font-size:.72rem;font-weight:700;color:#94a3b8;line-height:1.3;max-width:104px}
    .nv-step.done .nv-step-lbl{color:#475569}
    .nv-step.current .nv-step-lbl{color:#1e293b;font-weight:800}
    .nv-now-tag{display:block;margin-top:3px;font-size:.6rem;font-weight:800;color:#3b82f6}
    .nv-exception{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:12px;font-size:.85rem;font-weight:800;margin-bottom:12px}

    /* ══ حقول ══ */
    .nv-section-title{font-size:.95rem;font-weight:800;color:#0f172a;margin:6px 0 2px;display:flex;align-items:center;gap:8px}
    .nv-section-title i{color:#17947B}
    .nv-field .nv-k{font-size:.72rem;color:#94a3b8;font-weight:700}
    .nv-field .nv-v{font-weight:600;color:#1f2937;word-break:break-word;white-space:pre-wrap}
    .nv-badge{display:inline-flex;align-items:center;padding:3px 12px;border-radius:20px;font-size:.8rem;font-weight:800}
    .nv-sector-chip{display:inline-block;background:#f0f9ff;color:#0369a1;border-radius:20px;padding:3px 11px;font-size:.78rem;font-weight:700;margin:0 0 4px 4px}

    /* ══ سجل الإجراءات (Timeline) ══ */
    .nv-timeline{position:relative;padding-inline-start:22px;margin-top:4px}
    .nv-timeline::before{content:'';position:absolute;inset-inline-start:6px;top:4px;bottom:4px;width:2px;background:#e5e7eb}
    .nv-tl-item{position:relative;padding:0 0 16px}
    .nv-tl-item::before{content:'';position:absolute;inset-inline-start:-22px;top:3px;width:12px;height:12px;border-radius:50%;background:#17947B;box-shadow:0 0 0 3px #fff,0 0 0 4px #e5e7eb}
    .nv-tl-action{font-weight:800;color:#0f172a;font-size:.9rem}
    .nv-tl-flow{font-size:.76rem;color:#64748b;margin-top:1px}
    .nv-tl-meta{font-size:.72rem;color:#94a3b8;margin-top:2px}
    .nv-tl-note{font-size:.8rem;color:#334155;background:#f8fafc;border:1px solid #eef2f5;border-radius:8px;padding:6px 10px;margin-top:5px}
  </style>
</head>
<body>
<?php include $basePath . 'includes/layout/header.php'; ?>
<main>
  <section class="services-hero"><div class="container"><h1 class="fw-bold mb-2">تفاصيل الاحتياج</h1></div></section>
  <section class="section pt-0">
    <div class="container">
      <div id="needViewMessage" class="alert d-none"></div>
      <div id="needViewCard" class="card border-0 shadow-sm mb-3"><div class="card-body" id="needViewBody">جاري التحميل...</div></div>
      <div class="d-flex flex-wrap gap-2" id="needViewActions"></div>
    </div>
  </section>
</main>
<?php include $basePath . 'includes/layout/footer.php'; ?>
<?php include $basePath . 'includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-platform.js?v=1.0"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/need-view.js?v=3.1"></script>
<script src="<?php echo $basePath; ?>assets/js/pages/needs-ai-fab.js?v=1.0"></script>
</body>
</html>
