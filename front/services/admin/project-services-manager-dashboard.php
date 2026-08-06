<?php
$basePath       = '../../';
$pageTitle      = 'لوحة مدير خدمات المشروعات';
$activePage     = 'project-services-manager-dashboard';
$dashboardRole  = 'project_services_manager';
$sidebarTitle   = 'SMEDA — خدمات المشروعات';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<style>body{background:#effcfa;margin:0;}</style>
</head>
<body>
<div class="ds-layout">

<?php include __DIR__ . '/../../includes/partials/dashboard-sidebar.php'; ?>

<div class="ds-main">
  <div class="ds-topbar">
    <button class="ds-hamburger" id="dsHamburger"><i class="bi bi-list"></i></button>
    <div class="ds-topbar-title" id="dsTopbarTitle">لوحة مدير خدمات المشروعات</div>
    <div class="ds-topbar-right">
      <div class="ds-topbar-user">
        <span id="dsTopbarName"></span>
        <div class="ds-topbar-avatar" id="dsTopbarAvatar">م</div>
      </div>
    </div>
  </div>

  <div class="ds-content">

    <!-- Hero -->
    <div style="background:linear-gradient(135deg,#042f2e,#0a4f4a,#0f766e);color:#fff;border-radius:20px;padding:22px 26px;margin-bottom:20px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="position:absolute;font-size:12rem;opacity:.05;left:-10px;top:-30px;line-height:1;pointer-events:none">🗂️</div>
      <div style="position:relative;z-index:1">
        <div style="font-size:.75rem;font-weight:800;opacity:.65;letter-spacing:.5px;margin-bottom:4px">SMEDA — نافذة خدمات المشروعات</div>
        <h1 style="font-size:1.3rem;font-weight:900;margin:0 0 3px" id="heroGreet">مرحباً...</h1>
        <p style="opacity:.72;font-size:.82rem;margin:0">إدارة كاملة للتدريب والتأهيل، الاستشارات، المراكز، والحقائب التدريبية</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:1">
        <a href="<?php echo $basePath;?>services/training/program-bank-form.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:11px;font-size:.82rem;font-weight:700;text-decoration:none"><i class="bi bi-plus-circle"></i>حقيبة جديدة</a>
        <a href="<?php echo $basePath;?>services/consulting/consulting-admin-dashboard.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:#fff;color:#0f766e;border:none;border-radius:11px;font-size:.82rem;font-weight:800;text-decoration:none"><i class="bi bi-clipboard-data-fill"></i>لوحة الاستشارات</a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="ds-kpi-grid">
      <?php foreach([
        ['kPrograms','📚','برامج تدريبية',null],
        ['kCourses','🎓','دورات',null],
        ['kCenters','🏫','مراكز تدريبية',null],
        ['kKits','📦','حقائب معتمدة','#15803d'],
        ['kKitsReview','🔎','حقائب قيد المراجعة','#b45309'],
        ['kConsulting','💬','طلبات استشارة',null],
        ['kOffices','🏢','مكاتب استشارية',null],
        ['kTrainees','👥','متدربون',null],
      ] as [$id,$ic,$lbl,$col]):?>
      <div class="ds-kpi">
        <div class="ds-kpi-icon"><?php echo $ic;?></div>
        <div class="ds-kpi-val" id="<?php echo $id;?>" <?php echo $col?"style='color:$col'":'';?>>—</div>
        <div class="ds-kpi-lbl"><?php echo $lbl;?></div>
      </div>
      <?php endforeach;?>
    </div>

    <!-- Domain cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;margin-bottom:20px">
      <?php foreach([
        ['التدريب والتأهيل','bi-mortarboard-fill','#0f766e','برامج، دورات، مدربون، متدربون وشهادات',[
          [$basePath.'services/training/training-programs-list.php','البرامج'],
          [$basePath.'services/training/training-courses-list.php','الدورات'],
          [$basePath.'services/training/training-trainers-list.php','المدربون'],
          [$basePath.'services/training/training-certificates-list.php','الشهادات'],
        ]],
        ['المراكز التدريبية','bi-buildings-fill','#0369a1','إدارة المراكز ومراجعة طلبات التسجيل',[
          [$basePath.'services/training/training-centers-list.php','قائمة المراكز'],
          [$basePath.'services/training/registration-requests-review.php','طلبات التسجيل'],
        ]],
        ['الحقائب التدريبية','bi-collection-fill','#7c3aed','بنك الحقائب واعتماد المحتوى التدريبي',[
          [$basePath.'services/training/program-bank-dashboard.php','بنك الحقائب'],
          [$basePath.'services/training/program-bank-list.php','كل الحقائب'],
          [$basePath.'services/training/program-bank-form.php','حقيبة جديدة'],
        ]],
        ['الاستشارات','bi-chat-left-text-fill','#d97706','طلبات الاستشارة والمكاتب الاستشارية',[
          [$basePath.'services/consulting/consulting-requests-list.php','طلبات الاستشارة'],
          [$basePath.'services/consulting/consulting-offices-list.php','المكاتب'],
          [$basePath.'services/consulting/consulting-admin-dashboard.php','لوحة الاستشارات'],
        ]],
        ['الخريطة والاحتياجات','bi-map-fill','#b45309','خريطة الاحتياجات وإدخال/تدقيق البيانات',[
          [$basePath.'services/gis/needs-map.php','الخريطة'],
          [$basePath.'services/gis/needs-dashboard.php','لوحة الاحتياجات'],
          [$basePath.'services/gis/data-entry-dashboard.php','إدخال البيانات'],
          [$basePath.'services/gis/data-reviewer-dashboard.php','تدقيق البيانات'],
        ]],
        ['الفروع والحسابات','bi-people-fill','#1d4ed8','محافظون، مدخلو بيانات، ومدققون حسب المحافظة والفرع',[
          [$basePath.'services/admin/my-children.php','حسابات المحافظين والمدخلين'],
          [$basePath.'services/admin/admin-branches.php','المحافظات والفروع'],
        ]],
      ] as [$title,$icon,$color,$desc,$links]):?>
      <div class="ds-card" style="border-top:3px solid <?php echo $color;?>">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <div style="width:40px;height:40px;border-radius:12px;background:<?php echo $color;?>18;color:<?php echo $color;?>;display:flex;align-items:center;justify-content:center;font-size:1.2rem"><i class="bi <?php echo $icon;?>"></i></div>
          <div style="font-weight:900;font-size:.98rem"><?php echo $title;?></div>
        </div>
        <p style="color:var(--c-muted);font-size:.8rem;margin:0 0 10px;line-height:1.5"><?php echo $desc;?></p>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          <?php foreach($links as [$href,$lbl]):?>
          <a href="<?php echo $href;?>" style="font-size:.76rem;font-weight:700;color:<?php echo $color;?>;background:<?php echo $color;?>12;border:1px solid <?php echo $color;?>22;border-radius:9px;padding:5px 10px;text-decoration:none"><?php echo $lbl;?></a>
          <?php endforeach;?>
        </div>
      </div>
      <?php endforeach;?>
    </div>

    <div class="ds-two-col">
      <div>
        <!-- Latest training programs -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-book-fill"></i> أحدث البرامج التدريبية</div>
            <a href="<?php echo $basePath;?>services/training/training-programs-list.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>البرنامج</th><th>الرمز</th><th>النوع</th><th>الحالة</th></tr></thead>
              <tbody id="programsTbody"><tr><td colspan="4" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- Latest consulting requests -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-chat-left-text-fill"></i> أحدث طلبات الاستشارة</div>
            <a href="<?php echo $basePath;?>services/consulting/consulting-requests-list.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap">
            <table class="ds-tbl">
              <thead><tr><th>العنوان</th><th>التصنيف</th><th>التاريخ</th><th>الحالة</th></tr></thead>
              <tbody id="consultingTbody"><tr><td colspan="4" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- Training centers -->
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-buildings-fill"></i> المراكز التدريبية</div>
            <a href="<?php echo $basePath;?>services/training/training-centers-list.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div id="centersGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
            <div style="grid-column:1/-1;text-align:center;color:#9ca3af;padding:18px">جاري التحميل...</div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div>
        <!-- Program bank status -->
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-pie-chart-fill"></i> حالة الحقائب التدريبية</div>
          <div id="kitStatusChart" style="display:flex;flex-direction:column;gap:6px">
            <div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:12px">جاري التحميل...</div>
          </div>
        </div>

        <!-- Consulting status -->
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-bar-chart-fill"></i> حالة طلبات الاستشارة</div>
          <div id="consultingStatusChart" style="display:flex;flex-direction:column;gap:6px">
            <div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:12px">جاري التحميل...</div>
          </div>
        </div>

        <!-- Quick links -->
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-grid-fill"></i> روابط سريعة</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <?php foreach([
              [$basePath.'services/training/program-bank-form.php','bi-plus-circle-fill','حقيبة جديدة'],
              [$basePath.'services/training/registration-requests-review.php','bi-inbox-fill','طلبات التسجيل'],
              [$basePath.'services/training/training-certificates-approve.php','bi-patch-check-fill','اعتماد شهادات'],
              [$basePath.'services/consulting/consulting-requests-list.php','bi-chat-dots-fill','الاستشارات'],
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

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath;?>assets/js/modules/dashboard-sidebar.js?v=2.3"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  const allowed = ['project_services_manager','development_manager','local_development_manager','training_manager','admin','super_admin','general_director','system_admin'];
  if (!allowed.some(r => window.AppAuth.hasRole(r))) { location.href='<?php echo $basePath;?>'; return; }
  const user = window.AppAuth.getUser();
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H    = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E    = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const num  = v => Number(v||0).toLocaleString('ar');

  document.getElementById('heroGreet').textContent = `مرحباً، ${user?.name||'مدير خدمات المشروعات'} 👋`;

  const jget = async (path) => {
    try { const r = await fetch(`${BASE}${path}`, { headers:H() }); if (!r.ok) return null; return await r.json(); }
    catch(e){ return null; }
  };
  const listOf = d => Array.isArray(d) ? d : (d?.data || d?.items || []);
  const totalOf = (d, arr) => d?.total ?? d?.meta?.total ?? arr.length;

  const KIT_STATUS = {
    draft:['مسودة','#6b7280'], under_technical_review:['مراجعة فنية','#0369a1'],
    under_admin_review:['مراجعة إدارية','#7c3aed'], approved:['معتمدة','#15803d'],
    suspended:['موقوفة','#b91c1c'],
  };
  const CONS_STATUS = {
    submitted:['مقدمة','#0369a1'], needs_info:['بحاجة معلومات','#b45309'],
    in_progress:['قيد التنفيذ','#7c3aed'], completed:['مكتملة','#15803d'],
    rejected:['مرفوضة','#b91c1c'], draft:['مسودة','#6b7280'],
  };

  const bars = (elId, entries, labelMap) => {
    const el = document.getElementById(elId);
    const rows = entries.filter(([,n]) => n>0);
    if (!rows.length) { el.innerHTML = '<div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:8px">لا توجد بيانات</div>'; return; }
    const maxV = Math.max(1, ...rows.map(([,n])=>n));
    el.innerHTML = rows.map(([k,n])=>{
      const [lbl,color] = labelMap[k] || [k,'#6b7280'];
      return `<div style="display:flex;align-items:center;gap:8px;font-size:.8rem">
        <div style="min-width:92px;font-weight:700;color:#6b7280;text-align:right">${E(lbl)}</div>
        <div style="flex:1;background:#f3f4f6;border-radius:20px;height:7px"><div style="width:${Math.round(n/maxV*100)}%;height:100%;border-radius:20px;background:${color}"></div></div>
        <div style="font-weight:800;color:${color};min-width:22px">${num(n)}</div>
      </div>`;
    }).join('');
  };

  /* Parallel data load across all four domains */
  const [dPrograms, dCourses, dCenters, dKitStats, dConsList, dOffices, dTrainees, dConsStats] = await Promise.all([
    jget('/training-programs?per_page=6'),
    jget('/training-courses?per_page=1'),
    jget('/training-centers?per_page=6'),
    jget('/program-bank/stats'),
    jget('/consulting/requests?per_page=6'),
    jget('/consulting/offices?per_page=1'),
    jget('/training-kit-nominations?per_page=1'),
    jget('/consulting/requests/stats'),
  ]);

  /* ---- Training programs ---- */
  {
    const arr = listOf(dPrograms);
    document.getElementById('kPrograms').textContent = num(totalOf(dPrograms, arr));
    document.getElementById('programsTbody').innerHTML = arr.length
      ? arr.slice(0,6).map(p=>{
          const [lbl,color] = KIT_STATUS[p.bank_status] || ['—','#6b7280'];
          return `<tr>
            <td style="font-weight:700">${E(p.title||p.name||'—')}</td>
            <td style="font-size:.8rem;color:#6b7280">${E(p.code||'—')}</td>
            <td style="font-size:.8rem">${E(p.type||'—')}</td>
            <td><span style="background:${color}18;color:${color};border-radius:20px;padding:2px 9px;font-size:.72rem;font-weight:800">${lbl}</span></td>
          </tr>`;
        }).join('')
      : '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:18px">لا توجد برامج</td></tr>';
  }

  /* ---- Courses count ---- */
  document.getElementById('kCourses').textContent = num(totalOf(dCourses, listOf(dCourses)));

  /* ---- Centers ---- */
  {
    const arr = listOf(dCenters);
    document.getElementById('kCenters').textContent = num(totalOf(dCenters, arr));
    document.getElementById('centersGrid').innerHTML = arr.length
      ? arr.slice(0,6).map(c=>`
          <div style="background:#fafaf9;border-radius:13px;padding:12px;border:1.5px solid var(--ds-border)">
            <div style="font-weight:800;font-size:.86rem;margin-bottom:4px">${E(c.name||'—')}</div>
            <div style="font-size:.76rem;color:#6b7280"><i class="bi bi-geo-alt-fill"></i> ${E(c.governorate||c.city||'—')}</div>
            ${c.status?`<span style="display:inline-block;margin-top:6px;background:${c.status==='active'?'#dcfce7':'#f3f4f6'};color:${c.status==='active'?'#15803d':'#6b7280'};border-radius:20px;padding:2px 8px;font-size:.7rem;font-weight:700">${c.status==='active'?'نشط':E(c.status)}</span>`:''}
          </div>`).join('')
      : '<div style="grid-column:1/-1;text-align:center;color:#9ca3af;padding:18px">لا توجد مراكز</div>';
  }

  /* ---- Kits (program bank) stats ---- */
  {
    const s = dKitStats?.data || dKitStats || {};
    document.getElementById('kKits').textContent = num(s.approved);
    document.getElementById('kKitsReview').textContent = num(s.under_review);
    bars('kitStatusChart', [
      ['approved', s.approved||0],
      ['under_technical_review', 0],
      ['under_admin_review', s.under_review||0],
      ['draft', s.draft||0],
      ['suspended', s.suspended||0],
    ].filter(([k,n]) => !(k==='under_technical_review')), KIT_STATUS);
  }

  /* ---- Consulting requests ---- */
  {
    const arr = listOf(dConsList);
    document.getElementById('kConsulting').textContent = num(totalOf(dConsList, arr));
    document.getElementById('consultingTbody').innerHTML = arr.length
      ? arr.slice(0,6).map(r=>{
          const [lbl,color] = CONS_STATUS[r.status] || [r.status||'—','#6b7280'];
          return `<tr>
            <td style="font-weight:700">${E(r.title||r.subject||('#'+(r.id||'')))}</td>
            <td style="font-size:.8rem">${E(r.category?.name||r.category_name||'—')}</td>
            <td style="font-size:.78rem;color:#6b7280">${E((r.created_at||'').slice(0,10)||'—')}</td>
            <td><span style="background:${color}18;color:${color};border-radius:20px;padding:2px 9px;font-size:.72rem;font-weight:800">${lbl}</span></td>
          </tr>`;
        }).join('')
      : '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:18px">لا توجد طلبات</td></tr>';
  }

  /* ---- Offices & trainees counts ---- */
  document.getElementById('kOffices').textContent = num(totalOf(dOffices, listOf(dOffices)));
  document.getElementById('kTrainees').textContent = num(totalOf(dTrainees, listOf(dTrainees)));

  /* ---- Consulting status chart ---- */
  {
    let byStatus = {};
    if (dConsStats && (dConsStats.by_status || dConsStats.data?.by_status)) {
      byStatus = dConsStats.by_status || dConsStats.data.by_status;
    } else {
      listOf(dConsList).forEach(r => { byStatus[r.status] = (byStatus[r.status]||0)+1; });
    }
    bars('consultingStatusChart', Object.keys(CONS_STATUS).map(k => [k, byStatus[k]||0]), CONS_STATUS);
  }
});
</script>
</body>
</html>
