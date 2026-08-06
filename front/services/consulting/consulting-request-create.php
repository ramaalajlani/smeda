<?php
$basePath  = '../../';
$pageTitle = 'طلب استشارة جديدة';
$activePage= 'consulting';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }

    .wizard-shell { max-width:780px; margin:30px auto; padding:0 14px; }

    /* Progress bar */
    .wizard-progress { display:flex; gap:0; margin-bottom:28px; background:#fff; border-radius:16px; padding:16px 20px; border:1px solid var(--c-border); box-shadow:0 8px 20px rgba(15,79,71,.06); }
    .wizard-step { flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; position:relative; }
    .wizard-step::after { content:""; position:absolute; top:18px; left:50%; right:-50%; height:2px; background:var(--c-border); z-index:0; }
    .wizard-step:last-child::after { display:none; }
    .step-circle { width:36px; height:36px; border-radius:50%; background:#e5e7eb; color:#9ca3af; font-weight:800; font-size:.85rem; display:flex; align-items:center; justify-content:center; z-index:1; transition:all .3s; }
    .step-circle.active { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; box-shadow:0 8px 16px rgba(23,148,123,.22); }
    .step-circle.done { background:var(--c-accent); color:#fff; }
    .step-label { font-size:.75rem; font-weight:700; color:var(--c-muted); text-align:center; }
    .step-label.active { color:var(--c-primary); }

    /* Cards */
    .wizard-card { background:#fff; border:1px solid var(--c-border); border-radius:22px; padding:28px; box-shadow:0 10px 28px rgba(15,79,71,.07); }
    .wizard-card-title { font-size:1.15rem; font-weight:800; color:var(--c-text); margin:0 0 20px; display:flex; align-items:center; gap:10px; }
    .wizard-card-title i { color:var(--c-primary); font-size:1.25rem; }

    .form-group { margin-bottom:18px; }
    .form-label { display:block; font-size:.85rem; font-weight:700; color:var(--c-muted); margin-bottom:6px; }
    .form-control { width:100%; border:1px solid var(--c-border); border-radius:12px; padding:11px 14px; font-size:.9rem; color:var(--c-text); background:#fff; transition:border-color .2s, box-shadow .2s; }
    .form-control:focus { outline:none; border-color:var(--c-primary); box-shadow:0 0 0 3px rgba(23,148,123,.1); }
    textarea.form-control { min-height:110px; resize:vertical; }

    /* Category cards */
    .cat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
    .cat-card { border:2px solid var(--c-border); border-radius:16px; padding:16px 14px; cursor:pointer; transition:all .2s; text-align:center; }
    .cat-card:hover { border-color:var(--c-accent); background:var(--c-soft); }
    .cat-card.selected { border-color:var(--c-primary); background:linear-gradient(135deg,rgba(23,148,123,.1),rgba(6,170,137,.07)); }
    .cat-card i { font-size:1.8rem; color:var(--c-primary); display:block; margin-bottom:8px; }
    .cat-card span { font-weight:700; font-size:.88rem; color:var(--c-text); }

    /* Nav buttons */
    .wizard-nav { display:flex; gap:12px; margin-top:22px; justify-content:space-between; }
    .btn-back { border:1px solid var(--c-border); background:#fff; border-radius:14px; padding:11px 22px; font-weight:700; cursor:pointer; color:var(--c-muted); transition:all .2s; }
    .btn-next { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:11px 28px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:opacity .2s; }
    .btn-next:hover { opacity:.92; }
    .btn-next:disabled { opacity:.55; cursor:not-allowed; }

    .alert-success-box { background:#dcfce7; border:1px solid #86efac; border-radius:16px; padding:20px; text-align:center; }
    .alert-success-box i { font-size:2.5rem; color:#16a34a; display:block; margin-bottom:10px; }

    @media (max-width:575px) { .cat-grid { grid-template-columns:1fr 1fr; } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/app-shell-open.php'; ?>
<div class="wizard-shell">

  <div style="margin-bottom:16px">
    <a href="consulting-requests-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem">
      <i class="bi bi-arrow-right"></i> العودة للطلبات
    </a>
  </div>

  <!-- Progress -->
  <div class="wizard-progress" id="wizardProgress">
    <div class="wizard-step">
      <div class="step-circle active" id="sc1">1</div>
      <div class="step-label active" id="sl1">نوع الاستشارة</div>
    </div>
    <div class="wizard-step">
      <div class="step-circle" id="sc2">2</div>
      <div class="step-label" id="sl2">تفاصيل الطلب</div>
    </div>
    <div class="wizard-step">
      <div class="step-circle" id="sc3">3</div>
      <div class="step-label" id="sl3">المرفقات</div>
    </div>
    <div class="wizard-step">
      <div class="step-circle" id="sc4">4</div>
      <div class="step-label" id="sl4">مراجعة وإرسال</div>
    </div>
  </div>

  <!-- Step 1: Category -->
  <div id="step1">
    <div class="wizard-card">
      <div class="wizard-card-title"><i class="bi bi-grid-fill"></i> اختر نوع الاستشارة</div>
      <div class="cat-grid" id="catGrid">
        <div style="text-align:center;padding:30px;color:var(--c-muted)"><i class="bi bi-hourglass-split"></i> جاري التحميل...</div>
      </div>
      <div class="form-group mt-4">
        <label class="form-label">نوع المشروع</label>
        <select class="form-control" id="requestType">
          <option value="existing">مشروع قائم</option>
          <option value="new_project">مشروع جديد قيد التأسيس</option>
          <option value="financing">أريد تمويل</option>
          <option value="classification">أريد تصنيف نشاطي</option>
        </select>
      </div>
      <div class="wizard-nav">
        <span></span>
        <button class="btn-next" id="step1Next" disabled>
          التالي <i class="bi bi-arrow-left"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Step 2: Details -->
  <div id="step2" style="display:none">
    <div class="wizard-card">
      <div class="wizard-card-title"><i class="bi bi-pencil-square"></i> تفاصيل الطلب</div>
      <div class="form-group">
        <label class="form-label">عنوان الطلب <span style="color:red">*</span></label>
        <input type="text" class="form-control" id="reqTitle" placeholder="مثال: أحتاج دراسة جدوى لمشروع مخبز صناعي">
      </div>
      <div class="form-group">
        <label class="form-label">وصف الحاجة أو المشكلة <span style="color:red">*</span></label>
        <textarea class="form-control" id="reqDescription" placeholder="اشرح ما تحتاجه بالتفصيل..."></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">اسم المشروع</label>
            <input type="text" class="form-control" id="projectName">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">النشاط الاقتصادي</label>
            <input type="text" class="form-control" id="economicActivity" placeholder="مثال: تصنيع مواد غذائية">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">الميزانية المتوقعة (من)</label>
            <input type="number" class="form-control" id="budgetMin" placeholder="ل.س" min="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">الميزانية المتوقعة (إلى)</label>
            <input type="number" class="form-control" id="budgetMax" placeholder="ل.س" min="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">المدة المطلوبة (بالأيام)</label>
            <input type="number" class="form-control" id="expectedDuration" placeholder="مثال: 14" min="1">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label class="form-label">المحافظة</label>
            <select class="form-control" id="governorateId">
              <option value="">اختر المحافظة</option>
            </select>
          </div>
        </div>
      </div>
      <div class="wizard-nav">
        <button class="btn-back" id="step2Back">السابق</button>
        <button class="btn-next" id="step2Next">التالي <i class="bi bi-arrow-left"></i></button>
      </div>
    </div>
  </div>

  <!-- Step 3: Attachments -->
  <div id="step3" style="display:none">
    <div class="wizard-card">
      <div class="wizard-card-title"><i class="bi bi-paperclip"></i> المرفقات (اختياري)</div>
      <p style="color:var(--c-muted);font-size:.88rem">يمكنك إرفاق وثائق تدعم طلبك (سجل تجاري، ترخيص، خطة عمل...)</p>
      <div id="attachStep3Note" style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:14px;margin-bottom:16px;display:none;font-size:.88rem;color:#166534">
        <i class="bi bi-info-circle me-2"></i>سيتم رفع المرفقات بعد حفظ الطلب.
      </div>
      <div class="form-group">
        <label class="form-label">اختر الملفات</label>
        <input type="file" class="form-control" id="attachFiles" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx">
        <small style="color:var(--c-muted);display:block;margin-top:6px">الأنواع المقبولة: PDF, Word, Excel, صور — الحد الأقصى 10MB لكل ملف</small>
      </div>
      <div id="fileList" style="display:flex;flex-direction:column;gap:8px;margin-top:10px"></div>
      <div class="wizard-nav">
        <button class="btn-back" id="step3Back">السابق</button>
        <button class="btn-next" id="step3Next">التالي <i class="bi bi-arrow-left"></i></button>
      </div>
    </div>
  </div>

  <!-- Step 4: Review & Submit -->
  <div id="step4" style="display:none">
    <div class="wizard-card">
      <div class="wizard-card-title"><i class="bi bi-eye-fill"></i> مراجعة وإرسال</div>
      <div id="reviewSummary" style="background:var(--c-soft);border-radius:16px;padding:20px;margin-bottom:20px;font-size:.9rem;line-height:2"></div>
      <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:12px;padding:14px;font-size:.86rem;color:#856404;margin-bottom:16px">
        <i class="bi bi-info-circle me-2"></i>بعد الإرسال سيقوم مدير الفرع بمراجعة الطلب خلال 48 ساعة وتوجيهه للمكاتب المختصة.
      </div>
      <div class="wizard-nav">
        <button class="btn-back" id="step4Back">السابق</button>
        <button class="btn-next" id="submitBtn">
          <i class="bi bi-send-fill"></i> حفظ وإرسال الطلب
        </button>
      </div>
    </div>
  </div>

  <!-- Success -->
  <div id="successBox" style="display:none">
    <div class="wizard-card">
      <div class="alert-success-box">
        <i class="bi bi-check-circle-fill"></i>
        <h4 style="font-weight:800;margin:0 0 8px">تم إرسال طلبك بنجاح!</h4>
        <p style="color:#166534;margin:0 0 16px">سيتواصل معك مدير الفرع خلال 48 ساعة.</p>
        <a href="consulting-requests-list.php" class="btn-next">
          <i class="bi bi-list-ul"></i> عرض طلباتي
        </a>
      </div>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_REQUEST_CREATE_ROLES,
  });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();

  let currentStep      = 1;
  let selectedCategory = null;
  let savedRequestId   = null;

  const CAT_ICONS = {
    'CON-01':'bi-graph-up-arrow','CON-02':'bi-file-text-fill','CON-03':'bi-megaphone-fill',
    'CON-04':'bi-calculator-fill','CON-05':'bi-file-earmark-check-fill','CON-06':'bi-tag-fill',
    'CON-07':'bi-bank2','CON-08':'bi-gear-fill','CON-09':'bi-flower1',
    'CON-10':'bi-laptop-fill','CON-11':'bi-diagram-3-fill','CON-12':'bi-arrow-up-right-circle-fill',
  };

  function setStep(n) {
    [1,2,3,4].forEach(i => {
      document.getElementById('step'+i).style.display = i===n ? 'block' : 'none';
      document.getElementById('sc'+i).className = 'step-circle ' + (i<n ? 'done' : i===n ? 'active' : '');
      document.getElementById('sl'+i).className = 'step-label ' + (i===n ? 'active' : '');
      if (i<n) document.getElementById('sc'+i).innerHTML = '<i class="bi bi-check-lg" style="font-size:.8rem"></i>';
    });
    currentStep = n;
  }

  // Load categories
  try {
    const cats = await fetch(`${base}/consulting/categories`, {
      headers: { 'Authorization': `Bearer ${token()}`, 'Accept':'application/json' }
    }).then(r=>r.json());

    document.getElementById('catGrid').innerHTML = cats.map(c => `
      <div class="cat-card" onclick="selectCat('${c.code}','${c.name_ar.replace(/'/g,'\\'')}')">
        <i class="bi ${CAT_ICONS[c.code]||'bi-headset'}"></i>
        <span>${c.name_ar}</span>
      </div>`).join('');
  } catch {}

  // Load governorates
  try {
    const govs = await fetch(`${base}/governorates`, {
      headers: { 'Authorization': `Bearer ${token()}`, 'Accept':'application/json' }
    }).then(r=>r.json());
    const sel = document.getElementById('governorateId');
    (govs.data||govs).forEach(g => {
      const o = document.createElement('option');
      o.value = g.id; o.textContent = g.name_ar;
      sel.appendChild(o);
    });
  } catch {}

  window.selectCat = function(code, name) {
    selectedCategory = code;
    document.querySelectorAll('.cat-card').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.getElementById('step1Next').disabled = false;
  };

  document.getElementById('step1Next').addEventListener('click', () => setStep(2));
  document.getElementById('step2Back').addEventListener('click',() => setStep(1));
  document.getElementById('step3Back').addEventListener('click',() => setStep(2));
  document.getElementById('step4Back').addEventListener('click',() => setStep(3));

  document.getElementById('step2Next').addEventListener('click', () => {
    if (!document.getElementById('reqTitle').value.trim()) { alert('العنوان مطلوب'); return; }
    if (!document.getElementById('reqDescription').value.trim()) { alert('الوصف مطلوب'); return; }
    setStep(3);
  });

  // File preview
  document.getElementById('attachFiles').addEventListener('change', function() {
    const list = document.getElementById('fileList');
    list.innerHTML = [...this.files].map(f => `
      <div style="display:flex;align-items:center;gap:8px;background:var(--c-soft);border-radius:10px;padding:8px 12px;font-size:.84rem;font-weight:600">
        <i class="bi bi-file-earmark-fill" style="color:var(--c-primary)"></i>
        ${f.name} <span style="color:var(--c-muted)">(${(f.size/1024).toFixed(0)} KB)</span>
      </div>`).join('');
  });

  document.getElementById('step3Next').addEventListener('click', () => {
    // Build review summary
    const TYPE_LABELS = {new_project:'مشروع جديد',existing:'مشروع قائم',financing:'تمويل',classification:'تصنيف نشاط'};
    document.getElementById('reviewSummary').innerHTML = `
      <strong>نوع الاستشارة:</strong> ${selectedCategory}<br>
      <strong>نوع المشروع:</strong> ${TYPE_LABELS[document.getElementById('requestType').value]}<br>
      <strong>العنوان:</strong> ${window.APP_HELPERS.e(document.getElementById('reqTitle').value)}<br>
      <strong>الوصف:</strong> ${window.APP_HELPERS.e(document.getElementById('reqDescription').value).substring(0,120)}...<br>
      ${document.getElementById('projectName').value ? `<strong>اسم المشروع:</strong> ${window.APP_HELPERS.e(document.getElementById('projectName').value)}<br>` : ''}
      ${document.getElementById('governorateId').value ? `<strong>المحافظة:</strong> ${document.getElementById('governorateId').options[document.getElementById('governorateId').selectedIndex].text}<br>` : ''}
    `;
    setStep(4);
  });

  document.getElementById('submitBtn').addEventListener('click', async () => {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الإرسال...';

    try {
      const body = {
        category_code:          selectedCategory,
        request_type:           document.getElementById('requestType').value,
        title:                  document.getElementById('reqTitle').value,
        description:            document.getElementById('reqDescription').value,
        project_name:           document.getElementById('projectName').value || undefined,
        economic_activity:      document.getElementById('economicActivity').value || undefined,
        budget_min:             document.getElementById('budgetMin').value || undefined,
        budget_max:             document.getElementById('budgetMax').value || undefined,
        expected_duration_days: document.getElementById('expectedDuration').value || undefined,
        governorate_id:         document.getElementById('governorateId').value || undefined,
      };

      const res = await fetch(`${base}/consulting/requests`, {
        method:'POST',
        headers: { 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json', 'Accept':'application/json' },
        body: JSON.stringify(body),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'خطأ');

      savedRequestId = json.data?.id;

      // رفع المرفقات
      const files = document.getElementById('attachFiles').files;
      if (savedRequestId && files.length) {
        for (const file of files) {
          const fd = new FormData();
          fd.append('file', file);
          fd.append('stage', 'request');
          await fetch(`${base}/consulting/requests/${savedRequestId}/attachments`, {
            method:'POST',
            headers: { 'Authorization':`Bearer ${token()}` },
            body: fd,
          });
        }
      }

      // إرسال الطلب (تغيير الحالة من draft إلى submitted)
      if (savedRequestId) {
        await fetch(`${base}/consulting/requests/${savedRequestId}/submit`, {
          method:'POST',
          headers: { 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' },
        });
      }

      document.getElementById('step4').style.display = 'none';
      document.getElementById('wizardProgress').style.display = 'none';
      document.getElementById('successBox').style.display = 'block';

    } catch (e) {
      alert('حدث خطأ: ' + e.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send-fill"></i> حفظ وإرسال الطلب';
    }
  });
});
</script>
</body>
</html>
