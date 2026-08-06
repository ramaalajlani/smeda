<?php
$basePath   = '../../';
$pageTitle  = 'إنشاء / تعديل برنامج تدريبي';
$activePage = 'program-bank-form';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root{
      --c:#1a6b5a;--ca:#10b981;--cs:#e8f8f3;--cb:rgba(26,107,90,.12);
      --ct:#15312a;--cm:#6b7280;--sh:0 8px 24px rgba(15,60,50,.07);
    }
    .pw{width:100%;max-width:960px;margin:0 auto;padding:0 0 28px;}

    .bc{display:flex;align-items:center;gap:6px;font-size:.83rem;color:var(--cm);margin-bottom:12px;flex-wrap:wrap;}
    .bc a{color:var(--c);text-decoration:none;font-weight:600;}

    .page-hd{display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
    .page-hd h1{font-size:clamp(1.15rem,2.5vw,1.4rem);font-weight:800;color:var(--ct);margin:0;flex:1;min-width:0;line-height:1.35;}

    .card{background:#fff;border:1px solid var(--cb);border-radius:18px;box-shadow:var(--sh);margin-bottom:14px;overflow:hidden;}

    /* Tabs: desktop scroll strip / mobile select */
    .tabs-wrap{position:relative;border-bottom:2px solid var(--cb);background:#fafdfb;}
    .tabs-mobile{
      display:none;width:100%;padding:12px 14px;border:none;border-bottom:2px solid var(--cb);
      background:#fff;font-size:.92rem;font-weight:800;color:var(--ct);font-family:inherit;
      appearance:none;-webkit-appearance:none;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%231a6b5a' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
      background-repeat:no-repeat;background-position:left 14px center;padding-left:36px;
    }
    .tabs{
      display:flex;gap:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;
      scroll-snap-type:x proximity;scrollbar-width:none;-ms-overflow-style:none;
    }
    .tabs::-webkit-scrollbar{display:none;width:0;height:0;}
    .tab{
      flex:0 0 auto;padding:12px 16px;font-size:.84rem;font-weight:700;color:var(--cm);
      cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;
      scroll-snap-align:start;user-select:none;touch-action:manipulation;
    }
    .tab.active{color:var(--c);border-bottom-color:var(--c);background:rgba(26,107,90,.04);}
    .tab-panel{display:none;padding:18px 16px;}
    .tab-panel.active{display:block;}

    .fg{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .fg.fg3{grid-template-columns:repeat(3,minmax(0,1fr));}
    .fg.fg1{grid-template-columns:1fr;}
    .fld{display:flex;flex-direction:column;gap:6px;min-width:0;}
    .fld label{font-size:.78rem;font-weight:700;color:var(--ct);}
    .fld input,.fld select,.fld textarea{
      width:100%;max-width:100%;box-sizing:border-box;min-height:44px;
      padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:11px;
      font-size:16px;color:var(--ct);font-family:inherit;background:#fff;transition:.2s;
    }
    .fld textarea{min-height:100px;resize:vertical;font-size:.92rem;}
    .fld input:focus,.fld select:focus,.fld textarea:focus{
      border-color:var(--c);outline:none;box-shadow:0 0 0 3px rgba(26,107,90,.08);
    }
    .required::after{content:" *";color:#ef4444;}

    .toggle-row{
      display:flex;align-items:center;justify-content:space-between;gap:12px;
      padding:12px 0;border-bottom:1px solid #f0f4f8;
    }
    .toggle-row:last-child{border-bottom:none;}
    .toggle-lbl{font-size:.87rem;font-weight:600;color:var(--ct);}
    .toggle-sub{font-size:.76rem;color:var(--cm);margin-top:2px;}
    .toggle{position:relative;display:inline-block;width:46px;height:28px;flex-shrink:0;}
    .toggle input{opacity:0;width:0;height:0;}
    .slider{position:absolute;inset:0;background:#d1d5db;border-radius:24px;cursor:pointer;transition:.2s;}
    .slider::before{content:"";position:absolute;width:22px;height:22px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;}
    input:checked+.slider{background:var(--c);}
    input:checked+.slider::before{transform:translateX(18px);}

    .item-list{display:flex;flex-direction:column;gap:10px;margin-bottom:12px;}
    .item-card{background:#f8fdf9;border:1.5px solid var(--cb);border-radius:14px;padding:14px;}
    .item-hd{display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;}
    .item-num{
      width:28px;height:28px;border-radius:8px;background:var(--c);color:#fff;
      display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem;flex-shrink:0;
    }
    .item-title-text{font-weight:700;font-size:.9rem;color:var(--ct);flex:1;min-width:0;}
    .btn-icon{
      width:40px;height:40px;border-radius:10px;border:none;cursor:pointer;
      display:inline-flex;align-items:center;justify-content:center;font-size:1rem;
    }
    .bi-del{background:#fee2e2;color:#991b1b;}
    .bi-toggle{background:#e0f2fe;color:#0369a1;}

    .btn-add{
      display:inline-flex;align-items:center;justify-content:center;gap:6px;
      min-height:44px;padding:10px 16px;border-radius:12px;background:var(--cs);color:var(--c);
      border:1.5px dashed var(--c);font-weight:700;font-size:.88rem;cursor:pointer;width:100%;
    }

    .submit-card{padding:14px;}
    .submit-bar{display:flex;align-items:center;gap:10px;justify-content:flex-end;flex-wrap:wrap;}
    .btn-save,.btn-cancel{
      min-height:46px;padding:11px 18px;border-radius:12px;font-weight:700;font-size:.9rem;
      border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;
      text-decoration:none;touch-action:manipulation;
    }
    .btn-save{background:var(--c);color:#fff;}
    .btn-save.secondary{background:#0369a1;}
    .btn-cancel{background:#f1f5f9;color:#334155;}

    #toast{
      position:fixed;bottom:max(16px,env(safe-area-inset-bottom));left:50%;transform:translateX(-50%);
      padding:12px 20px;border-radius:14px;font-weight:700;font-size:.88rem;z-index:9999;
      display:none;box-shadow:0 8px 24px rgba(0,0,0,.18);max-width:min(92vw,420px);text-align:center;
    }
    .toast-ok{background:#dcfce7;color:#15803d;border:1.5px solid #4ade80;}
    .toast-err{background:#fee2e2;color:#991b1b;border:1.5px solid #f87171;}

    @media (max-width:900px){
      .fg.fg3{grid-template-columns:1fr 1fr;}
    }
    @media (max-width:767px){
      .tabs-mobile{display:block;}
      .tabs{display:none;}
      .fg,.fg.fg3{grid-template-columns:1fr;}
      .tab-panel{padding:14px 12px;}
      .submit-bar{flex-direction:column-reverse;}
      .btn-save,.btn-cancel{width:100%;}
      .pw{padding-bottom:20px;}
    }
    @media (max-width:480px){
      .page-hd{margin-bottom:12px;}
      .card{border-radius:14px;}
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/program-bank-shell-open.php'; ?>

<div class="pw">

  <div class="bc">
    <a href="program-bank-dashboard.php">بنك البرامج</a>
    <i class="bi bi-chevron-left"></i>
    <a href="program-bank-list.php">القائمة</a>
    <i class="bi bi-chevron-left"></i>
    <span id="bcTitle">برنامج جديد</span>
  </div>

  <div class="page-hd">
    <h1 id="pageTitle">إنشاء برنامج تدريبي جديد</h1>
    <div id="statusBadge"></div>
  </div>

  <div class="card">
    <div class="tabs-wrap">
      <select class="tabs-mobile" id="tabsMobile" aria-label="أقسام النموذج">
        <option value="basic">المعلومات الأساسية</option>
        <option value="settings">الإعدادات</option>
        <option value="modules">المحاور التدريبية</option>
        <option value="outcomes">المخرجات</option>
        <option value="links">ربط بالخدمات</option>
      </select>
      <div class="tabs" id="tabs" role="tablist">
        <div class="tab active" data-tab="basic" role="tab">المعلومات الأساسية</div>
        <div class="tab" data-tab="settings" role="tab">الإعدادات</div>
        <div class="tab" data-tab="modules" role="tab">المحاور التدريبية</div>
        <div class="tab" data-tab="outcomes" role="tab">المخرجات</div>
        <div class="tab" data-tab="links" role="tab">ربط بالخدمات</div>
      </div>
    </div>

    <div class="tab-panel active" id="tab-basic">
      <div class="fg" style="margin-bottom:14px">
        <div class="fld">
          <label class="required">اسم البرنامج</label>
          <input type="text" id="name" placeholder="اسم البرنامج التدريبي" autocomplete="off">
        </div>
        <div class="fld">
          <label>العنوان التفصيلي</label>
          <input type="text" id="title" placeholder="عنوان تفصيلي (اختياري)" autocomplete="off">
        </div>
      </div>
      <div class="fg" style="margin-bottom:14px">
        <div class="fld">
          <label class="required">الرمز</label>
          <input type="text" id="code" placeholder="مثال: PROG-001" autocomplete="off">
        </div>
        <div class="fld">
          <label>القطاع</label>
          <input type="text" id="sector" placeholder="القطاع المستهدف" autocomplete="off">
        </div>
      </div>
      <div class="fg" style="margin-bottom:14px">
        <div class="fld">
          <label>نوع البرنامج</label>
          <select id="type">
            <option value="">-- اختر --</option>
            <option value="entrepreneurial">ريادي</option>
            <option value="financial">مالي</option>
            <option value="marketing">تسويقي</option>
            <option value="technical">فني</option>
            <option value="digital">رقمي</option>
            <option value="administrative">إداري</option>
            <option value="agricultural">زراعي</option>
            <option value="craft">حرفي</option>
            <option value="other">أخرى</option>
          </select>
        </div>
        <div class="fld">
          <label>الفئة المستهدفة</label>
          <input type="text" id="target_audience" placeholder="من هو الجمهور المستهدف؟" autocomplete="off">
        </div>
      </div>
      <div class="fg fg1">
        <div class="fld">
          <label>وصف البرنامج</label>
          <textarea id="description" placeholder="وصف تفصيلي للبرنامج التدريبي..."></textarea>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="tab-settings">
      <div class="fg fg3" style="margin-bottom:14px">
        <div class="fld">
          <label>المستوى</label>
          <select id="level">
            <option value="">-- اختر --</option>
            <option value="beginner">مبتدئ</option>
            <option value="intermediate">متوسط</option>
            <option value="advanced">متقدم</option>
          </select>
        </div>
        <div class="fld">
          <label>طريقة التقديم</label>
          <select id="delivery_mode">
            <option value="">-- اختر --</option>
            <option value="in_person">حضوري</option>
            <option value="online">أونلاين</option>
            <option value="blended">مدمج</option>
          </select>
        </div>
        <div class="fld">
          <label>الساعات المقترحة</label>
          <input type="number" id="suggested_hours" min="0" inputmode="numeric" placeholder="عدد الساعات">
        </div>
      </div>
      <div class="fg fg3" style="margin-bottom:20px">
        <div class="fld">
          <label>عدد الجلسات المقترحة</label>
          <input type="number" id="suggested_sessions" min="0" inputmode="numeric" placeholder="عدد الجلسات">
        </div>
        <div class="fld">
          <label>نسبة الحضور الدنيا (%)</label>
          <input type="number" id="min_attendance_percent" min="0" max="100" inputmode="numeric" placeholder="80">
        </div>
        <div class="fld">
          <label>درجة النجاح</label>
          <input type="number" id="passing_score" min="0" max="100" inputmode="numeric" placeholder="60">
        </div>
      </div>

      <div style="border-top:1px solid var(--cb);padding-top:12px;">
        <div class="toggle-row">
          <div><div class="toggle-lbl">يمنح شهادة</div><div class="toggle-sub">هل يتم إصدار شهادة إتمام أو اجتياز؟</div></div>
          <label class="toggle"><input type="checkbox" id="grants_certificate"><span class="slider"></span></label>
        </div>
        <div class="toggle-row">
          <div><div class="toggle-lbl">يتطلب اختباراً نهائياً</div><div class="toggle-sub">هل هناك اختبار في نهاية البرنامج؟</div></div>
          <label class="toggle"><input type="checkbox" id="requires_final_exam"><span class="slider"></span></label>
        </div>
        <div class="toggle-row">
          <div><div class="toggle-lbl">يتطلب مشروعاً تطبيقياً</div><div class="toggle-sub">هل يشترط تقديم مشروع ختامي؟</div></div>
          <label class="toggle"><input type="checkbox" id="requires_project"><span class="slider"></span></label>
        </div>
      </div>

      <div class="fg fg1" style="margin-top:16px">
        <div class="fld">
          <label>المتطلبات المسبقة</label>
          <textarea id="prerequisites" placeholder="ما هي المتطلبات السابقة للالتحاق بالبرنامج؟"></textarea>
        </div>
      </div>
      <div class="fg fg1">
        <div class="fld">
          <label>ملخص المخرجات</label>
          <textarea id="outcomes_summary" placeholder="ملخص عام لما سيحققه المتدرب بعد إتمام البرنامج..."></textarea>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="tab-modules">
      <p style="color:var(--cm);font-size:.85rem;margin-bottom:14px">أضف المحاور والموضوعات الرئيسية للبرنامج التدريبي</p>
      <div class="item-list" id="modulesList"></div>
      <button type="button" class="btn-add" onclick="addModule()"><i class="bi bi-plus-lg"></i> إضافة محور</button>
    </div>

    <div class="tab-panel" id="tab-outcomes">
      <p style="color:var(--cm);font-size:.85rem;margin-bottom:14px">أضف المخرجات التعليمية المتوقعة من هذا البرنامج</p>
      <div class="item-list" id="outcomesList"></div>
      <button type="button" class="btn-add" onclick="addOutcome()"><i class="bi bi-plus-lg"></i> إضافة مخرج</button>
    </div>

    <div class="tab-panel" id="tab-links">
      <p style="color:var(--cm);font-size:.85rem;margin-bottom:14px">اربط هذا البرنامج بخدمات الهيئة ذات الصلة</p>
      <div class="item-list" id="linksList">
        <?php
        $services = [
          'needs_map'=>'خريطة الاحتياجات','entrepreneurs'=>'رواد الأعمال',
          'finance'=>'التمويل','consulting'=>'الاستشارات',
          'incubators'=>'الحاضنات','accelerators'=>'المسرعات',
          'certificates'=>'الشهادات','risk_assessment'=>'تقييم المخاطر',
        ];
        foreach($services as $val => $lbl): ?>
        <div class="toggle-row">
          <div><div class="toggle-lbl"><?= htmlspecialchars($lbl) ?></div></div>
          <label class="toggle"><input type="checkbox" class="svc-check" value="<?= htmlspecialchars($val) ?>"><span class="slider"></span></label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card submit-card">
    <div class="submit-bar">
      <a href="program-bank-list.php" class="btn-cancel">إلغاء</a>
      <button type="button" class="btn-save" onclick="saveProgram(false)"><i class="bi bi-floppy"></i> حفظ مسودة</button>
      <button type="button" class="btn-save secondary" onclick="saveProgram(true)"><i class="bi bi-send"></i> حفظ وإرسال للمراجعة</button>
    </div>
  </div>

</div>

<div id="toast"></div>

<?php include __DIR__ . '/../../includes/layout/program-bank-shell-close.php'; ?>
<script>
const API = window.APP_CONFIG?.API_BASE_URL;
const urlId = new URLSearchParams(location.search).get('id');
let editMode = !!urlId;
let existingProgram = null;
let modulesData = [];
let outcomesData = [];

function activateTab(key) {
  document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === key));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.id === 'tab-' + key));
  const mobile = document.getElementById('tabsMobile');
  if (mobile && mobile.value !== key) mobile.value = key;
  const activeTab = document.querySelector('.tab.active');
  if (activeTab && activeTab.scrollIntoView) {
    activeTab.scrollIntoView({ inline: 'nearest', block: 'nearest', behavior: 'smooth' });
  }
}

async function loadProgram() {
  if (!editMode) return;
  try {
    const r = await fetch(API + '/program-bank/' + urlId, {
      headers:{Authorization:'Bearer '+AppAuth.getToken()}
    });
    const { data } = await r.json();
    existingProgram = data;
    fillForm(data);
  } catch(e){ showToast('فشل تحميل البيانات','err'); }
}

function fillForm(p) {
  document.getElementById('pageTitle').textContent = 'تعديل: ' + (p.display_name||p.name);
  document.getElementById('bcTitle').textContent = 'تعديل برنامج';
  const BADGE = {
    draft:'<span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:16px;font-size:.78rem;font-weight:700">مسودة</span>',
    under_technical_review:'<span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:16px;font-size:.78rem;font-weight:700">مراجعة فنية</span>',
    under_admin_review:'<span style="background:#fff7ed;color:#9a3412;padding:3px 10px;border-radius:16px;font-size:.78rem;font-weight:700">مراجعة إدارية</span>',
    approved:'<span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:16px;font-size:.78rem;font-weight:700">معتمد</span>',
  };
  document.getElementById('statusBadge').innerHTML = BADGE[p.bank_status]||'';

  const set = (id,v)=>{ if(document.getElementById(id)) document.getElementById(id).value=v||''; };
  set('name',p.name); set('title',p.title); set('code',p.code); set('sector',p.sector);
  set('type',p.type); set('target_audience',p.target_audience); set('description',p.description);
  set('level',p.level); set('delivery_mode',p.delivery_mode);
  set('suggested_hours',p.suggested_hours); set('suggested_sessions',p.suggested_sessions);
  set('min_attendance_percent',p.min_attendance_percent); set('passing_score',p.passing_score);
  set('prerequisites',p.prerequisites); set('outcomes_summary',p.outcomes_summary);

  if(p.grants_certificate)  document.getElementById('grants_certificate').checked=true;
  if(p.requires_final_exam) document.getElementById('requires_final_exam').checked=true;
  if(p.requires_project)    document.getElementById('requires_project').checked=true;

  modulesData = (p.modules||[]).map(m=>({...m}));
  renderModules();
  outcomesData = (p.outcomes||[]).map(o=>({...o}));
  renderOutcomes();

  (p.service_links||[]).forEach(lnk => {
    const el = document.querySelector(`.svc-check[value="${lnk.service_type}"]`);
    if(el) el.checked = true;
  });
}

function addModule() {
  modulesData.push({ title:'', description:'', hours:'', objectives:'', activities:'', required_tools:'', evaluation_method:'', _open:true });
  renderModules();
}

function removeModule(i) {
  modulesData.splice(i,1);
  renderModules();
}

function toggleModule(i) {
  modulesData[i]._open = !modulesData[i]._open;
  renderModules();
}

function syncModule(i, field, val) { modulesData[i][field] = val; }

function renderModules() {
  const el = document.getElementById('modulesList');
  if(!modulesData.length){ el.innerHTML='<p style="color:var(--cm);font-size:.85rem">لا توجد محاور بعد</p>'; return; }
  el.innerHTML = modulesData.map((m,i)=>`
    <div class="item-card">
      <div class="item-hd">
        <div class="item-num">${i+1}</div>
        <input class="item-title-text" style="border:none;background:transparent;font-weight:700;font-size:.9rem;color:var(--ct);flex:1;outline:none;" placeholder="عنوان المحور" value="${escHtml(m.title)}" oninput="syncModule(${i},'title',this.value)">
        <button class="btn-icon bi-toggle" onclick="toggleModule(${i})" title="${m._open?'طي':'توسيع'}"><i class="bi bi-chevron-${m._open?'up':'down'}"></i></button>
        <button class="btn-icon bi-del" onclick="removeModule(${i})" title="حذف"><i class="bi bi-trash"></i></button>
      </div>
      ${m._open ? `
      <div class="fg" style="gap:10px">
        <div class="fld"><label>عدد الساعات</label><input type="number" value="${m.hours||''}" oninput="syncModule(${i},'hours',this.value)" placeholder="0"></div>
        <div class="fld"><label>طريقة التقييم</label><input type="text" value="${escHtml(m.evaluation_method||'')}" oninput="syncModule(${i},'evaluation_method',this.value)" placeholder="اختبار / تمرين..."></div>
      </div>
      <div class="fg fg1" style="gap:10px;margin-top:8px">
        <div class="fld"><label>الأهداف</label><textarea oninput="syncModule(${i},'objectives',this.value)" placeholder="أهداف هذا المحور...">${escHtml(m.objectives||'')}</textarea></div>
        <div class="fld"><label>الأنشطة</label><textarea oninput="syncModule(${i},'activities',this.value)" placeholder="الأنشطة والتمارين...">${escHtml(m.activities||'')}</textarea></div>
        <div class="fld"><label>الأدوات المطلوبة</label><textarea oninput="syncModule(${i},'required_tools',this.value)" placeholder="أدوات ومواد...">${escHtml(m.required_tools||'')}</textarea></div>
      </div>` : ''}
    </div>
  `).join('');
}

// ── Outcomes ──────────────────────────────────────────
function addOutcome() {
  outcomesData.push({ title:'', description:'' });
  renderOutcomes();
}

function removeOutcome(i) { outcomesData.splice(i,1); renderOutcomes(); }
function syncOutcome(i,field,val){ outcomesData[i][field]=val; }

function renderOutcomes() {
  const el = document.getElementById('outcomesList');
  if(!outcomesData.length){ el.innerHTML='<p style="color:var(--cm);font-size:.85rem">لا توجد مخرجات بعد</p>'; return; }
  el.innerHTML = outcomesData.map((o,i)=>`
    <div class="item-card">
      <div class="item-hd">
        <div class="item-num">${i+1}</div>
        <input class="item-title-text" style="border:none;background:transparent;font-weight:700;font-size:.9rem;color:var(--ct);flex:1;outline:none;" placeholder="المخرج التعليمي" value="${escHtml(o.title)}" oninput="syncOutcome(${i},'title',this.value)">
        <button class="btn-icon bi-del" onclick="removeOutcome(${i})" title="حذف"><i class="bi bi-trash"></i></button>
      </div>
      <div class="fld"><textarea placeholder="وصف المخرج..." oninput="syncOutcome(${i},'description',this.value)">${escHtml(o.description||'')}</textarea></div>
    </div>
  `).join('');
}

// ── Save ──────────────────────────────────────────────
async function saveProgram(submitForReview = false) {
  const name = document.getElementById('name').value.trim();
  const code = document.getElementById('code').value.trim();
  if(!name){ showToast('يرجى إدخال اسم البرنامج','err'); return; }
  if(!code){ showToast('يرجى إدخال رمز البرنامج','err'); return; }

  const saveBtns = document.querySelectorAll('.btn-save');
  saveBtns.forEach(b => { b.disabled=true; b.dataset.orig=b.innerHTML; b.innerHTML='<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-left:6px"></span> جارٍ الحفظ...'; });

  const payload = {
    name, code,
    title:               document.getElementById('title').value.trim()||null,
    description:         document.getElementById('description').value.trim()||null,
    type:                document.getElementById('type').value||null,
    sector:              document.getElementById('sector').value.trim()||null,
    target_audience:     document.getElementById('target_audience').value.trim()||null,
    level:               document.getElementById('level').value||null,
    delivery_mode:       document.getElementById('delivery_mode').value||null,
    suggested_hours:     parseInt(document.getElementById('suggested_hours').value)||null,
    suggested_sessions:  parseInt(document.getElementById('suggested_sessions').value)||null,
    min_attendance_percent: parseInt(document.getElementById('min_attendance_percent').value)||80,
    passing_score:       parseInt(document.getElementById('passing_score').value)||60,
    prerequisites:       document.getElementById('prerequisites').value.trim()||null,
    outcomes_summary:    document.getElementById('outcomes_summary').value.trim()||null,
    grants_certificate:  document.getElementById('grants_certificate').checked,
    requires_final_exam: document.getElementById('requires_final_exam').checked,
    requires_project:    document.getElementById('requires_project').checked,
  };

  try {
    let r;
    if (editMode) {
      r = await fetch(API + '/program-bank/' + urlId, {
        method:'PUT', headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
        body: JSON.stringify(payload)
      });
    } else {
      r = await fetch(API + '/program-bank', {
        method:'POST', headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
        body: JSON.stringify(payload)
      });
    }

    const body = await r.json();
    if (!r.ok) { showToast(body.message||'خطأ في الحفظ','err'); return; }

    const progId = body.data.id;

    // Save modules & outcomes via individual API calls
    await saveModulesAndOutcomes(progId);

    // Save service links
    await saveServiceLinks(progId);

    // Submit for review if requested
    if (submitForReview) {
      await fetch(API + '/program-bank/' + progId + '/transition', {
        method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
        body: JSON.stringify({ action:'submit' })
      });
    }

    showToast('تم الحفظ بنجاح!','ok');
    setTimeout(()=>{ location.href = 'program-bank-show.php?id=' + progId; }, 1200);

  } catch(e){
    showToast(e.message||'خطأ في الاتصال','err');
  } finally {
    saveBtns.forEach(b => { b.disabled=false; b.innerHTML=b.dataset.orig||b.innerHTML; });
  }
}

async function saveModulesAndOutcomes(progId) {
  const token = 'Bearer ' + AppAuth.getToken();
  const headers = { 'Content-Type': 'application/json', Authorization: token };

  const moduleReqs = modulesData
    .filter(m => m.title.trim())
    .map((m, i) => {
      const body = JSON.stringify({
        title: m.title, description: m.description||null, hours: parseInt(m.hours)||null,
        sort_order: i, objectives: m.objectives||null, activities: m.activities||null,
        required_tools: m.required_tools||null, evaluation_method: m.evaluation_method||null
      });
      const url = m.id ? API+'/program-bank/'+progId+'/modules/'+m.id : API+'/program-bank/'+progId+'/modules';
      return fetch(url, { method: m.id ? 'PUT' : 'POST', headers, body });
    });

  const outcomeReqs = outcomesData
    .filter(o => o.title.trim())
    .map((o, i) => {
      const body = JSON.stringify({ title: o.title, description: o.description||null, sort_order: i });
      const url = o.id ? API+'/program-bank/'+progId+'/outcomes/'+o.id : API+'/program-bank/'+progId+'/outcomes';
      return fetch(url, { method: o.id ? 'PUT' : 'POST', headers, body });
    });

  const results = await Promise.all([...moduleReqs, ...outcomeReqs]);
  const failed = results.filter(r => !r.ok);
  if (failed.length) {
    throw new Error('فشل حفظ ' + failed.length + ' عنصر من المحاور أو المخرجات');
  }
}

async function saveServiceLinks(progId) {
  const checked = [...document.querySelectorAll('.svc-check:checked')].map(el=>({ service_type:el.value }));
  if(!checked.length) return;
  await fetch(API+'/program-bank/'+progId+'/service-links',{
    method:'PUT',headers:{'Content-Type':'application/json','Authorization':'Bearer '+AppAuth.getToken()},
    body:JSON.stringify({ links:checked })
  });
}

// ── Toast & utils ─────────────────────────────────────
function showToast(msg, type='ok') {
  const t = document.getElementById('toast');
  t.className = type==='ok'?'toast-ok':'toast-err';
  t.textContent = msg;
  t.style.display='block';
  setTimeout(()=>{ t.style.display='none'; },3000);
}

function escHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.addEventListener('DOMContentLoaded', () => {
  AppBootstrapAuth.init({ requireAuth: true });

  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => activateTab(tab.dataset.tab));
  });
  const tabsMobile = document.getElementById('tabsMobile');
  if (tabsMobile) {
    tabsMobile.addEventListener('change', () => activateTab(tabsMobile.value));
  }

  loadProgram();
});
</script>
</body>
</html>
