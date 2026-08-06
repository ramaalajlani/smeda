<?php
$basePath  = '../../';
$pageTitle = 'قائمة البرامج التدريبية';
$activePage= 'program-bank-list';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <style>
    :root{--c:#1a6b5a;--ca:#10b981;--cs:#e8f8f3;--cb:rgba(26,107,90,.12);--ct:#15312a;--cm:#6b7280;--sh:0 8px 24px rgba(15,60,50,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e7f6f1);min-height:100vh;}
    .pw{width:100%;max-width:1340px;margin:0 auto;padding:0 0 28px;}
    @media(max-width:767px){
      .page-hd{flex-direction:column;align-items:stretch;gap:10px;}
      .page-hd h1{font-size:1.15rem;}
      .btn-prim{width:100%;justify-content:center;min-height:44px;}
      .filters{flex-direction:column;align-items:stretch;}
      .flt-grp,.flt-search{min-width:0;width:100%;}
      .flt-grp select,.flt-grp input{min-height:44px;font-size:16px;width:100%;}
      .btn-filter,.btn-reset{width:100%;justify-content:center;min-height:44px;}
      .stats-bar{gap:6px;}
      .stat-chip{flex:1 1 calc(50% - 6px);text-align:center;}
    }

    /* Page header */
    .page-hd{display:flex;align-items:center;gap:14px;margin-bottom:20px;flex-wrap:wrap;}
    .page-hd h1{font-size:1.4rem;font-weight:800;color:var(--ct);margin:0;flex:1;}
    .btn-prim{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:12px;background:var(--c);color:#fff;font-weight:700;font-size:.87rem;border:none;cursor:pointer;text-decoration:none;transition:.2s;}
    .btn-prim:hover{background:#145a4b;color:#fff;}

    /* Filters */
    .filters{background:#fff;border:1px solid var(--cb);border-radius:18px;padding:16px 18px;box-shadow:var(--sh);margin-bottom:18px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;}
    .flt-grp{display:flex;flex-direction:column;gap:4px;min-width:150px;}
    .flt-grp label{font-size:.75rem;font-weight:700;color:var(--cm);}
    .flt-grp select,.flt-grp input{padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:.85rem;color:var(--ct);background:#fff;font-family:inherit;}
    .flt-grp select:focus,.flt-grp input:focus{border-color:var(--c);outline:none;}
    .flt-search{flex:1;min-width:200px;}
    .btn-filter{padding:9px 16px;border-radius:10px;background:var(--cs);color:var(--c);border:1.5px solid var(--cb);font-weight:700;cursor:pointer;font-size:.85rem;display:flex;align-items:center;gap:5px;}
    .btn-reset{padding:9px 14px;border-radius:10px;background:#f1f5f9;color:#64748b;border:none;font-weight:600;cursor:pointer;font-size:.85rem;}

    /* Stats bar */
    .stats-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
    .stat-chip{padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:700;background:#fff;border:1.5px solid var(--cb);color:var(--ct);cursor:pointer;transition:.15s;}
    .stat-chip:hover,.stat-chip.active{background:var(--c);color:#fff;border-color:var(--c);}

    /* Table */
    .tbl-wrap{background:#fff;border:1px solid var(--cb);border-radius:20px;box-shadow:var(--sh);overflow:hidden;}
    .tbl{width:100%;border-collapse:collapse;}
    .tbl thead{background:var(--cs);}
    .tbl th{padding:11px 14px;font-size:.78rem;font-weight:800;color:var(--ct);text-align:right;white-space:nowrap;}
    .tbl td{padding:10px 14px;font-size:.84rem;color:var(--ct);vertical-align:middle;border-bottom:1px solid #f0f4f8;}
    .tbl tr:last-child td{border-bottom:none;}
    .tbl tr:hover td{background:#f8fdfb;}

    /* Badges */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:16px;font-size:.73rem;font-weight:700;white-space:nowrap;}
    .b-draft{background:#f1f5f9;color:#475569;}
    .b-tech{background:#fef3c7;color:#92400e;}
    .b-admin{background:#fff7ed;color:#9a3412;}
    .b-approved{background:#dcfce7;color:#15803d;}
    .b-suspended{background:#fee2e2;color:#991b1b;}
    .b-archived{background:#f1f5f9;color:#334155;}

    /* Actions */
    .act-btns{display:flex;gap:5px;flex-wrap:nowrap;}
    .act-btn{width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;transition:.15s;font-size:.9rem;text-decoration:none;}
    @media(max-width:768px){.act-btn{width:40px;height:40px;font-size:1rem;}}

    /* Toast */
    #listToast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 22px;border-radius:14px;font-weight:700;font-size:.88rem;z-index:9999;display:none;box-shadow:0 8px 24px rgba(0,0,0,.18);}
    .toast-ok{background:#dcfce7;color:#15803d;border:1.5px solid #4ade80;}
    .toast-err{background:#fee2e2;color:#991b1b;border:1.5px solid #f87171;}
    .ab-view{background:#e0f2fe;color:#0369a1;} .ab-view:hover{background:#0369a1;color:#fff;}
    .ab-edit{background:#fef9c3;color:#854d0e;} .ab-edit:hover{background:#854d0e;color:#fff;}
    .ab-del{background:#fee2e2;color:#991b1b;} .ab-del:hover{background:#991b1b;color:#fff;}
    .ab-approve{background:#dcfce7;color:#15803d;} .ab-approve:hover{background:#15803d;color:#fff;}

    /* Pagination */
    .pag{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;flex-wrap:wrap;gap:8px;}
    .pag-info{font-size:.82rem;color:var(--cm);font-weight:600;}
    .pag-btns{display:flex;gap:5px;}
    .pag-btn{padding:5px 12px;border-radius:8px;border:1.5px solid var(--cb);background:#fff;color:var(--ct);font-weight:700;font-size:.82rem;cursor:pointer;}
    .pag-btn:hover:not(:disabled){background:var(--cs);}
    .pag-btn:disabled{opacity:.4;cursor:default;}
    .pag-btn.active{background:var(--c);color:#fff;border-color:var(--c);}

    /* Empty */
    .empty{text-align:center;padding:48px 20px;color:var(--cm);}
    .empty i{font-size:3rem;margin-bottom:12px;opacity:.4;}
    .empty p{font-size:.9rem;}

    /* Loading */
    .skeleton{height:48px;background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);background-size:200%;animation:skel 1.4s infinite;border-radius:8px;}
    @keyframes skel{0%{background-position:200% 0}100%{background-position:-200% 0}}

    /* Overflow hidden on mobile */
    @media(max-width:768px){.tbl-wrap{overflow-x:auto;}.tbl{min-width:700px;}}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/program-bank-shell-open.php'; ?>

<div class="pw">

  <!-- Page Header -->
  <div class="page-hd">
    <a href="program-bank-dashboard.php" style="color:var(--cm);text-decoration:none;font-size:.85rem;display:flex;align-items:center;gap:4px;">
      <i class="bi bi-arrow-right"></i> الداشبورد
    </a>
    <h1><i class="bi bi-collection" style="color:var(--c)"></i> قائمة البرامج التدريبية</h1>
    <a href="program-bank-form.php" class="btn-prim"><i class="bi bi-plus-lg"></i> برنامج جديد</a>
  </div>

  <!-- Stats Chips -->
  <div class="stats-bar" id="statsBar"></div>

  <!-- Filters -->
  <div class="filters">
    <div class="flt-grp flt-search">
      <label>بحث</label>
      <input type="text" id="search" placeholder="اسم البرنامج، الرمز، الوصف...">
    </div>
    <div class="flt-grp">
      <label>الحالة</label>
      <select id="bankStatus">
        <option value="">كل الحالات</option>
        <option value="draft">مسودة</option>
        <option value="under_technical_review">مراجعة فنية</option>
        <option value="under_admin_review">مراجعة إدارية</option>
        <option value="approved">معتمد</option>
        <option value="suspended">موقوف</option>
        <option value="archived">مؤرشف</option>
      </select>
    </div>
    <div class="flt-grp">
      <label>النوع</label>
      <select id="typeFilter">
        <option value="">كل الأنواع</option>
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
    <div class="flt-grp">
      <label>المستوى</label>
      <select id="levelFilter">
        <option value="">كل المستويات</option>
        <option value="beginner">مبتدئ</option>
        <option value="intermediate">متوسط</option>
        <option value="advanced">متقدم</option>
      </select>
    </div>
    <button class="btn-filter" onclick="doSearch()"><i class="bi bi-funnel"></i> تصفية</button>
    <button class="btn-reset" onclick="resetFilters()">إعادة تعيين</button>
  </div>

  <!-- Table -->
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>اسم البرنامج</th>
          <th>الرمز</th>
          <th>النوع</th>
          <th>المستوى</th>
          <th>الساعات</th>
          <th>محاور</th>
          <th>تنفيذات</th>
          <th>الحالة</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <tr><td colspan="10"><div class="skeleton"></div></td></tr>
      </tbody>
    </table>
    <div class="pag" id="pagination" style="display:none">
      <div class="pag-info" id="pagInfo"></div>
      <div class="pag-btns" id="pagBtns"></div>
    </div>
  </div>

</div><!-- .pw -->

<div id="listToast"></div>

<!-- Confirm delete modal -->
<div id="delModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:18px;padding:28px;max-width:380px;width:90%;text-align:center;">
    <i class="bi bi-exclamation-triangle" style="font-size:2.5rem;color:#ef4444;"></i>
    <h3 style="margin:12px 0 6px;font-size:1.1rem;">تأكيد الحذف</h3>
    <p style="color:#6b7280;font-size:.88rem;margin-bottom:20px;">سيتم حذف البرنامج نهائياً. هذا الإجراء لا يمكن التراجع عنه.</p>
    <div style="display:flex;gap:10px;justify-content:center;">
      <button onclick="confirmDelete()" style="padding:9px 22px;border-radius:10px;background:#ef4444;color:#fff;border:none;font-weight:700;cursor:pointer;min-height:44px;">حذف</button>
      <button onclick="document.getElementById('delModal').style.display='none'" style="padding:9px 22px;border-radius:10px;background:#f1f5f9;color:#334155;border:none;font-weight:700;cursor:pointer;min-height:44px;">إلغاء</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/program-bank-shell-close.php'; ?>
<script>
let currentPage = 1, totalPages = 1, pendingDeleteId = null;
const API = window.APP_CONFIG?.API_BASE_URL;

const STATUS_BADGE = {
  draft:                   ['b-draft','مسودة'],
  under_technical_review:  ['b-tech','مراجعة فنية'],
  under_admin_review:      ['b-admin','مراجعة إدارية'],
  approved:                ['b-approved','معتمد ✓'],
  suspended:               ['b-suspended','موقوف'],
  archived:                ['b-archived','مؤرشف'],
};
const TYPE_LABELS = {
  entrepreneurial:'ريادي',financial:'مالي',marketing:'تسويقي',
  technical:'فني',digital:'رقمي',administrative:'إداري',
  agricultural:'زراعي',craft:'حرفي',other:'أخرى'
};
const LEVEL_LABELS = { beginner:'مبتدئ', intermediate:'متوسط', advanced:'متقدم' };

// Read URL params on load
function readUrlParams() {
  const p = new URLSearchParams(location.search);
  if (p.get('bank_status')) document.getElementById('bankStatus').value = p.get('bank_status');
  if (p.get('type'))        document.getElementById('typeFilter').value = p.get('type');
  if (p.get('level'))       document.getElementById('levelFilter').value = p.get('level');
  if (p.get('search'))      document.getElementById('search').value = p.get('search');
  if (p.get('grants_certificate')) { /* handled via filter */ }
}

async function loadPrograms(page=1) {
  currentPage = page;
  const params = new URLSearchParams({
    page,
    per_page: 20,
    bank_status: document.getElementById('bankStatus').value,
    type:        document.getElementById('typeFilter').value,
    level:       document.getElementById('levelFilter').value,
    search:      document.getElementById('search').value,
  });
  // remove empty
  [...params.entries()].forEach(([k,v]) => { if(!v) params.delete(k); });

  document.getElementById('tableBody').innerHTML = '<tr><td colspan="10"><div class="skeleton"></div></td></tr>';

  try {
    const r = await fetch(API + '/program-bank?' + params, {
      headers: { Authorization: 'Bearer ' + AppAuth.getToken() }
    });
    const { data, meta } = await r.json();
    renderTable(data);
    renderPagination(meta);
  } catch(e) {
    document.getElementById('tableBody').innerHTML = '<tr><td colspan="10" style="text-align:center;padding:30px;color:#ef4444">فشل تحميل البيانات</td></tr>';
  }
}

function renderTable(rows) {
  const tbody = document.getElementById('tableBody');
  if (!rows || !rows.length) {
    tbody.innerHTML = `<tr><td colspan="10"><div class="empty"><i class="bi bi-inbox"></i><p>لا توجد برامج بهذه المعايير</p></div></td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map((p,i) => {
    const [bc, bl] = STATUS_BADGE[p.bank_status] || ['b-draft','—'];
    return `<tr>
      <td style="color:var(--cm);font-size:.78rem">${((currentPage-1)*20)+i+1}</td>
      <td>
        <div style="font-weight:700;color:var(--ct)">${p.display_name || p.name}</div>
        ${p.sector ? `<div style="font-size:.75rem;color:var(--cm)">${p.sector}</div>` : ''}
      </td>
      <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:6px;font-size:.8rem">${p.code}</code></td>
      <td>${p.type ? `<span style="font-size:.8rem">${TYPE_LABELS[p.type]||p.type}</span>` : '—'}</td>
      <td>${p.level ? `<span style="font-size:.8rem">${LEVEL_LABELS[p.level]||p.level}</span>` : '—'}</td>
      <td style="text-align:center">${p.suggested_hours ?? '—'}</td>
      <td style="text-align:center">${p.modules_count ?? 0}</td>
      <td style="text-align:center">${p.executions_count ?? 0}</td>
      <td><span class="badge ${bc}">${bl}</span></td>
      <td>
        <div class="act-btns">
          <a href="program-bank-show.php?id=${p.id}" class="act-btn ab-view" title="عرض"><i class="bi bi-eye"></i></a>
          ${['draft','under_technical_review'].includes(p.bank_status) ? `<a href="program-bank-form.php?id=${p.id}" class="act-btn ab-edit" title="تعديل"><i class="bi bi-pencil"></i></a>` : ''}
          ${p.bank_status === 'draft' ? `<button onclick="askDelete(${p.id})" class="act-btn ab-del" title="حذف"><i class="bi bi-trash"></i></button>` : ''}
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderPagination(meta) {
  if (!meta) return;
  totalPages = meta.last_page;
  const pag = document.getElementById('pagination');
  const info = document.getElementById('pagInfo');
  const btns = document.getElementById('pagBtns');
  pag.style.display = 'flex';
  info.textContent = `الصفحة ${meta.current_page} من ${meta.last_page} — إجمالي ${meta.total} برنامج`;
  const pages = [];
  for(let p=Math.max(1,currentPage-2);p<=Math.min(totalPages,currentPage+2);p++) pages.push(p);
  btns.innerHTML = `
    <button class="pag-btn" onclick="loadPrograms(1)" ${currentPage===1?'disabled':''}>«</button>
    <button class="pag-btn" onclick="loadPrograms(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>
    ${pages.map(p=>`<button class="pag-btn ${p===currentPage?'active':''}" onclick="loadPrograms(${p})">${p}</button>`).join('')}
    <button class="pag-btn" onclick="loadPrograms(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>›</button>
    <button class="pag-btn" onclick="loadPrograms(${totalPages})" ${currentPage===totalPages?'disabled':''}>»</button>
  `;
}

async function loadStatsChips() {
  try {
    const r = await fetch(API + '/program-bank/stats', { headers:{Authorization:'Bearer '+AppAuth.getToken()} });
    const { data } = await r.json();
    const chips = [
      ['','الكل',data.total],
      ['approved','معتمدة',data.approved],
      ['under_technical_review,under_admin_review','قيد المراجعة',data.under_review],
      ['draft','مسودات',data.draft],
      ['suspended','موقوفة',data.suspended],
    ];
    const bar = document.getElementById('statsBar');
    const current = document.getElementById('bankStatus').value;
    bar.innerHTML = chips.map(([val,lbl,cnt]) =>
      `<div class="stat-chip ${current===val?'active':''}" onclick="document.getElementById('bankStatus').value='${val}';doSearch()">${lbl} (${cnt??0})</div>`
    ).join('');
  } catch(e){}
}

function doSearch() { loadPrograms(1); }
function resetFilters() {
  ['bankStatus','typeFilter','levelFilter'].forEach(id => document.getElementById(id).value='');
  document.getElementById('search').value='';
  loadPrograms(1);
}

function askDelete(id) { pendingDeleteId = id; document.getElementById('delModal').style.display='flex'; }
async function confirmDelete() {
  document.getElementById('delModal').style.display='none';
  if (!pendingDeleteId) return;
  try {
    const r = await fetch(API + '/program-bank/' + pendingDeleteId, {
      method:'DELETE', headers:{Authorization:'Bearer '+AppAuth.getToken()}
    });
    if (r.ok) { showListToast('تم حذف البرنامج','ok'); loadPrograms(currentPage); }
    else { const e=await r.json(); showListToast(e.message||'فشل الحذف','err'); }
  } catch(e){ showListToast('خطأ في الاتصال','err'); }
}

function showListToast(msg, type='ok') {
  const t = document.getElementById('listToast');
  t.className = type==='ok'?'toast-ok':'toast-err';
  t.textContent = msg;
  t.style.display='block';
  setTimeout(()=>{ t.style.display='none'; }, 3000);
}

document.addEventListener('DOMContentLoaded', () => {
  AppBootstrapAuth.init({ requireAuth: true });
  document.getElementById('search').addEventListener('keydown', e => { if(e.key==='Enter') doSearch(); });
  readUrlParams();
  loadPrograms(1);
  loadStatsChips();
});
</script>
</body>
</html>
