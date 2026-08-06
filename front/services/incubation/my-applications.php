<?php
$basePath  = '../../';
$pageTitle = 'طلباتي وملفي الريادي';
$activePage= 'incubation';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#7c3aed;--c-accent:#a78bfa;--c-soft:#f5f3ff;--c-border:rgba(124,58,237,.15);--c-text:#1e1b4b;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(124,58,237,.08);}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:linear-gradient(160deg,#f5f3ff,#ede9fe 60%,#e0e7ff 100%);font-family:'Segoe UI',Tahoma,sans-serif;color:var(--c-text);}
    a{text-decoration:none;color:inherit;}
    .pw{max-width:900px;margin:auto;padding:22px 14px 60px;}
    .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:12px;border:none;font-size:.88rem;font-weight:700;cursor:pointer;transition:all .15s;}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}
    .btn-primary:hover{opacity:.9;}
    .btn-outline{background:var(--c-soft);color:var(--c-primary);border:1.5px solid var(--c-border);}
    .btn-sm{padding:7px 13px;font-size:.8rem;}

    /* Profile header */
    .profile-banner{background:linear-gradient(135deg,#1e1b4b,#4c1d95,#7c3aed);color:#fff;border-radius:22px;padding:28px 30px;margin-bottom:22px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;}
    .avatar{width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.15);border:3px solid rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;}
    .profile-info h2{font-size:1.3rem;font-weight:900;margin-bottom:4px;}
    .profile-info p{font-size:.85rem;opacity:.8;}
    .profile-actions{margin-right:auto;display:flex;gap:10px;flex-wrap:wrap;}

    /* Tabs */
    .tabs{display:flex;gap:4px;background:#fff;border-radius:14px;padding:5px;border:1px solid var(--c-border);margin-bottom:20px;box-shadow:var(--c-shadow);}
    .tab{flex:1;padding:10px;border-radius:10px;border:none;background:none;font-size:.9rem;font-weight:700;cursor:pointer;color:var(--c-muted);transition:all .15s;text-align:center;}
    .tab.active{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;}

    /* Status timeline */
    .status-timeline{display:flex;gap:0;margin-bottom:20px;position:relative;}
    .status-timeline::before{content:"";position:absolute;top:18px;right:18px;left:18px;height:2px;background:var(--c-border);z-index:0;}
    .st-step{flex:1;text-align:center;position:relative;z-index:1;}
    .st-dot{width:36px;height:36px;border-radius:50%;border:2px solid var(--c-border);background:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;margin:0 auto 6px;transition:all .3s;}
    .st-step.done .st-dot{background:#dcfce7;border-color:#15803d;}
    .st-step.active .st-dot{background:var(--c-primary);border-color:var(--c-primary);box-shadow:0 0 0 4px rgba(124,58,237,.2);}
    .st-step.rejected .st-dot{background:#fee2e2;border-color:#dc2626;}
    .st-label{font-size:.7rem;font-weight:700;color:var(--c-muted);}
    .st-step.done .st-label{color:#15803d;}
    .st-step.active .st-label{color:var(--c-primary);}
    .st-step.rejected .st-label{color:#dc2626;}

    /* Card */
    .card{background:#fff;border:1px solid var(--c-border);border-radius:18px;padding:20px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-size:1rem;font-weight:800;margin-bottom:16px;color:var(--c-text);display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}

    /* Application card */
    .app-card{background:#fff;border:1.5px solid var(--c-border);border-radius:16px;padding:18px;margin-bottom:14px;transition:box-shadow .15s;}
    .app-card:hover{box-shadow:var(--c-shadow);}
    .app-card-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;gap:10px;}
    .app-card-name{font-size:.98rem;font-weight:900;}
    .app-card-meta{font-size:.78rem;color:var(--c-muted);margin-top:2px;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border-radius:20px;font-size:.76rem;font-weight:700;}
    .badge-pending{background:#fef3c7;color:#b45309;}
    .badge-under_review{background:#e0f2fe;color:#0369a1;}
    .badge-approved{background:#dcfce7;color:#15803d;}
    .badge-rejected{background:#fee2e2;color:#dc2626;}
    .badge-graduated{background:#f3e8ff;color:#7c3aed;}
    .badge-submitted{background:#fef3c7;color:#b45309;}
    .badge-draft{background:#f5f5f4;color:#78716c;}
    .badge-under_review{background:#e0f2fe;color:#0369a1;}
    .progress-bar-wrap{background:#f3f4f6;border-radius:20px;height:7px;margin:10px 0 6px;}
    .progress-bar-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--c-primary),var(--c-accent));transition:width .5s;}
    .stage-label{font-size:.75rem;color:var(--c-muted);font-weight:700;}
    .reviewer-note{background:var(--c-soft);border-radius:10px;padding:10px 12px;font-size:.83rem;color:var(--c-text);margin-top:10px;border-right:3px solid var(--c-primary);}

    /* Profile completeness */
    .completeness{background:var(--c-soft);border-radius:14px;padding:14px 16px;}
    .comp-bar-wrap{background:#e5e7eb;border-radius:20px;height:8px;margin:8px 0;}
    .comp-bar-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,#15803d,#4ade80);transition:width .5s;}
    .comp-label{font-size:.82rem;font-weight:700;color:var(--c-text);}
    .comp-hint{font-size:.76rem;color:var(--c-muted);margin-top:4px;}
    .comp-item{display:flex;align-items:center;gap:8px;font-size:.82rem;padding:5px 0;}
    .comp-item i{font-size:.9rem;}

    .empty{text-align:center;padding:40px 20px;color:var(--c-muted);}
    .empty i{font-size:2.5rem;opacity:.3;display:block;margin-bottom:10px;}
    .spin{display:inline-block;width:30px;height:30px;border:3px solid rgba(124,58,237,.2);border-top-color:var(--c-primary);border-radius:50%;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}
    .loading{text-align:center;padding:40px;}
  </style>
</head>
<body>
<div class="pw">

  <!-- Profile banner -->
  <div class="profile-banner">
    <div class="avatar" id="userAvatar">👤</div>
    <div class="profile-info">
      <h2 id="userName">جاري التحميل...</h2>
      <p id="userEmail">—</p>
    </div>
    <div class="profile-actions">
      <a href="<?php echo $basePath;?>services/incubation/entrepreneur-profile.php" class="btn btn-outline" style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
        <i class="bi bi-pencil-fill"></i> تعديل الاستبيان
      </a>
      <a href="<?php echo $basePath;?>services/incubation/incubators.php" class="btn btn-primary" style="background:rgba(255,255,255,.9);color:var(--c-primary)">
        <i class="bi bi-rocket-takeoff-fill"></i> تقدم لحاضنة
      </a>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab active" onclick="switchTab('profile',this)"><i class="bi bi-person-fill"></i> ملفي الريادي</button>
    <button class="tab" onclick="switchTab('applications',this)"><i class="bi bi-file-earmark-text-fill"></i> طلبات الحاضنات</button>
  </div>

  <!-- TAB: Profile -->
  <div id="tabProfile">
    <div class="card" id="profileCard">
      <div class="loading"><div class="spin"></div></div>
    </div>
  </div>

  <!-- TAB: Applications -->
  <div id="tabApplications" style="display:none">
    <div id="appsContainer">
      <div class="loading"><div class="spin"></div></div>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const user = window.AppAuth.getUser();
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json', 'Content-Type':'application/json' });
  const E    = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  document.getElementById('userName').textContent  = user?.name || '—';
  document.getElementById('userEmail').textContent = user?.email || '—';
  document.getElementById('userAvatar').textContent = (user?.name||'?')[0].toUpperCase();

  const STAGE_STEPS = ['idea','validation','development','testing','launch','growth','graduated'];
  const STAGE_LABELS = {idea:'فكرة',validation:'تحقق',development:'تطوير',testing:'اختبار',launch:'إطلاق',growth:'نمو',graduated:'تخرّج'};
  const STATUS_BADGE = {
    pending:'<span class="badge badge-pending">⏳ بانتظار المراجعة</span>',
    under_review:'<span class="badge badge-under_review">🔍 قيد المراجعة</span>',
    approved:'<span class="badge badge-approved">✅ مقبول</span>',
    rejected:'<span class="badge badge-rejected">❌ مرفوض</span>',
    active:'<span class="badge badge-approved">✅ نشط</span>',
    graduated:'<span class="badge badge-graduated">🎓 متخرج</span>',
  };

  window.switchTab = function(tab, el) {
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tabProfile').style.display      = tab==='profile'      ? '' : 'none';
    document.getElementById('tabApplications').style.display = tab==='applications' ? '' : 'none';
  };

  /* ── Load entrepreneur profile ── */
  async function loadProfile() {
    const res = await fetch(`${BASE}/entrepreneur/my-profile`, { headers:H() });
    const card = document.getElementById('profileCard');

    if (!res.ok || res.status===404) {
      card.innerHTML = `
        <div class="empty">
          <i class="bi bi-person-plus-fill"></i>
          <div style="font-weight:800;margin-bottom:8px">لم تقم بملء استبيان رائد الأعمال بعد</div>
          <div style="font-size:.85rem;margin-bottom:16px">أكمل الاستبيان حتى تتمكن من التقدم للحاضنات والحصول على الدعم المناسب</div>
          <a href="<?php echo $basePath;?>services/incubation/entrepreneur-profile.php" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill"></i> ملء الاستبيان الآن
          </a>
        </div>`;
      return;
    }

    const p = await res.json();
    if (!p) { card.innerHTML='<div class="empty"><i class="bi bi-inbox"></i> لا توجد بيانات</div>'; return; }

    /* Completeness score */
    const fields = ['full_name','governorate','phone','email','project_name','project_field',
      'executive_summary','elevator_pitch','readiness_stage','problem_description',
      'team_size_range','technologies','target_market'];
    const filled = fields.filter(f => p[f] && (Array.isArray(p[f]) ? p[f].length : true)).length;
    const pct    = Math.round(filled/fields.length*100);

    const STATUS_COLOR = {draft:'#78716c',submitted:'#b45309',under_review:'#0369a1',approved:'#15803d',rejected:'#dc2626'};
    const STATUS_TEXT  = {draft:'مسودة — لم تُرسل بعد',submitted:'مُرسل — بانتظار المراجعة',under_review:'قيد المراجعة',approved:'✅ مقبول',rejected:'❌ مرفوض'};

    card.innerHTML = `
      <div class="card-title"><i class="bi bi-person-badge-fill"></i> ملفي الريادي</div>

      <!-- Status -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
        <div style="font-weight:800;font-size:.95rem">${E(p.project_name)}</div>
        <div>
          <span style="background:${STATUS_COLOR[p.status]||'#78716c'}22;color:${STATUS_COLOR[p.status]||'#78716c'};border-radius:20px;padding:5px 13px;font-size:.8rem;font-weight:800">
            ${STATUS_TEXT[p.status]||p.status}
          </span>
        </div>
      </div>

      ${p.reviewer_notes ? `
      <div class="reviewer-note">
        <div style="font-size:.75rem;font-weight:800;color:var(--c-primary);margin-bottom:4px"><i class="bi bi-chat-square-text-fill"></i> ملاحظات المراجع</div>
        ${E(p.reviewer_notes)}
      </div>` : ''}

      <!-- Completeness -->
      <div class="completeness" style="margin-top:14px">
        <div class="comp-label">اكتمال الملف: <strong style="color:${pct>=80?'#15803d':pct>=50?'#b45309':'#dc2626'}">${pct}%</strong></div>
        <div class="comp-bar-wrap"><div class="comp-bar-fill" style="width:${pct}%"></div></div>
        <div class="comp-hint">${pct>=80?'ملفك ممتاز! اكتمل بشكل جيد':'أكمل معلوماتك لتحسين فرصك في القبول'}</div>
      </div>

      <!-- Key info grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:16px">
        ${[
          ['bi-geo-alt-fill','المحافظة',p.governorate||'—'],
          ['bi-cpu-fill','مجال المشروع',p.project_field||'—'],
          ['bi-speedometer2','مرحلة الجاهزية',p.readiness_stage||'—'],
          ['bi-people-fill','حجم الفريق',p.team_size_range||'—'],
          ['bi-globe2','السوق المستهدف',p.target_market||'—'],
          ['bi-currency-dollar','يبحث عن استثمار',p.seeking_investment?'نعم':'لا'],
        ].map(([ic,l,v])=>`
          <div style="background:#fafaf9;border-radius:12px;padding:10px 12px">
            <div style="font-size:.73rem;color:var(--c-muted);font-weight:700;margin-bottom:3px"><i class="bi ${ic}"></i> ${l}</div>
            <div style="font-size:.88rem;font-weight:800">${E(v)}</div>
          </div>`).join('')}
      </div>

      <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
        <a href="<?php echo $basePath;?>services/incubation/entrepreneur-profile.php" class="btn btn-primary">
          <i class="bi bi-pencil-fill"></i> ${p.status==='draft'?'إكمال الاستبيان':'تعديل الملف'}
        </a>
        ${p.status==='draft'?`
        <button class="btn btn-outline" onclick="submitProfile(${p.id})">
          <i class="bi bi-send-fill"></i> إرسال للمراجعة
        </button>`:''}
      </div>
    `;
  }

  window.submitProfile = async function(id) {
    if (!confirm('هل تريد إرسال الاستبيان للمراجعة؟')) return;
    const res = await fetch(`${BASE}/entrepreneur/profile/${id}`, {
      method:'PUT', headers:H(), body:JSON.stringify({status:'submitted'})
    });
    if (res.ok) loadProfile();
  };

  /* ── Load incubation applications ── */
  async function loadApplications() {
    const con = document.getElementById('appsContainer');

    /* We use /incubation/my-applications if it exists, otherwise /incubation/applications */
    const endpoints = [`${BASE}/incubation/my-applications`, `${BASE}/incubation/applications?user_id=me`];
    let apps = [];
    for (const ep of endpoints) {
      try {
        const r = await fetch(ep, { headers:H() });
        if (r.ok) { const d=await r.json(); apps=d.data||d||[]; break; }
      } catch(e) {}
    }

    if (!apps.length) {
      con.innerHTML = `
        <div class="card">
          <div class="empty">
            <i class="bi bi-file-earmark-plus"></i>
            <div style="font-weight:800;margin-bottom:8px">لا توجد طلبات سابقة</div>
            <div style="font-size:.85rem;margin-bottom:16px">يمكنك التقدم لأي حاضنة من قائمة الحاضنات</div>
            <a href="<?php echo $basePath;?>services/incubation/incubators.php" class="btn btn-primary">
              <i class="bi bi-search"></i> استعراض الحاضنات
            </a>
          </div>
        </div>`;
      return;
    }

    const stageIdx = s => STAGE_STEPS.indexOf(s);

    con.innerHTML = apps.map(app => {
      const si = stageIdx(app.stage||'idea');
      const pct= si>=0 ? Math.round((si+1)/STAGE_STEPS.length*100) : 0;
      return `
        <div class="app-card">
          <div class="app-card-head">
            <div>
              <div class="app-card-name">${E(app.incubator?.name||app.project_name||'—')}</div>
              <div class="app-card-meta">
                <i class="bi bi-calendar3"></i> ${app.created_at ? new Date(app.created_at).toLocaleDateString('ar-SA') : '—'}
                ${app.project_name ? ` &bull; مشروع: ${E(app.project_name)}` : ''}
              </div>
            </div>
            <div>${STATUS_BADGE[app.status]||`<span class="badge badge-pending">${E(app.status)}</span>`}</div>
          </div>

          ${(app.status==='approved'||app.status==='active') ? `
          <div>
            <div class="progress-bar-wrap">
              <div class="progress-bar-fill" style="width:${pct}%"></div>
            </div>
            <div class="stage-label">المرحلة الحالية: <strong>${STAGE_LABELS[app.stage]||app.stage||'—'}</strong> (${pct}% من الرحلة)</div>
          </div>

          <!-- Stage timeline -->
          <div class="status-timeline" style="margin-top:14px">
            ${STAGE_STEPS.map((s,i)=>`
              <div class="st-step ${i<si?'done':i===si?'active':''}">
                <div class="st-dot">${i<si?'✓':i===si?'●':''}</div>
                <div class="st-label">${STAGE_LABELS[s]}</div>
              </div>`).join('')}
          </div>` : ''}

          ${app.review_notes||app.notes ? `
          <div class="reviewer-note">
            <div style="font-size:.73rem;font-weight:800;color:var(--c-primary);margin-bottom:4px"><i class="bi bi-chat-square-text-fill"></i> ملاحظات</div>
            ${E(app.review_notes||app.notes)}
          </div>` : ''}
        </div>`;
    }).join('');
  }

  loadProfile();
  loadApplications();

  /* Switch tab triggers load on first visit */
  const origSwitch = window.switchTab;
  let appsLoaded = false;
  window.switchTab = function(tab, el) {
    origSwitch(tab, el);
    if (tab==='applications' && !appsLoaded) { appsLoaded=true; loadApplications(); }
  };
});
</script>
</body>
</html>
