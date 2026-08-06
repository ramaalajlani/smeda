<?php
$basePath  = '../';
$pageTitle = 'صندوق الرسائل';
$activePage= 'inbox';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../includes/layout/head.php'; ?>
  <?php include __DIR__ . '/../includes/layout/app-shell-styles.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:900px; margin:auto; padding:24px 14px; }
    .page-hero { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border-radius:22px; padding:24px 28px; margin-bottom:20px; position:relative; overflow:hidden; }
    .page-hero::before { content:""; position:absolute; width:180px; height:180px; border-radius:50%; top:-90px; left:-60px; background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%); }
    .tabs { display:flex; gap:4px; background:#fff; border:1px solid var(--c-border); border-radius:16px; padding:6px; margin-bottom:16px; box-shadow:var(--c-shadow); }
    .tab-btn { flex:1; padding:10px; border:none; border-radius:12px; background:transparent; font-weight:700; font-size:.88rem; cursor:pointer; color:var(--c-muted); transition:all .2s; }
    .tab-btn.active { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; }
    .msg-card { background:#fff; border:1px solid var(--c-border); border-radius:16px; padding:16px; margin-bottom:10px; cursor:pointer; transition:all .2s; display:flex; gap:12px; align-items:flex-start; }
    .msg-card:hover { box-shadow:0 8px 24px rgba(15,79,71,.1); transform:translateY(-1px); }
    .msg-card.unread { border-right:4px solid var(--c-primary); background:var(--c-soft); }
    .msg-avatar { width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:1rem; flex-shrink:0; }
    .msg-subject { font-weight:800; font-size:.95rem; color:var(--c-text); margin-bottom:3px; }
    .msg-preview { font-size:.83rem; color:var(--c-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:500px; }
    .msg-meta { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:6px; font-size:.78rem; color:var(--c-muted); }
    .priority-urgent { color:#dc2626; font-weight:800; }
    .priority-high    { color:#d97706; font-weight:700; }
    .badge-broadcast { background:#e0e7ff; color:#4338ca; padding:2px 8px; border-radius:8px; font-weight:700; font-size:.73rem; }
    .badge-reply-req  { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:8px; font-weight:700; font-size:.73rem; }
    .compose-btn { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:11px 22px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; font-size:.9rem; }
    .empty-box { text-align:center; padding:60px 20px; color:var(--c-muted); }
    .empty-box i { font-size:3.5rem; color:rgba(23,148,123,.2); display:block; margin-bottom:12px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/layout/app-shell-open.php'; ?>

<div class="page-wrap">
  <div class="page-hero">
    <i class="bi bi-envelope-fill" style="position:absolute;right:24px;top:50%;transform:translateY(-50%);font-size:4.5rem;opacity:.12"></i>
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      <div>
        <div style="font-size:1.4rem;font-weight:800;margin-bottom:4px">صندوق الرسائل</div>
        <div style="opacity:.9;font-size:.9rem">الرسائل والاستفسارات الموجهة من الهيئة</div>
      </div>
      <button class="compose-btn" style="margin-right:auto" onclick="openCompose()">
        <i class="bi bi-pencil-square"></i> إنشاء رسالة
      </button>
    </div>
  </div>

  <div class="tabs">
    <button class="tab-btn active" id="tab-inbox" onclick="switchTab('inbox')"><i class="bi bi-inbox-fill"></i> الوارد <span id="unread-badge"></span></button>
    <button class="tab-btn" id="tab-sent" onclick="switchTab('sent')"><i class="bi bi-send-fill"></i> المُرسَل</button>
  </div>

  <div id="loadingBox" style="text-align:center;padding:40px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2rem;display:block;margin-bottom:10px"></i>جاري التحميل...
  </div>
  <div id="msgContainer"></div>
  <div id="paginationBox" class="d-flex justify-content-center gap-2 mt-3"></div>
</div>

<!-- Compose Modal -->
<div id="composeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:22px;padding:28px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
      <i class="bi bi-pencil-square" style="color:var(--c-primary);font-size:1.4rem"></i>
      <strong style="font-size:1.1rem;color:var(--c-text)">إنشاء رسالة جديدة</strong>
      <button onclick="closeCompose()" style="margin-right:auto;background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--c-muted)">×</button>
    </div>
    <form id="composeForm">
      <div style="margin-bottom:14px">
        <label style="font-size:.83rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px">الموضوع *</label>
        <input type="text" id="c-subject" style="width:100%;border:1px solid var(--c-border);border-radius:12px;padding:10px 14px;font-size:.9rem" required placeholder="موضوع الرسالة">
      </div>
      <div id="recipientSection" style="margin-bottom:14px">
        <label style="font-size:.83rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px">المستقبل</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
          <label style="display:flex;align-items:center;gap:6px;font-size:.85rem;cursor:pointer">
            <input type="radio" name="send-type" value="individual" checked onchange="toggleSendType()"> مستخدم محدد
          </label>
          <label id="broadcastOption" style="display:none;align-items:center;gap:6px;font-size:.85rem;cursor:pointer">
            <input type="radio" name="send-type" value="broadcast" onchange="toggleSendType()"> بث لجميع المستخدمين
          </label>
        </div>
        <div id="individualField">
          <input type="text" id="c-recipient-search" style="width:100%;border:1px solid var(--c-border);border-radius:12px;padding:10px 14px;font-size:.88rem" placeholder="ابحث عن مستخدم بالاسم أو الإيميل...">
          <div id="recipientResults" style="border:1px solid var(--c-border);border-radius:12px;display:none;max-height:150px;overflow-y:auto;background:#fff;margin-top:4px"></div>
          <input type="hidden" id="c-recipient-id">
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:.83rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px">نص الرسالة *</label>
        <textarea id="c-body" rows="5" style="width:100%;border:1px solid var(--c-border);border-radius:12px;padding:10px 14px;font-size:.9rem;resize:vertical" required placeholder="اكتب رسالتك هنا..."></textarea>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
        <label style="display:flex;align-items:center;gap:6px;font-size:.84rem;cursor:pointer">
          <input type="checkbox" id="c-requires-reply"> يستلزم رداً
        </label>
        <div>
          <select id="c-priority" style="border:1px solid var(--c-border);border-radius:10px;padding:6px 12px;font-size:.84rem">
            <option value="normal">عادي</option>
            <option value="high">مهم</option>
            <option value="urgent">عاجل</option>
          </select>
        </div>
      </div>
      <div id="composeError" style="color:#dc2626;font-size:.85rem;margin-bottom:10px;display:none"></div>
      <button type="submit" style="width:100%;background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:14px;padding:13px;font-weight:800;font-size:.95rem;cursor:pointer">
        <i class="bi bi-send-fill"></i> إرسال
      </button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/layout/app-shell-close.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base    = window.APP_CONFIG.API_BASE_URL;
  const token   = () => window.AppAuth.getToken();
  const user    = window.AppAuth.getUser();
  const isAdmin = window.AppAuth.hasRole?.('admin') || window.AppAuth.hasRole?.('super_admin') || window.AppAuth.hasRole?.('branch_manager');
  let currentTab = 'inbox';

  if (isAdmin) {
    document.getElementById('broadcastOption').style.display = 'flex';
  }

  // عداد غير المقروءة في الـ tab
  fetch(`${base}/inbox/unread-count`, { headers:{ 'Authorization':`Bearer ${token()}` } })
    .then(r=>r.json()).then(d=>{
      const b = document.getElementById('unread-badge');
      if (d.count > 0) b.innerHTML = `<span style="background:#ef4444;color:#fff;border-radius:20px;padding:1px 7px;font-size:.7rem;font-weight:800;margin-right:4px">${d.count}</span>`;
    }).catch(()=>{});

  window.switchTab = function(tab) {
    currentTab = tab;
    ['inbox','sent'].forEach(t => document.getElementById('tab-'+t).classList.toggle('active', t===tab));
    load(1);
  };

  async function load(page=1) {
    document.getElementById('loadingBox').style.display='block';
    document.getElementById('msgContainer').innerHTML='';
    const endpoint = currentTab === 'inbox' ? '/inbox' : '/inbox/sent';
    const r = await fetch(`${base}${endpoint}?page=${page}&per_page=20`, { headers:{ 'Authorization':`Bearer ${token()}` } });
    const json = await r.json();
    const items = json.data || [];
    const meta  = json.meta || {};
    document.getElementById('loadingBox').style.display='none';

    if (!items.length) {
      document.getElementById('msgContainer').innerHTML=`<div class="empty-box"><i class="bi bi-envelope-open"></i>لا توجد رسائل</div>`;
      return;
    }

    const PRIORITY_LABELS = { normal:'', high:'<span class="priority-high"><i class="bi bi-exclamation-circle-fill"></i> مهم</span>', urgent:'<span class="priority-urgent"><i class="bi bi-exclamation-triangle-fill"></i> عاجل</span>' };

    document.getElementById('msgContainer').innerHTML = items.map(m => {
      const isUnread = currentTab === 'inbox' && !m.is_read;
      const name = currentTab === 'inbox' ? (m.sender?.name || 'النظام') : (m.recipient?.name || 'بث عام');
      const initial = name.charAt(0);
      return `<div class="msg-card ${isUnread?'unread':''}" onclick="location.href='inbox-view.php?id=${m.id}'">
        <div class="msg-avatar">${initial}</div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px">
            <span style="font-weight:700;font-size:.87rem;color:var(--c-muted)">${window.APP_HELPERS.e(name)}</span>
            ${m.is_broadcast ? '<span class="badge-broadcast"><i class="bi bi-broadcast"></i> بث عام</span>' : ''}
            ${m.requires_reply && !m.is_read ? '<span class="badge-reply-req"><i class="bi bi-reply-fill"></i> يستلزم رداً</span>' : ''}
            <span style="margin-right:auto;font-size:.77rem;color:var(--c-muted)">${new Date(m.created_at).toLocaleDateString('ar-SY')}</span>
          </div>
          <div class="msg-subject">${window.APP_HELPERS.e(m.subject)}</div>
          <div class="msg-preview">${window.APP_HELPERS.e(m.body).substring(0,100)}</div>
          <div class="msg-meta">${PRIORITY_LABELS[m.priority]||''}</div>
        </div>
      </div>`;
    }).join('');

    const lastPage = meta.last_page||1;
    const pg=document.getElementById('paginationBox'); pg.innerHTML='';
    if (lastPage>1) for(let p2=1;p2<=lastPage;p2++){const b=document.createElement('button');b.textContent=p2;b.className='btn btn-sm '+(p2===page?'btn-success':'btn-outline-secondary');b.style.borderRadius='8px';b.addEventListener('click',()=>load(p2));pg.appendChild(b);}
  }

  // Compose
  window.openCompose = () => {
    document.getElementById('composeModal').style.display='flex';
  };
  window.closeCompose = () => {
    document.getElementById('composeModal').style.display='none';
  };

  window.toggleSendType = function() {
    const type = document.querySelector('input[name="send-type"]:checked').value;
    document.getElementById('individualField').style.display = type==='individual' ? 'block' : 'none';
  };

  // البحث عن مستخدم
  let searchTimer;
  const recipientSearchInput = document.getElementById('c-recipient-search');
  const recipientResultsBox = document.getElementById('recipientResults');
  const recipientIdInput = document.getElementById('c-recipient-id');

  function hideRecipientResults() {
    recipientResultsBox.style.display = 'none';
    recipientResultsBox.innerHTML = '';
  }

  function renderRecipientResults(users) {
    recipientResultsBox.innerHTML = '';

    if (!Array.isArray(users) || users.length === 0) {
      recipientResultsBox.style.display = 'block';
      recipientResultsBox.innerHTML = '<div style="padding:8px 12px;font-size:.84rem;color:#6b7280">لا يوجد نتائج مطابقة.</div>';
      return;
    }

    users.forEach((u) => {
      const row = document.createElement('div');
      row.style.padding = '8px 12px';
      row.style.cursor = 'pointer';
      row.style.fontSize = '.87rem';
      row.style.borderBottom = '1px solid #f3f4f6';
      row.innerHTML = `${window.APP_HELPERS.e(u.name)} <span style="color:#9ca3af">${window.APP_HELPERS.e(u.email || '')}</span>`;
      row.addEventListener('click', () => {
        recipientIdInput.value = u.id;
        recipientSearchInput.value = u.name || '';
        hideRecipientResults();
      });
      recipientResultsBox.appendChild(row);
    });

    recipientResultsBox.style.display = 'block';
  }

  recipientSearchInput.addEventListener('input', function() {
    recipientIdInput.value = '';
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) {
      hideRecipientResults();
      return;
    }

    searchTimer = setTimeout(async () => {
      try {
        const res = await fetch(`${base}/inbox/users-list?q=${encodeURIComponent(q)}`, {
          headers: { 'Authorization': `Bearer ${token()}` },
        });

        if (!res.ok) {
          hideRecipientResults();
          return;
        }

        const users = await res.json();
        renderRecipientResults(users);
      } catch (e) {
        hideRecipientResults();
      }
    }, 300);
  });

  document.addEventListener('click', (event) => {
    if (!recipientResultsBox.contains(event.target) && event.target !== recipientSearchInput) {
      hideRecipientResults();
    }
  });

  document.getElementById('composeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const isBroadcast = document.querySelector('input[name="send-type"]:checked')?.value === 'broadcast';
    const recipientId = document.getElementById('c-recipient-id').value;

    if (!isBroadcast && !recipientId) {
      document.getElementById('composeError').style.display='block';
      document.getElementById('composeError').textContent='يرجى اختيار المستقبل.';
      return;
    }

    const body = {
      subject:        document.getElementById('c-subject').value,
      body:           document.getElementById('c-body').value,
      priority:       document.getElementById('c-priority').value,
      requires_reply: document.getElementById('c-requires-reply').checked,
      is_broadcast:   isBroadcast,
    };
    if (!isBroadcast) body.recipient_id = parseInt(recipientId);

    const r    = await fetch(`${base}/inbox`, { method:'POST', headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json' }, body:JSON.stringify(body) });
    const json = await r.json();

    if (!r.ok) {
      document.getElementById('composeError').style.display='block';
      document.getElementById('composeError').textContent=json.message||'حدث خطأ';
      return;
    }

    closeCompose();
    alert('تم إرسال الرسالة بنجاح');
    load(1);
  });

  load();
});
</script>
</body>
</html>
