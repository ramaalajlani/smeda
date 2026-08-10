<?php
$basePath  = '../';
$pageTitle = 'الإشعارات';
$activePage= 'notifications';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:780px; margin:auto; padding:24px 14px; }
    .page-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:22px 28px; margin-bottom:20px; position:relative; overflow:hidden; }
    .page-hero::before { content:""; position:absolute; width:160px; height:160px; border-radius:50%; top:-80px; left:-60px; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
    .notif-card { background:#fff; border:1px solid var(--c-border); border-radius:16px; padding:16px; margin-bottom:10px; display:flex; gap:14px; align-items:flex-start; cursor:pointer; transition:all .2s; }
    .notif-card:hover { box-shadow:0 8px 24px rgba(15,79,71,.1); }
    .notif-card.unread { border-right:4px solid var(--c-primary); background:var(--c-soft); }
    .notif-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .notif-title { font-weight:700; font-size:.92rem; color:var(--c-text); margin-bottom:3px; }
    .notif-body  { font-size:.83rem; color:var(--c-muted); margin-bottom:4px; line-height:1.6; }
    .notif-time  { font-size:.77rem; color:var(--c-muted); font-weight:700; }
    .filter-bar  { background:#fff; border:1px solid var(--c-border); border-radius:16px; padding:12px 16px; margin-bottom:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .btn-markall { background:var(--c-soft); color:var(--c-primary); border:none; border-radius:12px; padding:8px 16px; font-weight:700; font-size:.85rem; cursor:pointer; }
    .empty-box { text-align:center; padding:60px 20px; color:var(--c-muted); }
    .empty-box i { font-size:3.5rem; color:rgba(23,148,123,.2); display:block; margin-bottom:12px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/layout/app-shell-open.php'; ?>

<div class="page-wrap">
  <div class="page-hero">
    <i class="bi bi-bell-fill" style="position:absolute;right:24px;top:50%;transform:translateY(-50%);font-size:4rem;opacity:.12"></i>
    <div style="font-size:1.4rem;font-weight:800;margin-bottom:4px">الإشعارات</div>
    <div style="opacity:.9;font-size:.9rem">كل التنبيهات والتحديثات المتعلقة بطلباتك</div>
  </div>

  <div class="filter-bar">
    <label style="display:flex;align-items:center;gap:6px;font-size:.85rem;cursor:pointer;font-weight:700">
      <input type="checkbox" id="unreadOnly" onchange="load(1)"> غير المقروءة فقط
    </label>
    <button class="btn-markall" style="margin-right:auto" onclick="markAll()"><i class="bi bi-check2-all"></i> تحديد الكل كمقروء</button>
  </div>

  <div id="loadingBox" style="text-align:center;padding:40px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2rem;display:block;margin-bottom:10px"></i>جاري التحميل...
  </div>
  <div id="notifContainer"></div>
  <div id="paginationBox" class="d-flex justify-content-center gap-2 mt-3"></div>
</div>

<?php include __DIR__ . '/../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();

  const COLOR_MAP = { primary:'#17947B', success:'#16a34a', warning:'#d97706', danger:'#dc2626', info:'#0ea5e9' };

  async function load(page=1) {
    document.getElementById('loadingBox').style.display='block';
    document.getElementById('notifContainer').innerHTML='';
    const unreadOnly = document.getElementById('unreadOnly').checked;
    const p = new URLSearchParams({ page, per_page:30 });
    if (unreadOnly) p.set('unread_only', '1');

    const json  = await fetch(`${base}/notifications?${p}`, { headers:{ 'Authorization':`Bearer ${token()}` } }).then(r=>r.json());
    const items = json.data || [];
    const meta  = json.meta || {};
    document.getElementById('loadingBox').style.display='none';

    if (!items.length) {
      document.getElementById('notifContainer').innerHTML=`<div class="empty-box"><i class="bi bi-bell-slash"></i>لا توجد إشعارات</div>`;
      return;
    }

    document.getElementById('notifContainer').innerHTML = items.map(n => {
      const c = COLOR_MAP[n.color] || COLOR_MAP.primary;
      return `<div class="notif-card ${n.is_read?'':'unread'}" onclick="handleClick(${n.id},'${n.action_url||''}')">
        <div class="notif-icon" style="background:${c}22;color:${c}"><i class="${n.icon||'bi-bell-fill'}"></i></div>
        <div style="flex:1;min-width:0">
          <div class="notif-title">${window.APP_HELPERS.e(n.title)}</div>
          ${n.body ? `<div class="notif-body">${window.APP_HELPERS.e(n.body)}</div>` : ''}
          <div class="notif-time"><i class="bi bi-clock"></i> ${new Date(n.created_at).toLocaleString('ar-SY')}</div>
        </div>
        <button onclick="event.stopPropagation();deleteNotif(${n.id},this)" style="background:none;border:none;color:#d1d5db;cursor:pointer;font-size:1.1rem;padding:4px" title="حذف"><i class="bi bi-x-lg"></i></button>
      </div>`;
    }).join('');

    const lastPage = meta.last_page||1;
    const pg=document.getElementById('paginationBox'); pg.innerHTML='';
    if (lastPage>1) for(let p2=1;p2<=lastPage;p2++){const b=document.createElement('button');b.textContent=p2;b.className='btn btn-sm '+(p2===page?'btn-success':'btn-outline-secondary');b.style.borderRadius='8px';b.addEventListener('click',()=>load(p2));pg.appendChild(b);}
  }

  window.handleClick = async function(id, url) {
    await fetch(`${base}/notifications/${id}/read`, { method:'POST', headers:{ 'Authorization':`Bearer ${token()}` } });
    const card = document.querySelector(`.notif-card[onclick*="${id}"]`);
    if (card) card.classList.remove('unread');
    if (url && url!=='undefined') location.href = url;
  };

  window.deleteNotif = async function(id, btn) {
    await fetch(`${base}/notifications/${id}`, { method:'DELETE', headers:{ 'Authorization':`Bearer ${token()}` } });
    btn.closest('.notif-card')?.remove();
  };

  window.markAll = async function() {
    await fetch(`${base}/notifications/read-all`, { method:'POST', headers:{ 'Authorization':`Bearer ${token()}` } });
    document.querySelectorAll('.notif-card.unread').forEach(c=>c.classList.remove('unread'));
    if (window.AppNotif) window.AppNotif.refresh();
  };

  load();
});
</script>
</body>
</html>
