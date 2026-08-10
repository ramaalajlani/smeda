<?php
$basePath       = '../../';
$pageTitle      = 'لوحة المدير العام';
$activePage     = 'general-director-dashboard';
$dashboardRole  = 'general_director';
$sidebarTitle   = 'SMEDA — الإدارة العامة';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head><?php include __DIR__ . '/../../includes/layout/head.php'; ?>
<style>body{background:#f7f5ec;margin:0;}</style>
</head>
<body>
<div class="ds-layout">

<?php include __DIR__ . '/../../includes/partials/dashboard-sidebar.php'; ?>

<div class="ds-main">
  <div class="ds-topbar">
    <button class="ds-hamburger" id="dsHamburger"><i class="bi bi-list"></i></button>
    <div class="ds-topbar-title" id="dsTopbarTitle">لوحة المدير العام</div>
    <div class="ds-topbar-right">
      <div class="ds-topbar-user"><span id="dsTopbarName"></span><div class="ds-topbar-avatar" id="dsTopbarAvatar">م</div></div>
    </div>
  </div>

  <div class="ds-content">

    <!-- Hero -->
    <div style="background:linear-gradient(135deg,#062824,#0F5F4F,#17947B);color:#fff;border-radius:20px;padding:22px 26px;margin-bottom:20px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="position:absolute;font-size:12rem;opacity:.05;left:-10px;top:-30px;line-height:1;pointer-events:none">🛡️</div>
      <div style="position:relative;z-index:1">
        <div style="font-size:.75rem;font-weight:800;opacity:.65;letter-spacing:.5px;margin-bottom:4px">SMEDA — الإدارة العامة الوطنية</div>
        <h1 style="font-size:1.3rem;font-weight:900;margin:0 0 3px" id="heroGreet">مرحباً...</h1>
        <p style="opacity:.72;font-size:.82rem;margin:0">إشراف شامل على التدريب والاستشارات والمراكز والحقائب والاحتياجات والحاضنات والتمويل والمستخدمين</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:1">
        <a href="<?php echo $basePath;?>services/gis/needs-dashboard.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:11px;font-size:.82rem;font-weight:700;text-decoration:none"><i class="bi bi-signpost-split-fill"></i>منظومة الاحتياجات</a>
        <a href="<?php echo $basePath;?>services/admin/admin-users.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:#fff;color:#0F5F4F;border:none;border-radius:11px;font-size:.82rem;font-weight:800;text-decoration:none"><i class="bi bi-people-fill"></i>المستخدمون والصلاحيات</a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="ds-kpi-grid">
      <?php foreach([
        ['kBranches','bi-bank','الفروع',null],['kUsers','bi-people-fill','المستخدمون',null],
        ['kPrograms','bi-journal-bookmark-fill','البرامج',null],['kCourses','bi-mortarboard-fill','الدورات',null],
        ['kCenters','bi-buildings-fill','المراكز',null],['kKits','bi-collection-fill','حقائب معتمدة','#15803d'],
        ['kTrainees','bi-people','المتدربون',null],['kConsulting','bi-chat-left-text-fill','طلبات استشارة',null],
        ['kNeeds','bi-geo-alt-fill','الاحتياجات',null],['kIncubators','bi-rocket-takeoff-fill','الحاضنات',null],
      ] as [$id,$ic,$lbl,$col]):?>
      <div class="ds-kpi">
        <div class="ds-kpi-icon" style="color:var(--ds-primary)"><i class="bi <?php echo $ic;?>"></i></div>
        <div class="ds-kpi-val" id="<?php echo $id;?>" <?php echo $col?"style='color:$col'":'';?>>—</div>
        <div class="ds-kpi-lbl"><?php echo $lbl;?></div>
      </div>
      <?php endforeach;?>
    </div>

    <!-- Domain cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;margin-bottom:20px">
      <?php foreach([
        ['التدريب والتأهيل','bi-mortarboard-fill','#0f766e','البرامج والدورات والمدربين والمتدربين والشهادات',[
          [$basePath.'services/training/training-programs-list.php','البرامج'],
          [$basePath.'services/training/training-courses-list.php','الدورات'],
          [$basePath.'services/training/training-trainers-list.php','المدربون'],
          [$basePath.'services/training/training-certificates-list.php','الشهادات'],
        ]],
        ['المراكز والحقائب','bi-buildings-fill','#7c3aed','المراكز التدريبية وبنك الحقائب والاعتماد',[
          [$basePath.'services/training/training-centers-list.php','المراكز'],
          [$basePath.'services/training/program-bank-dashboard.php','بنك الحقائب'],
          [$basePath.'services/training/program-bank-list.php','كل الحقائب'],
        ]],
        ['الاستشارات','bi-chat-left-text-fill','#d97706','طلبات الاستشارة والمكاتب الاستشارية',[
          [$basePath.'services/consulting/consulting-admin-dashboard.php','لوحة الاستشارات'],
          [$basePath.'services/consulting/consulting-requests-list.php','الطلبات'],
          [$basePath.'services/consulting/consulting-offices-list.php','المكاتب'],
        ]],
        ['منظومة الاحتياجات','bi-geo-alt-fill','#0369a1','الخريطة التفاعلية وسير المعالجة والتقارير',[
          [$basePath.'services/gis/needs-map.php','الخريطة'],
          [$basePath.'services/gis/needs-list.php','التفاصيل'],
          [$basePath.'services/gis/needs-dashboard.php','سير المعالجة'],
        ]],
        ['الحاضنات وريادة الأعمال','bi-rocket-takeoff-fill','#be123c','الحاضنات والمشاريع وقصص النجاح',[
          [$basePath.'services/incubation/incubators.php','الحاضنات'],
          [$basePath.'services/incubation/incubated-projects.php','المشاريع'],
          [$basePath.'services/incubation/success-stories.php','قصص النجاح'],
        ]],
        ["الإدارة والرقابة","bi-shield-lock-fill","#0F5F4F",'المستخدمون والأدوار والتقارير والتدقيق',[
          [$basePath.'services/admin/admin-users.php','المستخدمون'],
          [$basePath.'services/admin/admin-roles.php','الأدوار'],
          [$basePath.'services/admin/admin-activity-logs.php','سجل التدقيق'],
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
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-book-fill"></i> أحدث البرامج التدريبية</div>
            <a href="<?php echo $basePath;?>services/training/training-programs-list.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap"><table class="ds-tbl">
            <thead><tr><th>البرنامج</th><th>الرمز</th><th>الحالة</th></tr></thead>
            <tbody id="programsTbody"><tr><td colspan="3" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
          </table></div>
        </div>
        <div class="ds-card">
          <div class="ds-card-head">
            <div class="ds-card-title"><i class="bi bi-chat-left-text-fill"></i> أحدث طلبات الاستشارة</div>
            <a href="<?php echo $basePath;?>services/consulting/consulting-requests-list.php" style="font-size:.8rem;font-weight:700;color:var(--ds-primary);text-decoration:none">عرض الكل</a>
          </div>
          <div class="ds-tbl-wrap"><table class="ds-tbl">
            <thead><tr><th>العنوان</th><th>الحالة</th><th>التاريخ</th></tr></thead>
            <tbody id="consultingTbody"><tr><td colspan="3" style="text-align:center;padding:22px;color:#9ca3af">جاري التحميل...</td></tr></tbody>
          </table></div>
        </div>
      </div>
      <div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-pie-chart-fill"></i> حالة الحقائب التدريبية</div>
          <div id="kitStatusChart" style="display:flex;flex-direction:column;gap:6px"><div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:12px">جاري التحميل...</div></div>
        </div>
        <div class="ds-card">
          <div class="ds-card-title" style="margin-bottom:12px"><i class="bi bi-grid-fill"></i> روابط سريعة</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <?php foreach([
              [$basePath.'services/admin/admin-users.php','bi-people-fill','المستخدمون'],
              [$basePath.'services/admin/admin-roles.php','bi-diagram-3-fill','الأدوار'],
              [$basePath.'services/gis/needs-dashboard.php','bi-signpost-split-fill','الاحتياجات'],
              [$basePath.'services/training/program-bank-dashboard.php','bi-collection-fill','بنك الحقائب'],
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
<script src="<?php echo $basePath;?>assets/js/modules/dashboard-sidebar.js?v=2.1"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;
  const allowed = ['general_director','deputy_general_director','admin','super_admin','system_admin'];
  if (!allowed.some(r => window.AppAuth.hasRole(r))) { location.href='<?php echo $basePath;?>'; return; }
  const user = window.AppAuth.getUser();
  const BASE = window.APP_CONFIG.API_BASE_URL;
  const H = () => ({ Authorization:`Bearer ${window.AppAuth.getToken()}`, Accept:'application/json' });
  const E = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const num = v => Number(v||0).toLocaleString('ar');
  document.getElementById('heroGreet').textContent = `مرحباً، ${user?.name||'المدير العام'} 👋`;

  const jget = async (p) => { try{ const r=await fetch(`${BASE}${p}`,{headers:H()}); if(!r.ok) return null; return await r.json(); }catch(e){ return null; } };
  const listOf = d => Array.isArray(d)?d:(d?.data||d?.items||[]);
  const totalOf = (d,arr) => d?.total ?? d?.meta?.total ?? (arr?arr.length:0);
  const setK = (id,v) => { const el=document.getElementById(id); if(el) el.textContent = v; };

  const [dPrograms,dCourses,dCenters,dKit,dTrainees,dCons,dNeeds,dInc,dBranches,dUsers] = await Promise.all([
    jget('/training-programs?per_page=6'), jget('/training-courses?per_page=1'), jget('/training-centers?per_page=1'),
    jget('/program-bank/stats'), jget('/trainees?per_page=1'), jget('/consulting/requests?per_page=6'),
    jget('/needs?per_page=1'), jget('/incubators?per_page=1'), jget('/branches?per_page=1'), jget('/users?per_page=1'),
  ]);

  const KIT_ST = { approved:['معتمدة','#15803d'], under_admin_review:['مراجعة إدارية','#7c3aed'], draft:['مسودة','#6b7280'], suspended:['موقوفة','#b91c1c'] };
  const CONS = { submitted:['مقدمة','#0369a1'], needs_info:['بحاجة معلومات','#b45309'], in_progress:['قيد التنفيذ','#7c3aed'], completed:['مكتملة','#15803d'], rejected:['مرفوضة','#b91c1c'], draft:['مسودة','#6b7280'] };

  { const a=listOf(dPrograms); setK('kPrograms', num(totalOf(dPrograms,a)));
    document.getElementById('programsTbody').innerHTML = a.length ? a.slice(0,6).map(p=>{const [l,c]=KIT_ST[p.bank_status]||['—','#6b7280'];return `<tr><td style="font-weight:700">${E(p.title||p.name||'—')}</td><td style="font-size:.8rem;color:#6b7280">${E(p.code||'—')}</td><td><span style="background:${c}18;color:${c};border-radius:20px;padding:2px 9px;font-size:.72rem;font-weight:800">${l}</span></td></tr>`;}).join('') : '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:18px">لا توجد بيانات</td></tr>'; }
  setK('kCourses', num(totalOf(dCourses,listOf(dCourses))));
  setK('kCenters', num(totalOf(dCenters,listOf(dCenters))));
  setK('kTrainees', num(totalOf(dTrainees,listOf(dTrainees))));
  setK('kNeeds', num(totalOf(dNeeds,listOf(dNeeds))));
  setK('kIncubators', num(totalOf(dInc,listOf(dInc))));
  setK('kBranches', num(totalOf(dBranches,listOf(dBranches))));
  setK('kUsers', num(totalOf(dUsers,listOf(dUsers))));

  { const s=dKit?.data||dKit||{}; setK('kKits', num(s.approved));
    const rows=[['approved',s.approved||0],['under_admin_review',s.under_review||0],['draft',s.draft||0],['suspended',s.suspended||0]].filter(([,n])=>n>0);
    const max=Math.max(1,...rows.map(([,n])=>n));
    document.getElementById('kitStatusChart').innerHTML = rows.length ? rows.map(([k,n])=>{const [l,c]=KIT_ST[k]||[k,'#6b7280'];return `<div style="display:flex;align-items:center;gap:8px;font-size:.8rem"><div style="min-width:92px;font-weight:700;color:#6b7280;text-align:right">${l}</div><div style="flex:1;background:#f3f4f6;border-radius:20px;height:7px"><div style="width:${Math.round(n/max*100)}%;height:100%;border-radius:20px;background:${c}"></div></div><div style="font-weight:800;color:${c};min-width:22px">${num(n)}</div></div>`;}).join('') : '<div style="color:#9ca3af;font-size:.84rem;text-align:center;padding:8px">لا توجد بيانات</div>'; }

  { const a=listOf(dCons); setK('kConsulting', num(totalOf(dCons,a)));
    document.getElementById('consultingTbody').innerHTML = a.length ? a.slice(0,6).map(r=>{const [l,c]=CONS[r.status]||[r.status||'—','#6b7280'];return `<tr><td style="font-weight:700">${E(r.title||r.subject||('#'+(r.id||'')))}</td><td><span style="background:${c}18;color:${c};border-radius:20px;padding:2px 9px;font-size:.72rem;font-weight:800">${l}</span></td><td style="font-size:.78rem;color:#6b7280">${E((r.created_at||'').slice(0,10)||'—')}</td></tr>`;}).join('') : '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:18px">لا توجد طلبات</td></tr>'; }
});
</script>
</body>
</html>
