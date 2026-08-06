<?php
$basePath  = '../';
$pageTitle = 'عرض الرسالة';
$activePage= 'inbox';
?>
<?php include __DIR__ . '/../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:760px; margin:auto; padding:24px 14px; }
    .msg-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:24px; box-shadow:var(--c-shadow); margin-bottom:14px; }
    .msg-header { display:flex; align-items:flex-start; gap:14px; margin-bottom:18px; }
    .msg-avatar { width:50px; height:50px; border-radius:14px; background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; font-weight:800; font-size:1.3rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .msg-subject { font-size:1.3rem; font-weight:800; color:var(--c-text); margin-bottom:4px; }
    .meta-row { display:flex; flex-wrap:wrap; gap:12px; font-size:.83rem; color:var(--c-muted); }
    .meta-row i { color:var(--c-primary); }
    .msg-body { font-size:.95rem; line-height:2; color:var(--c-text); white-space:pre-wrap; }
    .priority-badge { padding:4px 12px; border-radius:20px; font-size:.78rem; font-weight:700; }
    .p-urgent { background:#fee2e2; color:#dc2626; }
    .p-high   { background:#fef3c7; color:#d97706; }
    .p-normal { background:#f3f4f6; color:#6b7280; }

    .reply-card { background:var(--c-soft); border:1px solid var(--c-border); border-radius:16px; padding:18px; margin-bottom:10px; }
    .reply-from { font-weight:700; font-size:.88rem; color:var(--c-primary); margin-bottom:6px; }
    .reply-body { font-size:.9rem; line-height:1.9; color:var(--c-text); white-space:pre-wrap; }

    .reply-form { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); }
    .reply-textarea { width:100%; border:1px solid var(--c-border); border-radius:12px; padding:12px 14px; font-size:.9rem; resize:vertical; min-height:100px; }
    .btn-send { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:14px; padding:11px 24px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
  </style>
</head>
<body>
<div class="page-wrap">
  <a href="inbox-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem;display:inline-block;margin-bottom:14px">
    <i class="bi bi-arrow-right"></i> العودة لصندوق الرسائل
  </a>

  <div id="mainContent" style="text-align:center;padding:60px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>جاري التحميل...
  </div>
</div>

<?php include __DIR__ . '/../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const user  = window.AppAuth.getUser();
  const msgId = new URLSearchParams(location.search).get('id');
  if (!msgId) { alert('معرّف الرسالة مفقود'); return; }

  const PRIORITY_LABELS = { normal:'عادي', high:'مهم', urgent:'عاجل' };
  const PRIORITY_CLS    = { normal:'p-normal', high:'p-high', urgent:'p-urgent' };

  const json = await fetch(`${base}/inbox/${msgId}`, { headers:{ 'Authorization':`Bearer ${token()}` } }).then(r=>r.json());
  const msg  = json.data;
  if (!msg) { document.getElementById('mainContent').innerHTML='<div style="color:#dc2626;text-align:center">تعذّر تحميل الرسالة</div>'; return; }

  const senderName = msg.sender?.name || 'النظام';
  const isOwn = msg.sender_id === user?.id;

  let html = `
    <div class="msg-card">
      <div class="msg-header">
        <div class="msg-avatar">${senderName.charAt(0)}</div>
        <div style="flex:1">
          <div class="msg-subject">${window.APP_HELPERS.e(msg.subject)}</div>
          <div class="meta-row">
            <span><i class="bi bi-person-fill"></i> ${window.APP_HELPERS.e(senderName)}</span>
            ${msg.is_broadcast ? '<span><i class="bi bi-broadcast"></i> بث عام</span>' : (msg.recipient ? `<span><i class="bi bi-send-fill"></i> إلى: ${window.APP_HELPERS.e(msg.recipient.name)}</span>` : '')}
            <span><i class="bi bi-calendar3"></i> ${new Date(msg.created_at).toLocaleString('ar-SY')}</span>
            <span class="priority-badge ${PRIORITY_CLS[msg.priority]}">${PRIORITY_LABELS[msg.priority]}</span>
            ${msg.requires_reply ? '<span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:.77rem;font-weight:700"><i class="bi bi-reply-fill"></i> يستلزم رداً</span>' : ''}
          </div>
        </div>
      </div>
      <div class="msg-body">${window.APP_HELPERS.e(msg.body)}</div>
    </div>`;

  // الردود
  if (msg.replies?.length) {
    html += `<div style="margin-bottom:14px">
      <div style="font-size:.85rem;font-weight:700;color:var(--c-muted);margin-bottom:8px"><i class="bi bi-chat-text"></i> الردود (${msg.replies.length})</div>
      ${msg.replies.map(r=>`
        <div class="reply-card">
          <div class="reply-from"><i class="bi bi-reply-fill"></i> ${window.APP_HELPERS.e(r.sender?.name||'مجهول')} — ${new Date(r.created_at).toLocaleString('ar-SY')}</div>
          <div class="reply-body">${window.APP_HELPERS.e(r.body)}</div>
        </div>`).join('')}
    </div>`;
  }

  // نموذج الرد
  const canReply = !isOwn || msg.sender_id === user?.id;
  if (canReply) {
    html += `<div class="reply-form">
      <div style="font-weight:800;font-size:.95rem;color:var(--c-text);margin-bottom:14px;display:flex;align-items:center;gap:8px"><i class="bi bi-reply-fill" style="color:var(--c-primary)"></i> الرد على الرسالة</div>
      <textarea class="reply-textarea" id="replyBody" placeholder="اكتب ردك هنا..."></textarea>
      <div id="replyError" style="color:#dc2626;font-size:.84rem;margin-top:6px;display:none"></div>
      <div style="margin-top:12px">
        <button class="btn-send" onclick="sendReply()"><i class="bi bi-send-fill"></i> إرسال الرد</button>
      </div>
    </div>`;
  }

  document.getElementById('mainContent').innerHTML = html;

  window.sendReply = async function() {
    const body = document.getElementById('replyBody').value.trim();
    if (!body) { document.getElementById('replyError').style.display='block'; document.getElementById('replyError').textContent='الرد لا يمكن أن يكون فارغاً'; return; }
    const r = await fetch(`${base}/inbox/${msgId}/reply`, {
      method:'POST',
      headers:{ 'Authorization':`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify({ body }),
    });
    const json = await r.json();
    if (!r.ok) { document.getElementById('replyError').style.display='block'; document.getElementById('replyError').textContent=json.message||'خطأ'; return; }
    alert('تم إرسال الرد');
    location.reload();
  };
});
</script>
</body>
</html>
