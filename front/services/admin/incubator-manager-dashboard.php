<?php
$basePath       = '../../';
$pageTitle      = 'لوحة مدير الحاضنة';
$activePage     = 'incubator-manager-dashboard';
$dashboardRole  = 'incubator_manager';
$sidebarTitle   = 'SMEDA — الحاضنات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<style>body{background:#f5f3ff;margin:0;}</style>
</head>
<body>
<div class="ds-layout">

<?php include __DIR__ . '/../../includes/partials/dashboard-sidebar.php'; ?>

<div class="ds-main">
  <div class="ds-topbar">
    <button class="ds-hamburger" id="dsHamburger"><i class="bi bi-list"></i></button>
    <div class="ds-topbar-title" id="dsTopbarTitle">لوحة مدير الحاضنة</div>
    <div class="ds-topbar-right">
      <div class="ds-topbar-user">
        <span id="dsTopbarName"></span>
        <div class="ds-topbar-avatar" id="dsTopbarAvatar">م</div>
      </div>
    </div>
  </div>

  <div class="ds-content">

    <!-- Hero -->
    <div style="background:linear-gradient(135deg,#1e1b4b,#4c1d95,#7c3aed);color:#fff;border-radius:20px;padding:22px 26px;margin-bottom:20px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="position:absolute;font-size:12rem;opacity:.04;left:-10px;top:-30px;line-height:1;pointer-events:none">🏢</div>
      <div style="position:relative;z-index:1">
        <div style="font-size:.75rem;font-weight:800;opacity:.65;letter-spacing:.5px;margin-bottom:4px">SMEDA — إدارة الحاضنات</div>
        <h1 style="font-size:1.3rem;font-weight:900;margin:0 0 3px" id="heroGreet">مرحباً...</h1>
        <p style="opacity:.72;font-size:.82rem;margin:0" id="heroInc">جاري تحميل بيانات الحاضنة...</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:1">
        <button onclick="openModal('addProgramModal')" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:11px;font-size:.82rem;font-weight:700;cursor:pointer"><i class="bi bi-plus-circle"></i>برنامج جديد</button>
        <button onclick="openModal('addSessionModal')" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:#fff;color:#7c3aed;border:none;border-radius:11px;font-size:.82rem;font-weight:800;cursor:pointer"><i class="bi bi-calendar-plus-fill"></i>جلسة جديدة</button>
      </div>
    </div>

    <!-- KPIs -->
    <div class="ds-kpi-grid">
      <?php foreach([
        ['kPending','⏳','طلبات معلقة','#b45309'],
        ['kActive','✅','مشاريع نشطة','#15803d'],
        ['kGraduated','🏆','تخرّجت','#7c3aed'],
        ['kSessions','📅','جلسات الشهر',null],
        ['kReports','📊','تقارير',null],
        ['kPrograms','🎓','برامج',null],
        ['kCapacity','📦','الطاقة',null],
        ['kRevenue','💰','إيرادات (ألف)','#15803d'],
      ] as [$id,$ic,$lbl,$col]):?>
      <div class="ds-kpi">
        <div class="ds-kpi-icon"><?php echo $ic;?></div>
        <div class="ds-kpi-val" id="<?php echo $id;?>" <?php echo $col?"style='color:$col'":'';?>>—</div>
        <div class="ds-kpi-lbl"><?php echo $lbl;?></div>
      </div>
      <?php endforeach;?>
    </div>

    <div class="ds-two-col">
      <div>
        <!-- Pending applications -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-inbox-fill"></i> الطلبات المعلقة</div>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>المتقدم</th><th>المشروع</th><th>المرحلة المطلوبة</th><th>إجراء</th></tr></thead>
              <tbody id="pendingTbody"><tr><td colspan="4" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- Active projects -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-kanban-fill"></i> المشاريع النشطة</div>
            <a href="<?php echo $basePath;?>services/incubation/incubated-projects.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div id="projectsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
            <div style="grid-column:1/-1;text-align:center;color:#9ca3af;padding:18px">جاري التحميل...</div>
          </div>
        </div>

        <!-- Sessions -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-calendar-check-fill"></i> جلسات الإرشاد</div>
            <a href="<?php echo $basePath;?>services/incubation/project-mentoring.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>المشروع</th><th>الموضوع</th><th>التاريخ</th><th>الحالة</th></tr></thead>
              <tbody id="sessionsTbody"><tr><td colspan="4" style="text-align:center;padding:20px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-building"></i> معلومات الحاضنة</div>
          <div id="incInfoEl"><div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:12px">جاري التحميل...</div></div>
        </div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-pie-chart-fill"></i> توزيع المراحل</div>
          <div id="stageChart" style="display:flex;flex-direction:column;gap:6px"></div>
        </div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-grid-fill"></i> روابط سريعة</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <?php foreach([
              [$basePath.'services/incubation/incubated-projects.php','bi-kanban-fill','المشاريع'],
              [$basePath.'services/incubation/project-mentoring.php','bi-people-fill','الجلسات'],
              [$basePath.'services/incubation/incubation-reports.php','bi-bar-chart-fill','التقارير'],
              [$basePath.'services/incubation/success-stories.php','bi-trophy-fill','قصص النجاح'],
            ] as [$href,$ic,$lbl]):?>
            <a href="<?php echo $href;?>" style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:12px 6px;background:var(--ds-soft);border-radius:12px;text-decoration:none;color:var(--ds-primary);font-size:.75rem;font-weight:800;text-align:center;border:1.5px solid var(--ds-border)">
              <i class="bi <?php echo $ic;?>" style="font-size:1.1rem"></i><?php echo $lbl;?>
            </a>
            <?php endforeach;?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Add Program Modal -->
<div class="ds-modal-overlay" id="addProgramModal">
  <div class="ds-modal" style="max-width:500px">
    <div class="ds-modal-head"><h3><i class="bi bi-mortarboard-fill"></i> إضافة برنامج</h3><button class="ds-modal-close" onclick="closeModal('addProgramModal')"><i class="bi bi-x-lg"></i></button></div>
    <div class="ds-modal-body">
      <div style="display:flex;flex-direction:column;gap:12px">
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">اسم البرنامج <span style="color:red">*</span></label><input id="progName" type="text" class="ds-fi" style="width:100%" placeholder="اسم البرنامج..."></div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">النوع</label><select id="progType" class="ds-fs" style="width:100%"><option value="training">تدريب</option><option value="mentoring">إرشاد</option><option value="funding">تمويل</option><option value="networking">تشبيك</option></select></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">تاريخ البداية</label><input id="progStart" type="date" class="ds-fi" style="width:100%"></div>
          <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">تاريخ النهاية</label><input id="progEnd" type="date" class="ds-fi" style="width:100%"></div>
        </div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">الوصف</label><textarea id="progDesc" class="ds-fi" rows="3" style="width:100%;resize:vertical" placeholder="وصف البرنامج..."></textarea></div>
        <button onclick="saveProgram()" style="background:linear-gradient(135deg,var(--ds-primary),var(--ds-accent));color:#fff;border:none;border-radius:12px;padding:11px;font-size:.9rem;font-weight:800;cursor:pointer;width:100%"><i class="bi bi-plus-circle-fill"></i> حفظ البرنامج</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Session Modal -->
<div class="ds-modal-overlay" id="addSessionModal">
  <div class="ds-modal" style="max-width:500px">
    <div class="ds-modal-head"><h3><i class="bi bi-calendar-plus-fill"></i> إضافة جلسة إرشاد</h3><button class="ds-modal-close" onclick="closeModal('addSessionModal')"><i class="bi bi-x-lg"></i></button></div>
    <div class="ds-modal-body">
      <div style="display:flex;flex-direction:column;gap:12px">
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">المشروع <span style="color:red">*</span></label><select id="sessProject" class="ds-fs" style="width:100%"><option value="">-- اختر المشروع --</option></select></div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">الموضوع <span style="color:red">*</span></label><input id="sessTopic" type="text" class="ds-fi" style="width:100%" placeholder="موضوع الجلسة..."></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">التاريخ</label><input id="sessDate" type="date" class="ds-fi" style="width:100%"></div>
          <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">المدة (دقيقة)</label><input id="sessDuration" type="number" class="ds-fi" style="width:100%" placeholder="60" min="15"></div>
        </div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">ملاحظات</label><textarea id="sessNotes" class="ds-fi" rows="3" style="width:100%;resize:vertical" placeholder="ملاحظات الجلسة..."></textarea></div>
        <button onclick="saveSession()" style="background:linear-gradient(135deg,var(--ds-primary),var(--ds-accent));color:#fff;border:none;border-radius:12px;padding:11px;font-size:.9rem;font-weight:800;cursor:pointer;width:100%"><i class="bi bi-calendar-check-fill"></i> حفظ الجلسة</button>
      </div>
    </div>
  </div>
</div>

<!-- Review Modal -->
<div class="ds-modal-overlay" id="reviewModal">
  <div class="ds-modal" style="max-width:480px">
    <div class="ds-modal-head"><h3>مراجعة الطلب</h3><button class="ds-modal-close" onclick="closeModal('reviewModal')"><i class="bi bi-x-lg"></i></button></div>
    <div class="ds-modal-body">
      <div id="reviewProjName" style="font-weight:800;margin-bottom:14px;color:var(--ds-primary)"></div>
      <div style="background:var(--ds-soft);border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:10px">
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">القرار <span style="color:red">*</span></label>
          <select id="reviewStatus" class="ds-fs" style="width:100%"><option value="approved">قبول</option><option value="rejected">رفض</option><option value="pending">إعادة للمراجعة</option></select></div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">المرحلة</label>
          <select id="reviewStage" class="ds-fs" style="width:100%"><option value="idea">فكرة</option><option value="validation">تحقق</option><option value="development">تطوير</option><option value="testing">اختبار</option><option value="launch">إطلاق</option></select></div>
        <div><label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:5px">ملاحظات</label>
          <textarea id="reviewNotes" class="ds-fi" rows="3" style="width:100%;resize:vertical" placeholder="ملاحظات القرار..."></textarea></div>
        <button onclick="submitReview()" style="background:linear-gradient(135deg,var(--ds-primary),var(--ds-accent));color:#fff;border:none;border-radius:12px;padding:11px;font-size:.9rem;font-weight:800;cursor:pointer;width:100%"><i class="bi bi-send-fill"></i> حفظ القرار</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath;?>assets/js/modules/dashboard-sidebar.js?v=2.3"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  const allowed = ['incubator_manager','admin','super_admin','general_director','system_admin'];
  if (!allowed.some(r => window.AppAuth.hasRole(r))) { location.href='<?php echo $basePath;?>'; return; }
  const user = window.AppAuth.getUser();
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, 'Content-Type':'application/json', Accept:'application/json' });
  const E    = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const STAGE_LABEL = {idea:'فكرة',validation:'تحقق',development:'تطوير',testing:'اختبار',launch:'إطلاق',growth:'نمو',graduated:'تخرّج'};
  const STAGE_COLOR = {idea:'#6b7280',validation:'#b45309',development:'#0369a1',testing:'#7c3aed',launch:'#15803d',growth:'#be123c',graduated:'#166534'};

  document.getElementById('heroGreet').textContent = `مرحباً، ${user?.name||'مدير الحاضنة'} 👋`;

  let myInc = null, reviewId = null;

  /* Find my incubator */
  const rIncs = await fetch(`${BASE}/incubators?per_page=100`, { headers:H() });
  if (rIncs.ok) {
    const d = await rIncs.value?.json?.() || await rIncs.json();
    const all = d.data||d||[];
    myInc = all.find(i=>i.manager_user_id==user?.id) || all[0];
  }

  if (myInc) {
    document.getElementById('heroInc').textContent = myInc.name;
    document.getElementById('dsTopbarTitle').textContent = myInc.name;
    document.getElementById('kCapacity').textContent = `${myInc.active_projects_count||0} / ${myInc.capacity||'—'}`;
    document.getElementById('incInfoEl').innerHTML = `
      <div style="display:flex;flex-direction:column;gap:8px">
        ${[['القطاع',myInc.sector],['المحافظة',myInc.governorate],['الحالة',myInc.status==='active'?'🟢 نشطة':'🔴 غير نشطة'],['الطاقة',`${myInc.capacity} مشروع`]].map(([l,v])=>`
          <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed var(--ds-border);font-size:.84rem">
            <span style="color:var(--c-muted);font-weight:600">${l}</span><span style="font-weight:800">${E(v||'—')}</span>
          </div>`).join('')}
      </div>`;
  } else {
    document.getElementById('heroInc').textContent = 'لم يتم تعيين حاضنة';
    document.getElementById('incInfoEl').innerHTML='<div style="color:#9ca3af;text-align:center;padding:12px;font-size:.84rem">لا توجد حاضنة مرتبطة</div>';
  }

  /* Parallel data load */
  const incId = myInc?.id;
  const [rApps, rProj, rSess, rStats] = await Promise.allSettled([
    fetch(`${BASE}/incubation/applications?status=pending${incId?`&incubator_id=${incId}`:''}&per_page=8`, { headers:H() }),
    fetch(`${BASE}/incubation/projects?status=active${incId?`&incubator_id=${incId}`:''}&per_page=6`, { headers:H() }),
    fetch(`${BASE}/incubation/sessions${incId ? `?incubator_id=${incId}&per_page=6` : '?per_page=6'}`, { headers:H() }),
    fetch(`${BASE}/incubation/stats${incId?`?incubator_id=${incId}`:''}`, { headers:H() }),
  ]);

  if (rApps.status==='fulfilled' && rApps.value.ok) {
    const d = await rApps.value.json(); const apps = d.data||[];
    document.getElementById('kPending').textContent = d.total||apps.length;
    document.getElementById('pendingTbody').innerHTML = apps.length
      ? apps.map(a=>`<tr>
          <td style="font-weight:700">${E(a.applicant?.name||'—')}</td>
          <td style="font-size:.82rem">${E(a.project_name||'—')}</td>
          <td style="font-size:.8rem">${E(STAGE_LABEL[a.stage]||a.stage||'—')}</td>
          <td><button onclick="openReview(${a.id},'${E(a.project_name||'')}','${E(a.applicant?.name||'')}')" style="background:var(--ds-soft);color:var(--ds-primary);border:1.5px solid var(--ds-border);border-radius:9px;padding:5px 11px;font-size:.78rem;font-weight:700;cursor:pointer"><i class="bi bi-clipboard2-check-fill"></i> مراجعة</button></td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:18px">لا توجد طلبات معلقة</td></tr>';
  }

  if (rProj.status==='fulfilled' && rProj.value.ok) {
    const d = await rProj.value.json(); const projs = d.data||[];
    document.getElementById('kActive').textContent = d.total||projs.length;
    /* Fill session project select */
    const sel = document.getElementById('sessProject');
    projs.forEach(p=>{ const o=document.createElement('option'); o.value=p.id; o.textContent=p.name||p.project_name; sel.appendChild(o); });
    document.getElementById('projectsGrid').innerHTML = projs.length
      ? projs.map(p=>`
          <div style="background:#fafaf9;border-radius:13px;padding:12px;border:1.5px solid var(--ds-border)">
            <div style="font-weight:800;font-size:.88rem;margin-bottom:4px">${E(p.name||p.project_name||'—')}</div>
            <span style="background:${STAGE_COLOR[p.stage]||'#6b7280'}22;color:${STAGE_COLOR[p.stage]||'#6b7280'};border-radius:20px;padding:2px 9px;font-size:.72rem;font-weight:800">${STAGE_LABEL[p.stage]||p.stage||'—'}</span>
            ${p.revenue?`<div style="font-size:.75rem;color:#15803d;font-weight:700;margin-top:6px">💰 ${Number(p.revenue).toLocaleString('ar')} ل.س</div>`:''}
          </div>`).join('')
      : '<div style="grid-column:1/-1;text-align:center;color:#9ca3af;padding:18px">لا توجد مشاريع نشطة</div>';
  }

  if (rSess.status==='fulfilled' && rSess.value.ok) {
    const d = await rSess.value.json(); const sessions = d.data||d||[];
    document.getElementById('kSessions').textContent = sessions.length;
    document.getElementById('sessionsTbody').innerHTML = sessions.length
      ? sessions.map(s=>`<tr>
          <td style="font-weight:700">${E(s.project?.name||'—')}</td>
          <td style="font-size:.82rem">${E(s.topic||s.subject||'—')}</td>
          <td style="font-size:.8rem">${s.session_date||s.date||'—'}</td>
          <td><span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:.72rem;font-weight:700">${s.status||'مجدولة'}</span></td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:18px">لا توجد جلسات</td></tr>';
  }

  if (rStats.status==='fulfilled' && rStats.value.ok) {
    const s = await rStats.value.json();
    document.getElementById('kGraduated').textContent = s.graduated||0;
    document.getElementById('kReports').textContent   = s.reports||0;
    document.getElementById('kPrograms').textContent  = s.active_programs||s.programs||0;
    document.getElementById('kRevenue').textContent   = s.total_revenue ? Math.round(s.total_revenue/1000) : 0;
    /* Stage chart */
    const byStage = s.by_stage||{};
    const maxV = Math.max(1,...Object.values(byStage));
    document.getElementById('stageChart').innerHTML = Object.entries(byStage).map(([st,cnt])=>`
      <div style="display:flex;align-items:center;gap:8px;font-size:.8rem">
        <div style="min-width:70px;font-weight:700;color:#6b7280;text-align:right">${STAGE_LABEL[st]||st}</div>
        <div style="flex:1;background:#f3f4f6;border-radius:20px;height:7px"><div style="width:${Math.round(cnt/maxV*100)}%;height:100%;border-radius:20px;background:linear-gradient(90deg,var(--ds-primary),var(--ds-accent));transition:width .5s"></div></div>
        <div style="font-weight:800;color:var(--ds-primary);min-width:20px">${cnt}</div>
      </div>`).join('') || '<div style="color:#9ca3af;font-size:.84rem;text-align:center">لا توجد بيانات</div>';
  }

  /* Modals */
  window.openModal  = id => document.getElementById(id).classList.add('open');
  window.closeModal = id => document.getElementById(id).classList.remove('open');
  document.querySelectorAll('.ds-modal-overlay').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open');}));

  window.openReview = (id, proj, applicant) => {
    reviewId = id;
    document.getElementById('reviewProjName').textContent = `📋 ${proj} — ${applicant}`;
    openModal('reviewModal');
  };
  window.submitReview = async () => {
    const res = await fetch(`${BASE}/incubation/applications/${reviewId}/review`, {
      method:'POST', headers:H(),
      body:JSON.stringify({ status:document.getElementById('reviewStatus').value, stage:document.getElementById('reviewStage').value, review_notes:document.getElementById('reviewNotes').value.trim() })
    });
    if (res.ok) { closeModal('reviewModal'); location.reload(); }
    else alert('حدث خطأ، يرجى المحاولة مرة أخرى');
  };
  window.saveProgram = async () => {
    const name = document.getElementById('progName').value.trim();
    if (!name) { alert('يرجى إدخال اسم البرنامج'); return; }
    if (!incId) { alert('لا توجد حاضنة مرتبطة'); return; }
    const res = await fetch(`${BASE}/incubators/${incId}/programs`, {
      method:'POST', headers:H(),
      body:JSON.stringify({ name, description:document.getElementById('progDesc').value, start_date:document.getElementById('progStart').value||null, end_date:document.getElementById('progEnd').value||null })
    });
    if (res.ok) { closeModal('addProgramModal'); document.getElementById('kPrograms').textContent = parseInt(document.getElementById('kPrograms').textContent||0)+1; }
    else alert('حدث خطأ في الحفظ');
  };
  window.saveSession = async () => {
    const topic = document.getElementById('sessTopic').value.trim();
    const projectId = document.getElementById('sessProject').value;
    if (!topic) { alert('يرجى إدخال موضوع الجلسة'); return; }
    if (!projectId) { alert('يرجى اختيار مشروع'); return; }
    const res = await fetch(`${BASE}/incubation/projects/${projectId}/sessions`, {
      method:'POST', headers:H(),
      body:JSON.stringify({ topic, session_date:document.getElementById('sessDate').value, duration_minutes:document.getElementById('sessDuration').value, notes:document.getElementById('sessNotes').value })
    });
    if (res.ok) { closeModal('addSessionModal'); document.getElementById('kSessions').textContent = parseInt(document.getElementById('kSessions').textContent||0)+1; }
    else alert('حدث خطأ في الحفظ');
  };
});
</script>
</body>
</html>
