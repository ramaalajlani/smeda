<?php
$basePath  = '../../';
$pageTitle = 'تفاصيل القرض';
$activePage= 'finance';
?>
<?php include __DIR__ . '/../../includes/layout/html-open.php'; ?>
<head>
  <?php include __DIR__ . '/../../includes/layout/head.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root{--c-primary:#17947B;--c-accent:#06AA89;--c-soft:#EAF8F4;--c-border:rgba(23,148,123,.13);--c-text:#16332E;--c-muted:#6B7280;--c-shadow:0 10px 28px rgba(15,79,71,.07);}
    body{background:linear-gradient(160deg,#f0faf7,#e8f7f3);}
    .pw{max-width:960px;margin:auto;padding:22px 14px;}
    .page-head{background:linear-gradient(135deg,#0f5e4f,var(--c-primary));border-radius:20px;padding:24px 28px;color:#fff;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
    .page-head h1{margin:0;font-size:1.35rem;font-weight:800;}
    .card{background:#fff;border:1px solid var(--c-border);border-radius:20px;padding:22px;box-shadow:var(--c-shadow);margin-bottom:16px;}
    .card-title{font-weight:800;font-size:.97rem;color:var(--c-text);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
    .card-title i{color:var(--c-primary);}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;}
    .info-item{background:var(--c-soft);border-radius:12px;padding:12px 14px;}
    .info-lbl{font-size:.76rem;font-weight:700;color:var(--c-muted);margin-bottom:4px;}
    .info-val{font-size:.92rem;font-weight:800;color:var(--c-text);}
    .tbl{width:100%;border-collapse:collapse;font-size:.87rem;}
    .tbl th{background:var(--c-soft);color:var(--c-text);font-weight:800;padding:9px 12px;text-align:right;}
    .tbl td{padding:9px 12px;border-bottom:1px solid var(--c-border);color:var(--c-text);}
    .tbl tr:last-child td{border-bottom:none;}
    .badge-status{padding:3px 10px;border-radius:20px;font-size:.74rem;font-weight:700;display:inline-block;}
    .bs-paid{background:#dcfce7;color:#15803d;}
    .bs-pending{background:#fef3c7;color:#92400e;}
    .bs-late{background:#fee2e2;color:#dc2626;}
    .bs-partial{background:#dbeafe;color:#1d4ed8;}
    .bs-defaulted{background:#fce7f3;color:#9d174d;}
    .form-group{margin-bottom:14px;}
    .form-label{font-size:.81rem;font-weight:700;color:var(--c-muted);display:block;margin-bottom:5px;}
    .form-control,.form-select{width:100%;border:1px solid var(--c-border);border-radius:11px;padding:9px 12px;font-size:.88rem;color:var(--c-text);box-sizing:border-box;}
    .form-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
    @media(max-width:640px){.form-row{grid-template-columns:1fr 1fr;}}
    .btn-primary{background:linear-gradient(135deg,var(--c-primary),var(--c-accent));color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;font-size:.88rem;}
    .btn-danger{background:#fee2e2;color:#dc2626;border:none;border-radius:12px;padding:10px 22px;font-weight:700;cursor:pointer;font-size:.88rem;}
    .btn-outline{background:#fff;border:1px solid var(--c-border);color:var(--c-text);border-radius:12px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
    .alert-s{background:#dcfce7;color:#14532d;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .alert-e{background:#fee2e2;color:#991b1b;border-radius:12px;padding:10px 14px;font-weight:700;font-size:.87rem;display:none;margin-bottom:12px;}
    .empty{text-align:center;padding:30px;color:var(--c-muted);font-size:.88rem;}
  </style>
</head>
<body>
<div class="pw">
  <div class="page-head">
    <div>
      <h1><i class="bi bi-cash-stack"></i> <span id="loanTitle">تفاصيل القرض</span></h1>
      <div style="opacity:.8;font-size:.85rem;margin-top:4px" id="loanSubtitle"></div>
    </div>
    <a href="finance-funded.php" class="btn-outline" style="color:#fff;border-color:rgba(255,255,255,.3)"><i class="bi bi-arrow-right"></i> رجوع</a>
  </div>

  <div id="loading" style="text-align:center;padding:50px;color:var(--c-muted)">
    <i class="bi bi-hourglass-split" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4"></i>جاري التحميل...
  </div>

  <div id="content" style="display:none">
    <div class="card">
      <div class="card-title"><i class="bi bi-info-circle-fill"></i> تفاصيل القرض</div>
      <div class="info-grid" id="infoGrid"></div>
    </div>

    <div class="card" id="actionsCard" style="display:none">
      <div class="card-title"><i class="bi bi-gear-fill"></i> إجراءات</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap" id="actionsArea"></div>
      <div id="actSuccess" class="alert-s" style="margin-top:12px"></div>
      <div id="actError"   class="alert-e" style="margin-top:12px"></div>
    </div>

    <div class="card">
      <div class="card-title"><i class="bi bi-calendar3"></i> جدول الدفعات</div>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead><tr>
            <th>تاريخ الاستحقاق</th><th>تاريخ الدفع</th>
            <th>المبلغ المستحق</th><th>المبلغ المدفوع</th><th>الحالة</th>
          </tr></thead>
          <tbody id="paymentsBody"></tbody>
        </table>
      </div>
    </div>

    <div class="card" id="payFormCard" style="display:none">
      <div class="card-title"><i class="bi bi-plus-circle-fill"></i> تسجيل دفعة جديدة</div>
      <div id="paySuccess" class="alert-s"></div>
      <div id="payError"   class="alert-e"></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">تاريخ الاستحقاق *</label><input type="date" id="payDue" class="form-control"></div>
        <div class="form-group"><label class="form-label">تاريخ الدفع</label><input type="date" id="payPaid" class="form-control"></div>
        <div class="form-group"><label class="form-label">المبلغ المستحق *</label><input type="number" id="payAmtDue" class="form-control" min="0" step="0.01"></div>
        <div class="form-group"><label class="form-label">المبلغ المدفوع</label><input type="number" id="payAmtPaid" class="form-control" min="0" step="0.01"></div>
        <div class="form-group"><label class="form-label">الحالة</label>
          <select id="payStatus" class="form-select">
            <option value="pending">معلق</option><option value="paid">مدفوع</option>
            <option value="late">متأخر</option><option value="partial">جزئي</option><option value="defaulted">متعثر</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">ملاحظات</label><input type="text" id="payNotes" class="form-control"></div>
      </div>
      <button class="btn-primary" onclick="savePayment()"><i class="bi bi-save-fill"></i> حفظ الدفعة</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout/scripts.php'; ?>
<script src="<?php echo $basePath; ?>assets/js/pages/finance-platform.js?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  const id = new URLSearchParams(location.search).get('id');
  if (!id) { location.href = 'finance-funded.php'; return; }

  if (!window.AppAuth.hasPermission('finance.loans.view') && !window.FinancePlatform.canViewApplications()) {
    location.href = window.APP_CONFIG.FORBIDDEN_PAGE; return;
  }

  const base  = window.APP_CONFIG.API_BASE_URL;
  const token = () => window.AppAuth.getToken();
  const canManage   = window.AppAuth.hasPermission('finance.loans.manage');
  const canPayments = window.AppAuth.hasPermission('finance.loans.payments');
  const canDefault  = window.AppAuth.hasPermission('finance.loans.defaulted');
  const canClose    = window.AppAuth.hasPermission('finance.loans.close');

  const PAY_BADGES = { paid:'bs-paid',pending:'bs-pending',late:'bs-late',partial:'bs-partial',defaulted:'bs-defaulted' };

  async function loadLoan() {
    const r = await fetch(`${base}/finance/loans/${id}`, { headers:{ Authorization:`Bearer ${token()}` }});
    if (!r.ok) throw new Error('تعذر تحميل القرض');
    const json = await r.json();
    const loan = json.data ?? json;

    document.getElementById('loanTitle').textContent   = 'قرض ' + loan.loan_number;
    document.getElementById('loanSubtitle').textContent = loan.application?.project_name ?? '';

    const items = [
      ['رقم القرض',      loan.loan_number],
      ['المشروع',        loan.application?.project_name],
      ['جهة التمويل',   loan.partner?.name],
      ['المبلغ المعتمد', window.FinancePlatform.formatAmount(loan.approved_amount, loan.currency)],
      ['عدد الأقساط',   (loan.installment_count??'—') + ' قسط'],
      ['قيمة القسط',    window.FinancePlatform.formatAmount(loan.installment_amount, loan.currency)],
      ['تاريخ البدء',   loan.start_date ?? '—'],
      ['تاريخ الانتهاء',loan.end_date ?? '—'],
      ['الحالة',         window.FinancePlatform.statusLabel(loan.status)],
    ];
    document.getElementById('infoGrid').innerHTML = items.map(([l,v]) =>
      `<div class="info-item"><div class="info-lbl">${l}</div><div class="info-val">${window.APP_HELPERS.e(v??'—')}</div></div>`
    ).join('');

    // Actions
    const acts = [];
    if (canDefault && loan.status !== 'defaulted') acts.push(`<button class="btn-danger" onclick="doAction('mark-defaulted')"><i class="bi bi-exclamation-triangle-fill"></i> تعليم كمتعثر</button>`);
    if (canClose && !['closed','paid'].includes(loan.status)) acts.push(`<button class="btn-outline" onclick="doAction('close')"><i class="bi bi-x-circle-fill"></i> إغلاق القرض</button>`);
    if (acts.length) {
      document.getElementById('actionsCard').style.display = '';
      document.getElementById('actionsArea').innerHTML = acts.join('');
    }

    // Payments
    const payments = loan.payments ?? [];
    document.getElementById('paymentsBody').innerHTML = payments.length
      ? payments.map(p => `<tr>
          <td>${window.APP_HELPERS.e(p.due_date??'—')}</td>
          <td>${window.APP_HELPERS.e(p.paid_date??'—')}</td>
          <td style="font-weight:700">${window.FinancePlatform.formatAmount(p.amount_due, loan.currency)}</td>
          <td>${window.FinancePlatform.formatAmount(p.amount_paid, loan.currency)}</td>
          <td><span class="badge-status ${PAY_BADGES[p.status]||'bs-pending'}">${window.FinancePlatform.statusLabel(p.status)}</span></td>
        </tr>`).join('')
      : '<tr><td colspan="5" class="empty">لا توجد دفعات مسجلة</td></tr>';

    if (canPayments || canManage) document.getElementById('payFormCard').style.display = '';
  }

  window.doAction = async function(action) {
    const r = await fetch(`${base}/finance/loans/${id}/${action}`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' }, body:'{}'
    });
    const json = await r.json();
    const s = document.getElementById('actSuccess');
    const e = document.getElementById('actError');
    if (r.ok) { s.textContent='✓ تم التنفيذ'; s.style.display='block'; setTimeout(()=>{ s.style.display='none'; loadLoan(); },1200); }
    else { e.textContent = json.message||'خطأ'; e.style.display='block'; setTimeout(()=>e.style.display='none',4000); }
  };

  window.savePayment = async function() {
    const payload = {
      due_date:       document.getElementById('payDue').value,
      paid_date:      document.getElementById('payPaid').value||null,
      amount_due:     Number(document.getElementById('payAmtDue').value||0),
      amount_paid:    Number(document.getElementById('payAmtPaid').value||0),
      status:         document.getElementById('payStatus').value,
      notes:          document.getElementById('payNotes').value.trim()||null,
    };
    const r = await fetch(`${base}/finance/loans/${id}/payments`, {
      method:'POST', headers:{ Authorization:`Bearer ${token()}`, 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await r.json();
    const s = document.getElementById('paySuccess');
    const e = document.getElementById('payError');
    if (r.ok) { s.textContent='✓ تم تسجيل الدفعة'; s.style.display='block'; setTimeout(()=>{ s.style.display='none'; loadLoan(); },1200); }
    else { e.textContent = Object.values(json.errors??{}).flat()[0]||json.message||'خطأ'; e.style.display='block'; setTimeout(()=>e.style.display='none',4000); }
  };

  try {
    await loadLoan();
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = '';
  } catch(e) {
    document.getElementById('loading').innerHTML = `<div style="color:#dc2626;font-weight:700">${e.message}</div>`;
  }
});
</script>
</body>
</html>
