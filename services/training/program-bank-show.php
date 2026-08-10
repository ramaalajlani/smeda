<?php
$basePath  = '../../';
$pageTitle = 'عرض البرنامج التدريبي';
$activePage= 'program-bank-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root{--c:#1a6b5a;--ca:#10b981;--cs:#e8f8f3;--cb:rgba(26,107,90,.12);--ct:#15312a;--cm:#6b7280;--sh:0 8px 24px rgba(15,60,50,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e7f6f1);min-height:100vh;}
    .pw{width:100%;max-width:1200px;margin:0 auto;padding:0 0 28px;}
    @media(max-width:767px){
      .prog-hero{padding:18px 14px;border-radius:16px;}
      .prog-title{font-size:1.2rem;}
      .hero-actions{width:100%;}
      .btn-act{flex:1;justify-content:center;min-height:44px;}
      .modal-box{width:min(94vw,480px);max-height:90vh;overflow:auto;padding:18px 14px;}
      .fld input,.fld select,.fld textarea{min-height:44px;font-size:16px;width:100%;}
    }

    /* Hero bar */
    .prog-hero{background:linear-gradient(135deg,#0d4d3f,var(--c),var(--ca));color:#fff;border-radius:22px;padding:24px 28px;margin-bottom:20px;position:relative;overflow:hidden;}
    .prog-hero::before{content:"";position:absolute;width:260px;height:260px;border-radius:50%;top:-130px;left:-60px;background:radial-gradient(circle,rgba(255,255,255,.12),transparent 70%);}
    .prog-title{font-size:1.5rem;font-weight:800;margin:0 0 6px;}
    .prog-meta{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-top:10px;font-size:.83rem;opacity:.88;}
    .prog-meta span{display:flex;align-items:center;gap:5px;}
    .hero-actions{position:absolute;top:20px;left:24px;display:flex;gap:8px;flex-wrap:wrap;}
    @media(max-width:600px){.hero-actions{position:static;margin-top:12px;flex-wrap:wrap;}}
    .btn-act{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:10px;font-weight:700;font-size:.82rem;border:none;cursor:pointer;text-decoration:none;transition:.2s;}
    .btn-edit{background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.4);}
    .btn-edit:hover{background:rgba(255,255,255,.3);color:#fff;}
    .btn-green{background:#dcfce7;color:#15803d;}
    .btn-amber{background:#fef3c7;color:#92400e;}
    .btn-red{background:#fee2e2;color:#991b1b;}
    .btn-blue{background:#e0f2fe;color:#0369a1;}
    .btn-course{background:#fff;color:var(--c);}

    /* Badges */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border-radius:20px;font-size:.76rem;font-weight:700;}
    .b-draft{background:rgba(255,255,255,.2);color:#fff;}
    .b-tech{background:#fef3c7;color:#92400e;}
    .b-admin{background:#fff7ed;color:#9a3412;}
    .b-approved{background:#dcfce7;color:#15803d;}
    .b-suspended{background:#fee2e2;color:#991b1b;}
    .b-archived{background:#f1f5f9;color:#334155;}

    /* Cards */
    .card{background:#fff;border:1px solid var(--cb);border-radius:20px;box-shadow:var(--sh);margin-bottom:18px;overflow:hidden;}
    .card-hd{padding:14px 20px;border-bottom:1px solid var(--cb);display:flex;align-items:center;gap:10px;}
    .card-hd i{color:var(--c);font-size:1.1rem;}
    .card-hd h3{margin:0;font-size:.95rem;font-weight:800;color:var(--ct);}
    .card-body{padding:18px 20px;}

    /* Info grid */
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;}
    .info-item{}
    .info-lbl{font-size:.72rem;font-weight:700;color:var(--cm);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
    .info-val{font-size:.9rem;font-weight:700;color:var(--ct);}

    /* Tabs */
    .tabs{display:flex;gap:4px;border-bottom:2px solid var(--cb);overflow-x:auto;}
    .tab{padding:10px 18px;font-size:.85rem;font-weight:700;color:var(--cm);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:.15s;}
    .tab.active{color:var(--c);border-bottom-color:var(--c);}
    .tab-panel{display:none;padding:18px 20px;}
    .tab-panel.active{display:block;}

    /* Module cards */
    .mod-card{background:#f8fdf9;border:1.5px solid var(--cb);border-radius:14px;padding:14px;margin-bottom:10px;}
    .mod-num{display:inline-flex;width:26px;height:26px;border-radius:8px;background:var(--c);color:#fff;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;margin-left:8px;}
    .mod-title{font-weight:800;font-size:.9rem;color:var(--ct);display:inline;}
    .mod-meta{display:flex;gap:16px;margin-top:8px;flex-wrap:wrap;}
    .mod-meta span{font-size:.78rem;color:var(--cm);display:flex;align-items:center;gap:4px;}
    .mod-section{margin-top:10px;padding-top:10px;border-top:1px solid #e8f4ef;}
    .mod-section-title{font-size:.75rem;font-weight:700;color:var(--c);margin-bottom:4px;}
    .mod-section p{font-size:.84rem;color:var(--ct);margin:0;}

    /* Outcomes */
    .out-item{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #f0f4f8;}
    .out-item:last-child{border-bottom:none;}
    .out-num{width:28px;height:28px;border-radius:8px;background:var(--cs);color:var(--c);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem;flex-shrink:0;}
    .out-info .out-title{font-weight:700;font-size:.88rem;color:var(--ct);}
    .out-info .out-desc{font-size:.8rem;color:var(--cm);margin-top:2px;}

    /* Timeline */
    .tl-item{display:flex;gap:12px;padding:8px 0;border-bottom:1px solid #f0f4f8;}
    .tl-item:last-child{border-bottom:none;}
    .tl-dot{width:10px;height:10px;border-radius:50%;background:var(--c);margin-top:5px;flex-shrink:0;}
    .tl-content{flex:1;}
    .tl-action{font-size:.85rem;font-weight:700;color:var(--ct);}
    .tl-meta{font-size:.75rem;color:var(--cm);margin-top:2px;}

    /* Service links */
    .svc-grid{display:flex;gap:8px;flex-wrap:wrap;}
    .svc-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;background:var(--cs);border:1.5px solid var(--cb);color:var(--c);font-size:.8rem;font-weight:700;}

    /* Execution rows */
    .exec-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0f4f8;}
    .exec-row:last-child{border-bottom:none;}
    .exec-icon{width:38px;height:38px;border-radius:10px;background:var(--cs);display:flex;align-items:center;justify-content:center;color:var(--c);}

    /* Workflow buttons */
    .wf-bar{display:flex;gap:8px;flex-wrap:wrap;padding:16px 20px;background:var(--cs);border-top:1px solid var(--cb);}

    /* 2-col layout */
    .col2{display:grid;grid-template-columns:2fr 1fr;gap:18px;}
    @media(max-width:860px){.col2{grid-template-columns:1fr;}}

    /* Create course modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
    .modal-box{background:#fff;border-radius:20px;padding:24px;max-width:480px;width:92%;max-height:90vh;overflow-y:auto;}
    .modal-title{font-size:1.05rem;font-weight:800;color:var(--ct);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .fld{display:flex;flex-direction:column;gap:5px;margin-bottom:12px;}
    .fld label{font-size:.78rem;font-weight:700;color:var(--ct);}
    .fld input,.fld select{padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:11px;font-size:.88rem;font-family:inherit;}
    .fld input:focus,.fld select:focus{border-color:var(--c);outline:none;}

    #toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 22px;border-radius:14px;font-weight:700;font-size:.88rem;z-index:9999;display:none;box-shadow:0 8px 24px rgba(0,0,0,.18);}
    .toast-ok{background:#dcfce7;color:#15803d;}
    .toast-err{background:#fee2e2;color:#991b1b;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/program-bank-shell-open.php'; ?>

<div class="pw">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:.83rem;color:var(--cm);flex-wrap:wrap">
    <a href="program-bank-dashboard.php" style="color:var(--c);text-decoration:none;font-weight:600">بنك البرامج</a>
    <i class="bi bi-chevron-left"></i>
    <a href="program-bank-list.php" style="color:var(--c);text-decoration:none;font-weight:600">القائمة</a>
    <i class="bi bi-chevron-left"></i>
    <span id="bcName">تحميل...</span>
  </div>

  <!-- Loading skeleton -->
  <div id="skeleton">
    <div style="height:130px;background:linear-gradient(90deg,#c8ede3 25%,#b8e8d8 50%,#c8ede3 75%);background-size:200%;animation:skel 1.4s infinite;border-radius:22px;margin-bottom:20px;"></div>
    <div style="height:200px;background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);background-size:200%;animation:skel 1.4s infinite;border-radius:20px;"></div>
    @keyframes skel{0%{background-position:200% 0}100%{background-position:-200% 0}}
  </div>
  <style>@keyframes skel{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>

  <!-- Content (hidden until loaded) -->
  <div id="content" style="display:none">

    <!-- Hero -->
    <div class="prog-hero">
      <div class="prog-title" id="heroTitle">—</div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:4px;">
        <code style="background:rgba(255,255,255,.2);padding:2px 8px;border-radius:6px;font-size:.83rem" id="heroCode">—</code>
        <span id="heroBadge"></span>
      </div>
      <div class="prog-meta" id="heroMeta"></div>
      <div class="hero-actions" id="heroActions"></div>
    </div>

    <div class="col2">
      <div>
        <!-- Tabs card -->
        <div class="card">
          <div class="tabs" id="tabs">
            <div class="tab active" data-tab="info">المعلومات</div>
            <div class="tab" data-tab="modules">المحاور</div>
            <div class="tab" data-tab="outcomes">المخرجات</div>
            <div class="tab" data-tab="links">ربط بالخدمات</div>
            <div class="tab" data-tab="executions">الدورات المنفذة</div>
          </div>

          <!-- Info -->
          <div class="tab-panel active" id="tab-info">
            <div class="info-grid" id="infoGrid"></div>
            <div style="margin-top:16px" id="infoDesc"></div>
          </div>

          <!-- Modules -->
          <div class="tab-panel" id="tab-modules">
            <div id="modulesList"></div>
          </div>

          <!-- Outcomes -->
          <div class="tab-panel" id="tab-outcomes">
            <div id="outcomesList"></div>
          </div>

          <!-- Service Links -->
          <div class="tab-panel" id="tab-links">
            <div class="svc-grid" id="linksList"></div>
          </div>

          <!-- Executions -->
          <div class="tab-panel" id="tab-executions">
            <div id="execList"></div>
          </div>
        </div>
      </div>

      <!-- Sidebar cards -->
      <div>

        <!-- Workflow -->
        <div class="card">
          <div class="card-hd"><i class="bi bi-diagram-3"></i><h3>سير الاعتماد</h3></div>
          <div id="wfActions" class="wf-bar"></div>
          <div class="card-body">
            <div id="approvalLog"></div>
          </div>
        </div>

        <!-- Quick stats -->
        <div class="card">
          <div class="card-hd"><i class="bi bi-bar-chart"></i><h3>إحصائيات</h3></div>
          <div class="card-body">
            <div class="info-grid" id="quickStats" style="grid-template-columns:1fr 1fr;gap:10px;"></div>
          </div>
        </div>

      </div>
    </div>
  </div>

</div><!-- .pw -->

<!-- Notes Modal (replaces prompt()) -->
<div class="modal-overlay" id="notesModal">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-title"><i class="bi bi-chat-text" style="color:var(--c)"></i> ملاحظات الإجراء</div>
    <div class="fld">
      <label>الملاحظات (اختياري)</label>
      <textarea id="notesInput" rows="3" style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:11px;font-size:.88rem;font-family:inherit;width:100%;resize:vertical;" placeholder="أضف ملاحظة للإجراء..."></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:4px;flex-wrap:wrap">
      <button onclick="closeNotesModal()" style="padding:10px 18px;border-radius:10px;background:#f1f5f9;color:#334155;border:none;font-weight:700;cursor:pointer;min-height:44px">إلغاء</button>
      <button onclick="submitTransitionWithNotes()" style="padding:10px 18px;border-radius:10px;background:var(--c);color:#fff;border:none;font-weight:700;cursor:pointer;min-height:44px"><i class="bi bi-check-lg"></i> تأكيد</button>
    </div>
  </div>
</div>

<!-- Create Course Modal -->
<div class="modal-overlay" id="courseModal">
  <div class="modal-box">
    <div class="modal-title"><i class="bi bi-calendar-plus" style="color:var(--c)"></i> إنشاء دورة من البرنامج</div>
    <div class="fld"><label>تاريخ البداية *</label><input type="date" id="cm-start"></div>
    <div class="fld"><label>تاريخ النهاية *</label><input type="date" id="cm-end"></div>
    <div class="fld"><label>مركز التدريب *</label>
      <select id="cm-center"><option value="">-- اختر --</option></select>
    </div>
    <div class="fld"><label>المحافظة</label><input type="text" id="cm-gov" placeholder="المحافظة"></div>
    <div class="fld"><label>الموقع / المكان</label><input type="text" id="cm-loc" placeholder="العنوان التفصيلي"></div>
    <div class="fld"><label>الطاقة الاستيعابية</label><input type="number" id="cm-cap" value="30" min="1"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:4px;flex-wrap:wrap">
      <button onclick="document.getElementById('courseModal').style.display='none'" style="padding:10px 18px;border-radius:10px;background:#f1f5f9;color:#334155;border:none;font-weight:700;cursor:pointer;min-height:44px">إلغاء</button>
      <button onclick="createCourse()" style="padding:10px 18px;border-radius:10px;background:var(--c);color:#fff;border:none;font-weight:700;cursor:pointer;min-height:44px"><i class="bi bi-check-lg"></i> إنشاء</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<?php include __DIR__ . '/../../includes/layout/program-bank-shell-close.php'; ?>
<script>
const API = window.APP_CONFIG?.API_BASE_URL;
const urlId = new URLSearchParams(location.search).get('id');
if(!urlId) location.href='program-bank-list.php';

let PROGRAM = null;

const STATUS_BADGE = {
  draft:                   ['b-draft','مسودة'],
  under_technical_review:  ['b-tech','مراجعة فنية'],
  under_admin_review:      ['b-admin','مراجعة إدارية'],
  approved:                ['b-approved','✓ معتمد'],
  suspended:               ['b-suspended','موقوف'],
  archived:                ['b-archived','مؤرشف'],
};

const TYPE_LABELS = {entrepreneurial:'ريادي',financial:'مالي',marketing:'تسويقي',technical:'فني',digital:'رقمي',administrative:'إداري',agricultural:'زراعي',craft:'حرفي',other:'أخرى'};
const LEVEL_LABELS = {beginner:'مبتدئ',intermediate:'متوسط',advanced:'متقدم'};
const DELIVERY_LABELS = {in_person:'حضوري',online:'أونلاين',blended:'مدمج'};

// Tabs (initialized in DOMContentLoaded)

async function loadProgram() {
  try {
    const r = await fetch(API+'/program-bank/'+urlId, {headers:{Authorization:'Bearer '+AppAuth.getToken()}});
    if(!r.ok) throw new Error();
    const { data } = await r.json();
    PROGRAM = data;
    render(data);
  } catch(e){
    document.getElementById('skeleton').innerHTML='<div style="text-align:center;padding:40px;color:#ef4444">فشل تحميل البيانات</div>';
  }
}

function render(p) {
  document.getElementById('skeleton').style.display='none';
  document.getElementById('content').style.display='block';
  document.getElementById('bcName').textContent = p.display_name||p.name;

  // Hero
  document.getElementById('heroTitle').textContent = p.display_name||p.name;
  document.getElementById('heroCode').textContent = p.code;
  const [bc,bl] = STATUS_BADGE[p.bank_status]||['b-draft','—'];
  document.getElementById('heroBadge').innerHTML = `<span class="badge ${bc}">${bl}</span>`;
  document.getElementById('heroMeta').innerHTML = [
    p.type         ? `<span><i class="bi bi-tag"></i> ${TYPE_LABELS[p.type]||p.type}</span>` : '',
    p.level        ? `<span><i class="bi bi-bar-chart-steps"></i> ${LEVEL_LABELS[p.level]||p.level}</span>` : '',
    p.delivery_mode? `<span><i class="bi bi-geo"></i> ${DELIVERY_LABELS[p.delivery_mode]||p.delivery_mode}</span>` : '',
    p.suggested_hours ? `<span><i class="bi bi-clock"></i> ${p.suggested_hours} ساعة</span>` : '',
    p.sector       ? `<span><i class="bi bi-building"></i> ${p.sector}</span>` : '',
    p.creator      ? `<span><i class="bi bi-person"></i> ${p.creator}</span>` : '',
  ].filter(Boolean).join('');

  // Hero actions
  const actions = [];
  if(['draft','under_technical_review'].includes(p.bank_status))
    actions.push(`<a href="program-bank-form.php?id=${p.id}" class="btn-act btn-edit"><i class="bi bi-pencil"></i> تعديل</a>`);
  if(p.bank_status==='approved')
    actions.push(`<button onclick="openCourseModal()" class="btn-act btn-course"><i class="bi bi-calendar-plus"></i> إنشاء دورة</button>`);
  actions.push(`<button onclick="duplicateProgram()" class="btn-act btn-edit"><i class="bi bi-copy"></i> نسخ</button>`);
  document.getElementById('heroActions').innerHTML = actions.join('');

  // Info grid
  document.getElementById('infoGrid').innerHTML = [
    ['القطاع', p.sector||'—'],
    ['الفئة المستهدفة', p.target_audience||'—'],
    ['طريقة التقديم', p.delivery_mode?DELIVERY_LABELS[p.delivery_mode]:('—')],
    ['الساعات المقترحة', p.suggested_hours??'—'],
    ['عدد الجلسات', p.suggested_sessions??'—'],
    ['نسبة الحضور الدنيا', p.min_attendance_percent!=null?p.min_attendance_percent+'%':'—'],
    ['درجة النجاح', p.passing_score!=null?p.passing_score+' / 100':'—'],
    ['يمنح شهادة', p.grants_certificate?'✓ نعم':'لا'],
    ['اختبار نهائي', p.requires_final_exam?'✓ نعم':'لا'],
    ['مشروع تطبيقي', p.requires_project?'✓ نعم':'لا'],
    ['تاريخ الاعتماد', p.approved_at||'—'],
    ['المعتمد بواسطة', p.approver||'—'],
  ].map(([l,v])=>`<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${v}</div></div>`).join('');

  if(p.description||p.prerequisites||p.outcomes_summary) {
    let html='';
    if(p.description)       html+=`<div style="margin-bottom:12px"><div class="info-lbl" style="margin-bottom:6px">الوصف</div><div style="font-size:.88rem;color:var(--ct);line-height:1.6">${nl2br(p.description)}</div></div>`;
    if(p.prerequisites)     html+=`<div style="margin-bottom:12px"><div class="info-lbl" style="margin-bottom:6px">المتطلبات المسبقة</div><div style="font-size:.85rem;color:var(--ct)">${nl2br(p.prerequisites)}</div></div>`;
    if(p.outcomes_summary)  html+=`<div><div class="info-lbl" style="margin-bottom:6px">ملخص المخرجات</div><div style="font-size:.85rem;color:var(--ct)">${nl2br(p.outcomes_summary)}</div></div>`;
    document.getElementById('infoDesc').innerHTML=html;
  }

  // Modules
  const ml = document.getElementById('modulesList');
  if(!p.modules||!p.modules.length){ ml.innerHTML='<p style="color:var(--cm);font-size:.85rem">لا توجد محاور</p>'; }
  else ml.innerHTML = p.modules.map((m,i)=>`
    <div class="mod-card">
      <div><span class="mod-num">${i+1}</span><span class="mod-title">${escHtml(m.title)}</span></div>
      <div class="mod-meta">
        ${m.hours?`<span><i class="bi bi-clock"></i> ${m.hours} ساعات</span>`:''}
        ${m.evaluation_method?`<span><i class="bi bi-check2-square"></i> ${m.evaluation_method}</span>`:''}
      </div>
      ${m.objectives?`<div class="mod-section"><div class="mod-section-title">الأهداف</div><p>${nl2br(m.objectives)}</p></div>`:''}
      ${m.activities?`<div class="mod-section"><div class="mod-section-title">الأنشطة</div><p>${nl2br(m.activities)}</p></div>`:''}
      ${m.required_tools?`<div class="mod-section"><div class="mod-section-title">الأدوات</div><p>${nl2br(m.required_tools)}</p></div>`:''}
    </div>
  `).join('');

  // Outcomes
  const ol = document.getElementById('outcomesList');
  if(!p.outcomes||!p.outcomes.length){ ol.innerHTML='<p style="color:var(--cm);font-size:.85rem">لا توجد مخرجات</p>'; }
  else ol.innerHTML = p.outcomes.map((o,i)=>`
    <div class="out-item">
      <div class="out-num">${i+1}</div>
      <div class="out-info">
        <div class="out-title">${escHtml(o.title)}</div>
        ${o.description?`<div class="out-desc">${escHtml(o.description)}</div>`:''}
      </div>
    </div>
  `).join('');

  // Service Links
  const ll = document.getElementById('linksList');
  if(!p.service_links||!p.service_links.length){ ll.innerHTML='<p style="color:var(--cm);font-size:.85rem">لا توجد روابط خدمات</p>'; }
  else ll.innerHTML = p.service_links.map(l=>`<div class="svc-chip"><i class="bi bi-link-45deg"></i>${l.service_label}</div>`).join('');

  // Executions
  const el = document.getElementById('execList');
  if(!p.executions||!p.executions.length){ el.innerHTML='<p style="color:var(--cm);font-size:.85rem">لم يتم تنفيذ دورات من هذا البرنامج بعد</p>'; }
  else el.innerHTML = p.executions.map(e=>`
    <div class="exec-row">
      <div class="exec-icon"><i class="bi bi-calendar-event"></i></div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:.87rem">${e.course_title||'دورة #'+e.course_id}</div>
        <div style="font-size:.76rem;color:var(--cm)">${e.start_date||''} — ${e.status||''}</div>
      </div>
    </div>
  `).join('');

  // Workflow actions
  renderWorkflowActions(p.bank_status);

  // Approval log
  const al = document.getElementById('approvalLog');
  if(!p.approval_logs||!p.approval_logs.length){ al.innerHTML='<p style="color:var(--cm);font-size:.8rem">لا يوجد سجل</p>'; }
  else al.innerHTML = p.approval_logs.map(l=>`
    <div class="tl-item">
      <div class="tl-dot"></div>
      <div class="tl-content">
        <div class="tl-action">${l.action||''} ${l.to_status?'→ '+l.to_status:''}</div>
        <div class="tl-meta">${l.user||''} • ${l.date?new Date(l.date).toLocaleDateString('ar-SY'):''}</div>
        ${l.notes?`<div style="font-size:.78rem;color:var(--ct);margin-top:2px">${l.notes}</div>`:''}
      </div>
    </div>
  `).join('');

  // Quick stats
  document.getElementById('quickStats').innerHTML = [
    ['المحاور','bi-collection',p.modules_count??0],
    ['المخرجات','bi-list-check',p.outcomes_count??0],
    ['الدورات','bi-calendar-check',p.executions_count??0],
  ].map(([l,ic,v])=>`<div class="info-item"><div class="info-lbl"><i class="bi ${ic}"></i> ${l}</div><div class="info-val" style="font-size:1.5rem">${v}</div></div>`).join('');
}

function renderWorkflowActions(status) {
  const ACTIONS = {
    draft:                   [['submit','إرسال للمراجعة الفنية','btn-green','send']],
    under_technical_review:  [['technical_approve','اعتماد فني','btn-green','check-lg'],['technical_reject','إعادة للمسودة','btn-amber','arrow-return-right']],
    under_admin_review:      [['admin_approve','اعتماد إداري','btn-green','check-circle'],['admin_reject','إعادة للمراجعة الفنية','btn-amber','arrow-return-right']],
    approved:                [['suspend','إيقاف','btn-amber','pause-circle'],['archive','أرشفة','btn-red','archive']],
    suspended:               [['reactivate','إعادة تفعيل','btn-green','play-circle'],['archive','أرشفة','btn-red','archive']],
  };
  const acts = ACTIONS[status] || [];
  document.getElementById('wfActions').innerHTML = acts.length
    ? acts.map(([action,lbl,cls,icon])=>`<button class="btn-act ${cls}" onclick="doTransition('${action}')"><i class="bi bi-${icon}"></i> ${lbl}</button>`).join('')
    : '<span style="font-size:.82rem;color:var(--cm)">لا توجد إجراءات متاحة</span>';
}

let _pendingAction = null;

function doTransition(action) {
  if (['technical_reject','admin_reject','suspend'].includes(action)) {
    _pendingAction = action;
    document.getElementById('notesInput').value = '';
    document.getElementById('notesModal').style.display = 'flex';
    return;
  }
  _execTransition(action, null);
}

function closeNotesModal() {
  document.getElementById('notesModal').style.display = 'none';
  _pendingAction = null;
}

async function submitTransitionWithNotes() {
  const notes = document.getElementById('notesInput').value.trim() || null;
  const action = _pendingAction;
  closeNotesModal();
  await _execTransition(action, notes);
}

async function _execTransition(action, notes) {
  try {
    const r = await fetch(API+'/program-bank/'+urlId+'/transition', {
      method:'POST',
      headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
      body:JSON.stringify({ action, notes })
    });
    const body = await r.json();
    if(!r.ok){ showToast(body.message||'فشل الإجراء','err'); return; }
    showToast('تم تنفيذ الإجراء بنجاح','ok');
    setTimeout(()=>loadProgram(), 900);
  } catch(e){ showToast('خطأ في الاتصال','err'); }
}

async function duplicateProgram() {
  if(!confirm('هل تريد نسخ هذا البرنامج؟')) return;
  try {
    const r = await fetch(API+'/program-bank/'+urlId+'/duplicate',{
      method:'POST',headers:{Authorization:'Bearer '+AppAuth.getToken()}
    });
    const body = await r.json();
    if(!r.ok){ showToast(body.message||'فشل النسخ','err'); return; }
    showToast('تم نسخ البرنامج','ok');
    setTimeout(()=>{ location.href='program-bank-show.php?id='+body.data.id; },1000);
  } catch(e){ showToast('خطأ','err'); }
}

async function openCourseModal() {
  document.getElementById('courseModal').style.display='flex';
  // Load training centers
  try {
    const r = await fetch(API+'/training-centers?per_page=100',{headers:{Authorization:'Bearer '+AppAuth.getToken()}});
    const body = await r.json();
    const centers = body.data||body||[];
    const sel = document.getElementById('cm-center');
    (Array.isArray(centers)?centers:(centers.data||[])).forEach(c=>{
      sel.innerHTML+=`<option value="${c.id}">${c.name}</option>`;
    });
  } catch(e){}
}

async function createCourse() {
  const start = document.getElementById('cm-start').value;
  const end   = document.getElementById('cm-end').value;
  const center = document.getElementById('cm-center').value;
  if(!start||!end||!center){ showToast('يرجى تعبئة الحقول المطلوبة','err'); return; }
  try {
    const r = await fetch(API+'/program-bank/'+urlId+'/create-course',{
      method:'POST',
      headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
      body:JSON.stringify({
        start_date:start, end_date:end, training_center_id:center,
        governorate:document.getElementById('cm-gov').value,
        location:document.getElementById('cm-loc').value,
        capacity:parseInt(document.getElementById('cm-cap').value)||30
      })
    });
    const body = await r.json();
    if(!r.ok){ showToast(body.message||'فشل الإنشاء','err'); return; }
    document.getElementById('courseModal').style.display='none';
    showToast('تم إنشاء الدورة بنجاح','ok');
    setTimeout(()=>loadProgram(),1000);
  } catch(e){ showToast('خطأ','err'); }
}

function showToast(msg,type='ok'){
  const t=document.getElementById('toast');
  t.className=type==='ok'?'toast-ok':'toast-err';
  t.textContent=msg;
  t.style.display='block';
  setTimeout(()=>{t.style.display='none';},3000);
}

function escHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function nl2br(s){ return escHtml(s).replace(/\n/g,'<br>'); }

document.addEventListener('DOMContentLoaded', () => {
  AppBootstrapAuth.init({ requireAuth: true });

  document.querySelectorAll('.tab').forEach(tab=>{
    tab.addEventListener('click',()=>{
      document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById('tab-'+tab.dataset.tab).classList.add('active');
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.getElementById('courseModal').style.display = 'none';
      closeNotesModal();
    }
  });

  loadProgram();
});
</script>
</body>
</html>
