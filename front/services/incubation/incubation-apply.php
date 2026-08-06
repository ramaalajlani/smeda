<?php
$basePath  = '../../';
$pageTitle = 'التقديم للانضمام لحاضنة';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.13);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.07);}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe);}
    .pw{max-width:720px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .btn-outline-w{background:#fff;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;padding:9px 20px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:26px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 18px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .step-indicator{display:flex;gap:0;margin-bottom:24px;}
    .step{flex:1;text-align:center;font-size:.78rem;font-weight:700;color:var(--c-muted);position:relative;padding-bottom:8px;}
    .step::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:#e5e7eb;border-radius:3px;}
    .step.active{color:var(--c-primary);}
    .step.active::after{background:var(--c-primary);}
    .step.done{color:#15803d;}
    .step.done::after{background:#15803d;}
    .form-group{margin-bottom:16px;}
    .form-label{font-size:.82rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:6px;}
    .form-control,.form-select{width:100%;border:1px solid var(--c-border);border-radius:11px;padding:10px 13px;font-size:.88rem;color:var(--c-text);box-sizing:border-box;}
    .form-control:focus,.form-select:focus{outline:none;border-color:var(--c-primary);box-shadow:0 0 0 3px rgba(124,58,237,.12);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:560px){.form-row{grid-template-columns:1fr;}}
    .check-group{display:flex;align-items:center;gap:10px;padding:12px;background:var(--c-soft);border-radius:12px;margin-bottom:10px;}
    .check-group input[type=checkbox]{width:18px;height:18px;accent-color:var(--c-primary);}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:12px 28px;font-weight:700;cursor:pointer;font-size:.92rem;display:inline-flex;align-items:center;gap:8px;}
    .btn-secondary{background:var(--c-soft);color:var(--c-primary);border:none;border-radius:12px;padding:12px 22px;font-weight:700;cursor:pointer;font-size:.88rem;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:12px 16px;font-weight:700;font-size:.88rem;display:none;margin-bottom:14px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:12px 16px;font-weight:700;font-size:.88rem;display:none;margin-bottom:14px;}
    .inc-select-card{border:2px solid var(--c-border);border-radius:14px;padding:14px;cursor:pointer;transition:border-color .2s;}
    .inc-select-card:hover,.inc-select-card.selected{border-color:var(--c-primary);background:var(--c-soft);}
    .inc-select-card .inc-n{font-weight:800;color:var(--c-text);}
    .inc-select-card .inc-m{font-size:.8rem;color:var(--c-muted);}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-send-fill"></i> التقديم للانضمام لحاضنة</h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px">قدّم مشروعك للحصول على دعم الحاضنة</div>
    </div>
    <a href="incubators-list.php" class="btn-outline-w"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="step-indicator">
        <div class="step active" id="step1-ind">1 — اختيار الحاضنة</div>
        <div class="step" id="step2-ind">2 — بيانات المشروع</div>
        <div class="step" id="step3-ind">3 — إرسال الطلب</div>
      </div>

      <div id="formSuccess" class="alert-s"></div>
      <div id="formError"   class="alert-e"></div>

      <!-- Step 1 -->
      <div id="step1">
        <div class="card-title"><i class="bi bi-rocket-takeoff-fill"></i> اختر الحاضنة</div>
        <div id="incList" style="display:grid;gap:10px"></div>
        <div style="margin-top:16px;text-align:left">
          <button class="btn-primary" onclick="goStep2()"><i class="bi bi-arrow-left-circle-fill"></i> التالي</button>
        </div>
      </div>

      <!-- Step 2 -->
      <div id="step2" style="display:none">
        <div class="card-title" id="step2Title"><i class="bi bi-briefcase-fill"></i> بيانات المشروع</div>

        <!-- شارة الحاضنة التقنية -->
        <div id="techBanner" style="display:none;background:linear-gradient(135deg,#1e1b4b,#4c1d95);color:#fff;border-radius:14px;padding:14px 18px;margin-bottom:18px;display:none;align-items:center;gap:12px">
          <i class="bi bi-cpu-fill" style="font-size:1.6rem;opacity:.9"></i>
          <div>
            <div style="font-weight:800;font-size:.95rem">حاضنة تقنية — نموذج تقديم متخصص</div>
            <div style="opacity:.8;font-size:.8rem;margin-top:2px">ستظهر حقول إضافية لمشاريع التقنية (TRL، Tech Stack، نموذج الإيرادات...)</div>
          </div>
        </div>

        <!-- ─── الحقول الأساسية ─── -->
        <div class="form-group"><label class="form-label">اسم المشروع <span style="color:#dc2626">*</span></label><input type="text" id="fProjName" class="form-control" placeholder="اسم مشروعك..."></div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">قطاع المشروع</label>
            <select id="fSector" class="form-select">
              <option value="">-- اختر --</option>
              <option value="tech">تقنية</option>
              <option value="industrial">صناعي</option>
              <option value="agricultural">زراعي</option>
              <option value="services">خدمات</option>
              <option value="creative">إبداعي</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">مرحلة المشروع <span style="color:#dc2626">*</span></label>
            <select id="fStage" class="form-select">
              <option value="idea">فكرة</option>
              <option value="pre_seed">ما قبل الإطلاق</option>
              <option value="seed">بذرة</option>
              <option value="early">مبكر</option>
              <option value="growth">نمو</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">وصف المشروع <span style="color:#dc2626">*</span></label><textarea id="fDesc" class="form-control" rows="4" placeholder="اشرح فكرة مشروعك بالتفصيل..."></textarea></div>
        <div class="form-group"><label class="form-label">المشكلة التي يحلها</label><textarea id="fProblem" class="form-control" rows="3" placeholder="ما المشكلة التي يعالجها مشروعك؟"></textarea></div>
        <div class="form-group"><label class="form-label">السوق المستهدف</label><textarea id="fMarket" class="form-control" rows="2" placeholder="من هم عملاؤك المستهدفون؟"></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">حجم الفريق</label><input type="number" id="fTeam" class="form-control" value="1" min="1" max="200"></div>
          <div class="form-group"><label class="form-label">التمويل المطلوب (ل.س)</label><input type="number" id="fFunding" class="form-control" placeholder="0" min="0"></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">مرحلة التمويل</label>
            <select id="fFundStage" class="form-select">
              <option value="">-- اختر --</option>
              <option value="bootstrapped">ممول ذاتياً</option>
              <option value="seeking">يبحث عن تمويل</option>
              <option value="funded">حصل على تمويل</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">الوظائف المتوقعة</label><input type="number" id="fJobs" class="form-control" placeholder="عدد الوظائف المتوقعة" min="0"></div>
        </div>
        <div class="form-group"><label class="form-label">الميزة التنافسية</label><textarea id="fAdvantage" class="form-control" rows="2" placeholder="ما الذي يميز مشروعك عن المنافسين؟"></textarea></div>
        <div class="check-group"><input type="checkbox" id="fProto"><label for="fProto" style="font-weight:700;cursor:pointer">لدي نموذج أولي (Prototype / MVP)</label></div>
        <div class="check-group"><input type="checkbox" id="fRev"><label for="fRev" style="font-weight:700;cursor:pointer">المشروع يحقق إيرادات حالياً</label></div>

        <!-- ─── الحقول التقنية (تظهر فقط للحاضنات التقنية) ─── -->
        <div id="techFields" style="display:none">
          <div style="border-top:2px dashed var(--c-border);margin:20px 0 16px;position:relative">
            <span style="position:absolute;top:-12px;right:50%;transform:translateX(50%);background:#fff;padding:0 12px;font-size:.8rem;font-weight:700;color:var(--c-primary);white-space:nowrap"><i class="bi bi-cpu-fill"></i> الحقول التقنية المتخصصة</span>
          </div>

          <!-- TRL -->
          <div class="form-group">
            <label class="form-label">مستوى الجاهزية التقنية (TRL) <span style="color:#dc2626">*</span></label>
            <select id="fTRL" class="form-select">
              <option value="">-- اختر المستوى --</option>
              <option value="1">1 — مبادئ أساسية (فكرة بحثية)</option>
              <option value="2">2 — مفهوم التقنية (صياغة التطبيق)</option>
              <option value="3">3 — إثبات المفهوم التجريبي (POC)</option>
              <option value="4">4 — نموذج أولي في المختبر</option>
              <option value="5">5 — نموذج أولي في بيئة محاكاة</option>
              <option value="6">6 — نموذج أولي في بيئة حقيقية</option>
              <option value="7">7 — نموذج تجريبي في بيئة حقيقية (Beta)</option>
              <option value="8">8 — نظام مكتمل وجاهز للإطلاق</option>
              <option value="9">9 — منتج مُثبَت في السوق (Launched)</option>
            </select>
            <div id="trlHint" style="font-size:.77rem;color:var(--c-muted);margin-top:6px;padding:8px;background:var(--c-soft);border-radius:8px;display:none"></div>
          </div>

          <!-- Tech Stack -->
          <div class="form-group">
            <label class="form-label">التقنيات المستخدمة (Tech Stack)</label>
            <div style="border:1.5px solid var(--c-border);border-radius:12px;padding:12px;background:#fafaf9">
              <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px" id="techTagsBox"></div>
              <div style="display:flex;gap:8px">
                <input type="text" id="techInput" class="form-control" placeholder="أضف تقنية... (React, Laravel, Python...)" style="flex:1" onkeydown="handleTechKey(event)">
                <button type="button" class="btn-secondary" style="padding:8px 14px;font-size:.82rem;flex-shrink:0" onclick="addTechTag()"><i class="bi bi-plus-lg"></i> إضافة</button>
              </div>
              <div style="font-size:.76rem;color:var(--c-muted);margin-top:6px">اضغط Enter أو زر الإضافة لكل تقنية</div>
            </div>
            <input type="hidden" id="fTechStack">
          </div>

          <div class="form-row">
            <!-- نموذج الإيرادات -->
            <div class="form-group">
              <label class="form-label">نموذج الإيرادات</label>
              <select id="fRevenueModel" class="form-select">
                <option value="">-- اختر --</option>
                <option value="saas">SaaS (اشتراك)</option>
                <option value="b2b">B2B (مؤسسات)</option>
                <option value="b2c">B2C (أفراد)</option>
                <option value="marketplace">Marketplace</option>
                <option value="hardware">Hardware (أجهزة)</option>
                <option value="freemium">Freemium</option>
                <option value="other">أخرى</option>
              </select>
            </div>
            <!-- المنصة المستهدفة -->
            <div class="form-group">
              <label class="form-label">المنصة المستهدفة</label>
              <select id="fPlatform" class="form-select">
                <option value="">-- اختر --</option>
                <option value="web">ويب</option>
                <option value="mobile">جوال</option>
                <option value="desktop">سطح المكتب</option>
                <option value="saas">SaaS Cloud</option>
                <option value="api">API / خدمة</option>
                <option value="embedded">مدمج / IoT</option>
                <option value="other">أخرى</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <!-- رابط Demo -->
            <div class="form-group">
              <label class="form-label"><i class="bi bi-play-btn-fill" style="color:var(--c-primary)"></i> رابط Demo / MVP</label>
              <input type="url" id="fDemoUrl" class="form-control" placeholder="https://demo.yourproject.com">
            </div>
            <!-- GitHub -->
            <div class="form-group">
              <label class="form-label"><i class="bi bi-github"></i> رابط GitHub</label>
              <input type="url" id="fGithub" class="form-control" placeholder="https://github.com/...">
            </div>
          </div>

          <!-- الملكية الفكرية -->
          <div class="check-group" style="border:1.5px solid #ddd6fe;background:#ede9fe">
            <input type="checkbox" id="fHasIP" onchange="toggleIpDesc()">
            <label for="fHasIP" style="font-weight:700;cursor:pointer;color:#4c1d95">
              <i class="bi bi-shield-fill-check" style="color:#7c3aed"></i>
              لدي ملكية فكرية / براءة اختراع مسجلة أو قيد التسجيل
            </label>
          </div>
          <div class="form-group" id="ipDescGroup" style="display:none;margin-top:8px">
            <label class="form-label">وصف الملكية الفكرية</label>
            <textarea id="fIPDesc" class="form-control" rows="2" placeholder="اذكر نوع الحماية ورقم البراءة إن وجد..."></textarea>
          </div>
        </div><!-- /techFields -->

        <div style="display:flex;gap:10px;margin-top:20px;justify-content:space-between">
          <button class="btn-secondary" onclick="goStep1()"><i class="bi bi-arrow-right-circle-fill"></i> السابق</button>
          <button class="btn-primary" onclick="submitApplication()"><i class="bi bi-send-fill"></i> إرسال الطلب</button>
        </div>
      </div>

      <!-- Step 3 success -->
      <div id="step3" style="display:none;text-align:center;padding:30px">
        <i class="bi bi-check-circle-fill" style="font-size:4rem;color:#15803d;display:block;margin-bottom:16px"></i>
        <div style="font-size:1.2rem;font-weight:800;color:var(--c-text);margin-bottom:8px">تم إرسال طلبك بنجاح!</div>
        <div style="color:var(--c-muted);font-size:.9rem;margin-bottom:24px">سيتم مراجعة طلبك والتواصل معك قريباً</div>
        <a href="my-incubation-application.php" class="btn-primary"><i class="bi bi-file-earmark-text-fill"></i> متابعة طلبي</a>
      </div>
    </div>
  </div>
</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const SECTOR_LBL = { tech:'تقنية', industrial:'صناعي', agricultural:'زراعي', services:'خدمات', creative:'إبداعي' };

  let allInc = [], selectedId = null, selectedSector = null;
  let techTags = [];

  const TRL_HINTS = {
    1:'فكرة بحثية أساسية — لا يزال في مرحلة النظرية.',
    2:'تم تحديد مفهوم التطبيق ووضع الأساس العلمي.',
    3:'تم إثبات المفهوم تجريبياً في بيئة مختبرية.',
    4:'نموذج أولي أساسي يعمل في ظروف مضبوطة.',
    5:'النموذج الأولي يعمل في بيئة محاكاة قريبة من الواقع.',
    6:'النموذج الأولي تم اختباره في بيئة حقيقية.',
    7:'نموذج تجريبي (Beta) يعمل في بيئة التشغيل الفعلية.',
    8:'النظام مكتمل وجاهز للإطلاق التجاري.',
    9:'المنتج موجود في السوق وأثبت نجاحه التجاري.',
  };

  // pre-select from URL
  const preId = new URLSearchParams(location.search).get('incubator_id');

  async function load() {
    const r = await fetch(`${base}/incubators`, { headers:{ Authorization:`Bearer ${token()}` }});
    allInc = (await r.json()) ?? [];
    if (allInc.data) allInc = allInc.data;

    document.getElementById('incList').innerHTML = allInc.map(i => `
      <div class="inc-select-card ${preId && String(i.id)===preId ? 'selected' : ''}" id="inc-${i.id}" onclick="selectInc(${i.id},'${i.sector||''}')">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:40px;height:40px;border-radius:12px;background:${i.sector==='tech'?'linear-gradient(135deg,#1e1b4b,#7c3aed)':'linear-gradient(135deg,#0f5e4f,#17947B)'};display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0">
            <i class="bi ${i.sector==='tech'?'bi-cpu-fill':'bi-rocket-takeoff-fill'}"></i>
          </div>
          <div>
            <div class="inc-n">${window.APP_HELPERS.e(i.name)}</div>
            <div class="inc-m">
              ${i.sector==='tech'?'<span style="background:#ede9fe;color:#7c3aed;border-radius:6px;padding:2px 7px;font-size:.73rem;font-weight:700"><i class="bi bi-cpu-fill"></i> حاضنة تقنية</span>':''}
              ${SECTOR_LBL[i.sector]??''} ${i.location ? '· '+window.APP_HELPERS.e(i.location) : ''} · طاقة: ${i.capacity??'—'} مشروع
            </div>
          </div>
        </div>
      </div>`).join('');

    if (preId) {
      const found = allInc.find(i=>String(i.id)===preId);
      selectInc(Number(preId), found?.sector||'');
    }
  }

  window.selectInc = function(id, sector) {
    document.querySelectorAll('.inc-select-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('inc-'+id)?.classList.add('selected');
    selectedId     = id;
    selectedSector = sector;
  };

  window.goStep2 = function() {
    if (!selectedId) {
      const el = document.getElementById('formError');
      el.textContent='يرجى اختيار حاضنة أولاً'; el.style.display='block';
      setTimeout(()=>el.style.display='none',3000); return;
    }
    document.getElementById('step1').style.display='none';
    document.getElementById('step2').style.display='';
    document.getElementById('step1-ind').classList.replace('active','done');
    document.getElementById('step2-ind').classList.add('active');

    // كشف تلقائي للحاضنة التقنية
    const isTech = selectedSector === 'tech';
    const banner = document.getElementById('techBanner');
    const fields = document.getElementById('techFields');
    banner.style.display = isTech ? 'flex' : 'none';
    fields.style.display  = isTech ? ''     : 'none';

    const inc = allInc.find(i=>i.id===selectedId);
    document.getElementById('step2Title').innerHTML =
      `<i class="bi ${isTech?'bi-cpu-fill':'bi-briefcase-fill'}"></i> بيانات المشروع${inc?' — '+window.APP_HELPERS.e(inc.name):''}`;
  };

  window.goStep1 = function() {
    document.getElementById('step2').style.display='none';
    document.getElementById('step1').style.display='';
    document.getElementById('step2-ind').classList.remove('active');
    document.getElementById('step1-ind').classList.replace('done','active');
  };

  /* ── TRL hint ── */
  document.getElementById('fTRL')?.addEventListener('change', function() {
    const hintEl = document.getElementById('trlHint');
    if (this.value && TRL_HINTS[this.value]) {
      hintEl.textContent = 'TRL ' + this.value + ': ' + TRL_HINTS[this.value];
      hintEl.style.display = '';
    } else {
      hintEl.style.display = 'none';
    }
  });

  /* ── Tech Tags ── */
  window.handleTechKey = function(e) { if (e.key==='Enter') { e.preventDefault(); addTechTag(); } };
  window.addTechTag = function() {
    const input = document.getElementById('techInput');
    const val   = input.value.trim();
    if (!val || techTags.includes(val)) { input.value=''; return; }
    techTags.push(val);
    input.value = '';
    renderTechTags();
  };
  window.removeTechTag = function(tag) {
    techTags = techTags.filter(t=>t!==tag);
    renderTechTags();
  };
  function renderTechTags() {
    document.getElementById('techTagsBox').innerHTML = techTags.map(t=>
      `<span style="background:#ede9fe;color:#4c1d95;border-radius:8px;padding:4px 10px;font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;gap:5px">
        ${window.APP_HELPERS.e(t)}
        <button type="button" onclick="removeTechTag('${window.APP_HELPERS.e(t)}')" style="background:none;border:none;color:#7c3aed;cursor:pointer;font-size:.9rem;line-height:1;padding:0">×</button>
      </span>`).join('');
    document.getElementById('fTechStack').value = JSON.stringify(techTags);
  }

  /* ── IP toggle ── */
  window.toggleIpDesc = function() {
    document.getElementById('ipDescGroup').style.display =
      document.getElementById('fHasIP').checked ? '' : 'none';
  };

  /* ── Submit ── */
  window.submitApplication = async function() {
    const isTech   = selectedSector === 'tech';
    const projName = document.getElementById('fProjName').value.trim();
    const desc     = document.getElementById('fDesc').value.trim();

    if (!projName || !desc) {
      const el = document.getElementById('formError');
      el.textContent='يرجى تعبئة اسم المشروع والوصف'; el.style.display='block';
      setTimeout(()=>el.style.display='none',4000); return;
    }

    if (isTech && !document.getElementById('fTRL').value) {
      const el = document.getElementById('formError');
      el.textContent='يرجى تحديد مستوى الجاهزية التقنية (TRL) للحاضنة التقنية'; el.style.display='block';
      setTimeout(()=>el.style.display='none',4000); return;
    }

    const payload = {
      incubator_id:          selectedId,
      project_name:          projName,
      project_sector:        document.getElementById('fSector').value||null,
      business_stage:        document.getElementById('fStage').value,
      project_description:   desc,
      problem_statement:     document.getElementById('fProblem').value.trim()||null,
      target_market:         document.getElementById('fMarket').value.trim()||null,
      team_size:             Number(document.getElementById('fTeam').value||1),
      has_prototype:         document.getElementById('fProto').checked,
      has_revenue:           document.getElementById('fRev').checked,
      funding_needed:        parseFloat(document.getElementById('fFunding').value)||null,
      funding_stage:         document.getElementById('fFundStage').value||null,
      expected_jobs:         parseInt(document.getElementById('fJobs').value)||null,
      competitive_advantage: document.getElementById('fAdvantage').value.trim()||null,
    };

    if (isTech) {
      payload.tech_readiness_level = parseInt(document.getElementById('fTRL').value)||null;
      payload.revenue_model        = document.getElementById('fRevenueModel').value||null;
      payload.target_platform      = document.getElementById('fPlatform').value||null;
      payload.demo_url             = document.getElementById('fDemoUrl').value.trim()||null;
      payload.github_url           = document.getElementById('fGithub').value.trim()||null;
      payload.has_ip               = document.getElementById('fHasIP').checked;
      payload.ip_description       = document.getElementById('fHasIP').checked ? (document.getElementById('fIPDesc').value.trim()||null) : null;
      payload.tech_stack           = techTags.length ? techTags : null;
    }

    const r = await fetch(`${base}/incubation/apply`, {
      method:'POST',
      headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });
    const j = await r.json();
    if (r.ok) {
      document.getElementById('step2').style.display='none';
      document.getElementById('step3').style.display='';
      document.getElementById('step2-ind').classList.replace('active','done');
      document.getElementById('step3-ind').classList.add('active');
    } else {
      const el = document.getElementById('formError');
      el.textContent = Object.values(j.errors??{}).flat()[0]||j.message||'خطأ في الإرسال';
      el.style.display='block'; setTimeout(()=>el.style.display='none',5000);
    }
  };

  try {
    await load();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(err) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${err.message}</div>`;
  }
});
</script>
</body>
</html>
