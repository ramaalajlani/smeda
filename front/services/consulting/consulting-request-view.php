<?php
$basePath  = '../../';
$pageTitle = 'تفاصيل طلب الاستشارة';
$activePage= 'consulting';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --c-primary:#17947B; --c-accent:#06AA89; --c-soft:#EAF8F4; --c-border:rgba(23,148,123,.13); --c-text:#16332E; --c-muted:#6B7280; --c-shadow:0 10px 28px rgba(15,79,71,.07); }
    body { background:linear-gradient(160deg,#f4fbf8,#eef9f5); }
    .page-wrap { max-width:1000px; margin:auto; padding:24px 14px; }

    /* Timeline */
    .timeline { position:relative; padding-right:24px; }
    .timeline::before { content:""; position:absolute; right:8px; top:0; bottom:0; width:2px; background:var(--c-border); }
    .tl-item { position:relative; margin-bottom:14px; padding-right:28px; }
    .tl-dot { position:absolute; right:-4px; top:4px; width:14px; height:14px; border-radius:50%; background:var(--c-accent); border:2px solid #fff; box-shadow:0 0 0 3px rgba(6,170,137,.15); }
    .tl-date { font-size:.76rem; color:var(--c-muted); font-weight:700; margin-bottom:2px; }
    .tl-text { font-size:.88rem; font-weight:700; color:var(--c-text); }

    .info-card { background:#fff; border:1px solid var(--c-border); border-radius:20px; padding:20px; box-shadow:var(--c-shadow); margin-bottom:16px; }
    .info-card-title { font-weight:800; font-size:1rem; color:var(--c-text); margin:0 0 14px; display:flex; align-items:center; gap:8px; }
    .info-card-title i { color:var(--c-primary); }

    .detail-row { display:grid; grid-template-columns:160px 1fr; gap:6px 12px; font-size:.88rem; margin-bottom:8px; }
    .detail-label { font-weight:700; color:var(--c-muted); }
    .detail-value { color:var(--c-text); font-weight:600; }

    /* Status badge */
    .status-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px; font-weight:700; font-size:.85rem; }
    .sb-draft    { background:#f3f4f6; color:#6b7280; }
    .sb-submitted{ background:#dbeafe; color:#1d4ed8; }
    .sb-needs_info{ background:#fef3c7; color:#92400e; }
    .sb-awaiting_offers{ background:#e0e7ff; color:#4338ca; }
    .sb-in_progress{ background:#ecfdf5; color:#065f46; }
    .sb-report_submitted{ background:#f0f9ff; color:#0369a1; }
    .sb-completed{ background:#dcfce7; color:#166534; }
    .sb-rejected { background:#fee2e2; color:#dc2626; }
    .sb-offer_submitted{ background:#f0fdf4; color:#166534; }

    /* Offers */
    .offer-card { border:1px solid var(--c-border); border-radius:16px; padding:16px; margin-bottom:12px; background:#fff; }
    .offer-card:hover { border-color:var(--c-accent); }
    .offer-price { font-size:1.3rem; font-weight:800; color:var(--c-primary); }
    .btn-sm-primary { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:10px; padding:8px 16px; font-weight:700; cursor:pointer; font-size:.84rem; transition:opacity .2s; }
    .btn-sm-primary:hover { opacity:.9; }

    /* Messages */
    .msg-wrap { display:flex; flex-direction:column; gap:10px; max-height:400px; overflow-y:auto; padding:4px; }
    .msg-bubble { padding:12px 16px; border-radius:16px; max-width:80%; font-size:.88rem; line-height:1.7; }
    .msg-mine { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
    .msg-other { background:#f3f4f6; color:var(--c-text); align-self:flex-start; border-bottom-left-radius:4px; }
    .msg-meta { font-size:.72rem; opacity:.75; margin-top:4px; }
    .msg-input-wrap { display:flex; gap:8px; margin-top:12px; }
    .msg-input { flex:1; border:1px solid var(--c-border); border-radius:12px; padding:10px 14px; font-size:.88rem; }
    .msg-send { background:linear-gradient(135deg,var(--c-primary),var(--c-accent)); color:#fff; border:none; border-radius:12px; padding:10px 18px; cursor:pointer; font-size:.9rem; }

    /* Action buttons */
    .action-bar { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px; }
    .btn-action { display:inline-flex; align-items:center; gap:7px; padding:10px 18px; border-radius:14px; font-weight:700; font-size:.88rem; cursor:pointer; border:none; transition:all .2s; text-decoration:none; }
    .btn-success-act { background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; }
    .btn-warning-act { background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; }
    .btn-purple-act  { background:linear-gradient(135deg,#7c3aed,#a78bfa); color:#fff; }
    .btn-red-act     { background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; }
    .btn-outline-act { background:#fff; border:1px solid var(--c-border); color:var(--c-text); }

    /* Stars rating */
    .stars { display:flex; gap:4px; }
    .star { font-size:1.4rem; cursor:pointer; color:#d1d5db; transition:color .15s; }
    .star.active { color:#f59e0b; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../includes/layout/header.php'; ?>
<main>
<div class="page-wrap">

  <div style="margin-bottom:16px">
    <a href="consulting-requests-list.php" style="color:var(--c-primary);font-weight:700;text-decoration:none;font-size:.9rem">
      <i class="bi bi-arrow-right"></i> العودة للطلبات
    </a>
  </div>

  <div id="mainContent">
    <div style="text-align:center;padding:60px;color:var(--c-muted)">
      <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
      جاري التحميل...
    </div>
  </div>

</div>

</main>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({
    requireAuth: true,
    requiredAnyRoles: window.AppPermissions.CONSULTING_REQUEST_ACCESS_ROLES,
  });
  if (!ok) return;

  const base   = window.APP_CONFIG.API_BASE_URL;
  const token  = () => window.AppAuth.getToken();
  const user   = window.AppAuth.getUser();
  const reqId  = new URLSearchParams(location.search).get('id');
  if (!reqId) { alert('معرّف الطلب مفقود'); return; }

  const STATUS_LABELS = {
    draft:'مسودة', submitted:'مُرسَل', needs_info:'بحاجة معلومات',
    sorting:'قيد الفرز', awaiting_offers:'بانتظار عروض',
    offer_submitted:'عرض مقدم', in_progress:'قيد التنفيذ',
    report_submitted:'تقرير مرفوع', awaiting_authority_approval:'بانتظار اعتماد الهيئة',
    completed:'مكتمل', transferred_financing:'محوّل للتمويل',
    transferred_training:'محوّل للتدريب', rejected:'مرفوض',
  };

  const canAdminRequest = window.AppAuth.hasAnyRole?.(window.AppPermissions.CONSULTING_ADMIN_ROLES);
  const isBranch = window.AppAuth.hasAnyRole?.(['branch_manager', 'branch_officer']);

  async function apiGet(url) {
    const r = await fetch(url, { headers: { 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' }});
    return r.json();
  }

  async function apiPost(url, body={}, isForm=false) {
    const opts = { method:'POST', headers: { 'Authorization':`Bearer ${token()}`, 'Accept':'application/json' }};
    if (isForm) { opts.body = body; }
    else { opts.headers['Content-Type']='application/json'; opts.body = JSON.stringify(body); }
    const r = await fetch(url, opts);
    return r.json();
  }

  async function load() {
    const json = await apiGet(`${base}/consulting/requests/${reqId}`);
    const req  = json.data || json;
    render(req);
  }

  function renderStars(n) {
    return [1,2,3,4,5].map(i => `<span class="star ${i<=n?'active':''}">${i<=n?'★':'☆'}</span>`).join('');
  }

  function render(req) {
    const isOwner    = req.user_id === (user?.id);
    const status     = req.status;
    const contract   = req.contract;
    const hasOffers  = req.offers?.length > 0;
    const statusCls  = 'sb-' + (status.replace(/_/g,'-') in ['sb-draft','sb-submitted'] ? status : status);

    let html = `
      <!-- Header -->
      <div style="display:flex;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:16px">
        <div style="flex:1">
          <div style="font-size:.8rem;color:var(--c-muted);font-weight:700;margin-bottom:4px">${req.request_code || ''}</div>
          <h2 style="font-weight:800;font-size:1.3rem;color:var(--c-text);margin:0 0 8px">${window.APP_HELPERS.e(req.title)}</h2>
          <span class="status-badge sb-${status}" style="background:${statusColor(status)};color:${statusTextColor(status)}">${STATUS_LABELS[status]||status}</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="action-bar" id="actionBar">`;

    // أزرار حسب الدور والحالة
    if ((canAdminRequest || isBranch) && status === 'submitted') {
      html += `
        <button class="btn-action btn-success-act" onclick="sortReq('approve')"><i class="bi bi-check-circle-fill"></i> قبول وتوجيه للمكاتب</button>
        <button class="btn-action btn-warning-act" onclick="sortReq('needs_info')"><i class="bi bi-question-circle-fill"></i> طلب معلومات إضافية</button>`;
    }

    if (isOwner && status === 'offer_submitted') {
      html += `<button class="btn-action btn-success-act" onclick="scrollToOffers()"><i class="bi bi-list-check"></i> عرض العروض والاختيار</button>`;
    }

    if ((canAdminRequest || isBranch) && status === 'report_submitted') {
      html += `
        <button class="btn-action btn-success-act" onclick="approveReport()"><i class="bi bi-patch-check-fill"></i> اعتماد التقرير</button>
        <button class="btn-action btn-red-act" onclick="returnReport()"><i class="bi bi-arrow-counterclockwise"></i> إعادة للمكتب</button>`;
    }

    if ((canAdminRequest || isBranch) && status === 'completed') {
      html += `
        <button class="btn-action btn-purple-act" onclick="transfer('financing')"><i class="bi bi-bank2"></i> تحويل للتمويل</button>
        <button class="btn-action btn-purple-act" onclick="transfer('training')"><i class="bi bi-mortarboard-fill"></i> تحويل للتدريب</button>
        <button class="btn-action btn-purple-act" onclick="transfer('gis')"><i class="bi bi-geo-alt-fill"></i> تحويل لـ GIS</button>`;
    }

    html += `</div>

      <!-- Info -->
      <div class="info-card">
        <div class="info-card-title"><i class="bi bi-info-circle-fill"></i> تفاصيل الطلب</div>
        <div class="detail-row"><span class="detail-label">نوع الاستشارة</span><span class="detail-value">${req.category_code||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">تاريخ الإرسال</span><span class="detail-value">${req.submitted_at ? new Date(req.submitted_at).toLocaleDateString('ar-SY') : '—'}</span></div>
        <div class="detail-row"><span class="detail-label">المحافظة</span><span class="detail-value">${req.governorate?.name_ar||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">الفرع</span><span class="detail-value">${req.branch?.name||'—'}</span></div>
        ${req.budget_max ? `<div class="detail-row"><span class="detail-label">الميزانية</span><span class="detail-value">${Number(req.budget_min||0).toLocaleString('ar')} — ${Number(req.budget_max).toLocaleString('ar')} ل.س</span></div>` : ''}
        ${req.expected_duration_days ? `<div class="detail-row"><span class="detail-label">المدة المطلوبة</span><span class="detail-value">${req.expected_duration_days} يوم</span></div>` : ''}
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--c-border)">
          <div class="detail-label" style="margin-bottom:6px">وصف الطلب</div>
          <p style="font-size:.9rem;color:var(--c-text);line-height:1.8;margin:0">${window.APP_HELPERS.e(req.description)}</p>
        </div>
        ${req.branch_notes ? `<div style="margin-top:12px;background:#fef3c7;border-radius:10px;padding:12px"><strong>ملاحظات الفرع:</strong> ${window.APP_HELPERS.e(req.branch_notes)}</div>` : ''}
      </div>`;

    // العروض
    if (req.offers?.length) {
      html += `<div class="info-card" id="offersSection">
        <div class="info-card-title"><i class="bi bi-collection-fill"></i> العروض المقدمة (${req.offers.length})</div>`;

      req.offers.forEach(offer => {
        const canAccept = isOwner && offer.status === 'pending' && (status === 'offer_submitted' || status === 'awaiting_offers');
        html += `<div class="offer-card">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap">
            <strong>${window.APP_HELPERS.e(offer.office?.name||'مكتب')}</strong>
            <span style="color:var(--c-muted);font-size:.8rem">⭐ ${offer.office?.overall_rating||'—'}</span>
            <span class="offer-price" style="margin-right:auto">${Number(offer.price).toLocaleString('ar')} ل.س</span>
            <span style="font-size:.8rem;color:var(--c-muted)">${offer.proposed_duration_days} يوم</span>
          </div>
          <p style="font-size:.86rem;color:var(--c-muted);margin:0 0 10px;line-height:1.7">${window.APP_HELPERS.e(offer.methodology_text).substring(0,200)}...</p>
          ${canAccept ? `<button class="btn-sm-primary" onclick="acceptOffer(${offer.id})"><i class="bi bi-check-circle-fill"></i> قبول هذا العرض</button>` : ''}
          ${offer.status==='accepted' ? '<span style="color:#16a34a;font-weight:700"><i class="bi bi-check-circle-fill"></i> عرض مقبول</span>' : ''}
        </div>`;
      });
      html += `</div>`;
    }

    // العقد والرسائل
    if (contract) {
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-file-earmark-check-fill"></i> العقد والتواصل</div>
        <div class="detail-row"><span class="detail-label">رقم العقد</span><span class="detail-value">${contract.contract_code||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">تاريخ البدء</span><span class="detail-value">${contract.start_date||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">تاريخ الانتهاء المتوقع</span><span class="detail-value">${contract.expected_end_date||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">القيمة الكلية</span><span class="detail-value">${Number(contract.total_value).toLocaleString('ar')} ل.س</span></div>
        <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap">
          ${!contract.signed_by_client_at && isOwner ? `<button class="btn-sm-primary" onclick="signContract(${contract.id})"><i class="bi bi-pen-fill"></i> توقيع العقد</button>` : ''}
          ${contract.signed_by_client_at ? '<span style="color:#16a34a;font-size:.85rem;font-weight:700"><i class="bi bi-check2-all"></i> تم التوقيع</span>' : ''}
        </div>

        <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--c-border)">
          <div class="info-card-title" style="margin-bottom:10px"><i class="bi bi-chat-text-fill"></i> الرسائل الداخلية</div>
          <div class="msg-wrap" id="msgWrap">جاري تحميل الرسائل...</div>
          <div class="msg-input-wrap">
            <input type="text" class="msg-input" id="msgInput" placeholder="اكتب رسالتك...">
            <button class="msg-send" onclick="sendMsg(${contract.id})"><i class="bi bi-send-fill"></i></button>
          </div>
        </div>
      </div>`;
    }

    // التقرير النهائي
    if (contract?.report) {
      const rep = contract.report;
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-file-earmark-pdf-fill"></i> التقرير النهائي</div>
        <div class="detail-row"><span class="detail-label">حالة المراجعة</span><span class="detail-value">${rep.review_status==='approved'?'✅ معتمد':rep.review_status==='returned'?'⚠️ أُعيد للمكتب':'⏳ بانتظار المراجعة'}</span></div>
        ${rep.recommendation_type&&rep.recommendation_type!=='none'?`<div class="detail-row"><span class="detail-label">التوصية</span><span class="detail-value" style="color:#7c3aed;font-weight:700">${rep.recommendation_type}</span></div>`:''}
        ${rep.reviewer_notes?`<div style="background:#f0f9ff;border-radius:10px;padding:12px;margin-top:10px;font-size:.86rem">${window.APP_HELPERS.e(rep.reviewer_notes)}</div>`:''}
        <a href="${window.APP_CONFIG.BACKEND_BASE_URL}/storage/${rep.report_pdf_path}" target="_blank" class="btn-action btn-outline-act" style="margin-top:12px;display:inline-flex">
          <i class="bi bi-download"></i> تحميل التقرير
        </a>
      </div>`;
    }

    // التقييم
    if (isOwner && status === 'completed' && !contract?.review) {
      html += `<div class="info-card">
        <div class="info-card-title"><i class="bi bi-star-fill"></i> قيّم المكتب الاستشاري</div>
        <div class="stars" id="ratingStars">
          ${[1,2,3,4,5].map(i=>`<span class="star" onclick="setRating(${i})">☆</span>`).join('')}
        </div>
        <textarea class="form-control" id="ratingComment" placeholder="تعليقك..." style="margin-top:12px;border:1px solid var(--c-border);border-radius:12px;padding:10px;width:100%;min-height:80px"></textarea>
        <button class="btn-sm-primary" onclick="submitReview(${contract?.id})" style="margin-top:12px"><i class="bi bi-send-fill"></i> إرسال التقييم</button>
      </div>`;
    }

    document.getElementById('mainContent').innerHTML = html;

    if (contract) loadMessages(contract.id);
  }

  function statusColor(s) {
    const m = {
      draft:'#f3f4f6',submitted:'#dbeafe',needs_info:'#fef3c7',
      awaiting_offers:'#e0e7ff',offer_submitted:'#f0fdf4',in_progress:'#ecfdf5',
      report_submitted:'#f0f9ff',completed:'#dcfce7',rejected:'#fee2e2',
    };
    return m[s]||'#f3f4f6';
  }
  function statusTextColor(s) {
    const m = {
      draft:'#6b7280',submitted:'#1d4ed8',needs_info:'#92400e',
      awaiting_offers:'#4338ca',offer_submitted:'#166534',in_progress:'#065f46',
      report_submitted:'#0369a1',completed:'#166534',rejected:'#dc2626',
    };
    return m[s]||'#6b7280';
  }

  async function loadMessages(contractId) {
    const json = await apiGet(`${base}/consulting/contracts/${contractId}/messages`);
    const msgs = json.data || [];
    const uid  = user?.id;
    document.getElementById('msgWrap').innerHTML = msgs.length ? msgs.map(m => `
      <div>
        <div class="msg-bubble ${m.sender_id===uid?'msg-mine':'msg-other'}">
          ${window.APP_HELPERS.e(m.message_text)}
          <div class="msg-meta">${m.sender?.name||''} · ${new Date(m.sent_at||m.created_at).toLocaleTimeString('ar-SY',{hour:'2-digit',minute:'2-digit'})}</div>
        </div>
      </div>`).join('') : '<div style="text-align:center;color:var(--c-muted);font-size:.85rem">لا توجد رسائل بعد</div>';
    const w = document.getElementById('msgWrap');
    if(w) w.scrollTop = w.scrollHeight;
  }

  let myRating = 0;
  window.setRating = function(n) {
    myRating = n;
    document.querySelectorAll('.star').forEach((s,i) => { s.textContent = i<n?'★':'☆'; s.classList.toggle('active',i<n); });
  };

  window.scrollToOffers = function() {
    document.getElementById('offersSection')?.scrollIntoView({behavior:'smooth'});
  };

  window.acceptOffer = async function(offerId) {
    if (!confirm('هل تريد قبول هذا العرض؟ سيُنشأ العقد فوراً.')) return;
    const r = await apiPost(`${base}/consulting/requests/${reqId}/accept-offer`, { offer_id: offerId });
    alert(r.message || 'تم');
    load();
  };

  window.signContract = async function(cid) {
    const r = await apiPost(`${base}/consulting/contracts/${cid}/sign`);
    alert(r.message||'تم التوقيع');
    load();
  };

  window.sendMsg = async function(cid) {
    const txt = document.getElementById('msgInput').value.trim();
    if (!txt) return;
    await apiPost(`${base}/consulting/contracts/${cid}/messages`, { message_text: txt });
    document.getElementById('msgInput').value = '';
    loadMessages(cid);
  };

  window.sortReq = async function(action) {
    const notes = action==='needs_info' ? prompt('اذكر ما ينقص في الطلب:') : null;
    const r = await apiPost(`${base}/consulting/requests/${reqId}/sort`, { action, branch_notes: notes||undefined });
    alert(r.message||'تم');
    load();
  };

  window.approveReport = async function() {
    const contract = (await apiGet(`${base}/consulting/requests/${reqId}`)).data?.contract;
    if (!contract) return;
    const r = await apiPost(`${base}/consulting/contracts/${contract.id}/approve-report`, { action:'approve' });
    alert(r.message||'تم الاعتماد');
    load();
  };

  window.returnReport = async function() {
    const contract = (await apiGet(`${base}/consulting/requests/${reqId}`)).data?.contract;
    if (!contract) return;
    const notes = prompt('ملاحظات الإعادة:');
    const r = await apiPost(`${base}/consulting/contracts/${contract.id}/approve-report`, { action:'return', reviewer_notes: notes });
    alert(r.message||'تم');
    load();
  };

  window.transfer = async function(target) {
    if (!confirm(`تحويل الطلب إلى قسم ${target}?`)) return;
    const r = await apiPost(`${base}/consulting/requests/${reqId}/transfer`, { target });
    alert(r.message||'تم التحويل');
    load();
  };

  window.submitReview = async function(cid) {
    if (!myRating) { alert('اختر تقييماً من 1 إلى 5 نجوم'); return; }
    const comment = document.getElementById('ratingComment').value;
    const r = await apiPost(`${base}/consulting/contracts/${cid}/review`, {
      overall_rating: myRating, comment
    });
    alert(r.message||'شكراً على تقييمك');
    load();
  };

  load();
});
</script>
</body>
</html>
