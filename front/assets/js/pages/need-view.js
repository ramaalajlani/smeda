document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok || !window.NeedsPlatform.canView()) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const params = new URLSearchParams(window.location.search);
  const id = params.get('id');
  if (!id) {
    window.location.href = 'needs-list.php';
    return;
  }

  const body    = document.getElementById('needViewBody');
  const actions = document.getElementById('needViewActions');
  const message = document.getElementById('needViewMessage');

  function showMessage(text, type = 'success') {
    message.className = `alert alert-${type}`;
    message.textContent = text;
    message.classList.remove('d-none');
    message.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /* ══ Modal مشترك للإجراءات التي تحتاج سبباً ══ */
  function injectModal() {
    if (document.getElementById('needActionModal')) return;
    document.body.insertAdjacentHTML('beforeend', `
      <div class="modal fade" id="needActionModal" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header border-0 pb-0">
              <h5 class="modal-title fw-bold" id="needActionModalTitle"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
              <label for="needActionReason" class="form-label text-muted small fw-semibold" id="needActionReasonLabel"></label>
              <textarea id="needActionReason" class="form-control" rows="3" placeholder="${SiteI18n.ta('اكتب هنا...')}"></textarea>
              <div id="needActionModalSelect" class="mt-3 d-none">
                <label class="form-label text-muted small fw-semibold">التدخل المقترح</label>
                <select id="needClassifySelect" class="form-select">
                  <option value="">${SiteI18n.ta('اختر التدخل...')}</option>
                  <option value="دراسة">دراسة</option>
                  <option value="تدريب">تدريب</option>
                  <option value="استشارات">استشارات</option>
                  <option value="تمويل">تمويل</option>
                  <option value="حاضنة">حاضنة</option>
                  <option value="بنية تحتية">بنية تحتية</option>
                </select>
              </div>
              <div id="needActionError" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
              <button type="button" class="btn btn-sm fw-semibold" id="needActionConfirmBtn"></button>
            </div>
          </div>
        </div>
      </div>`);
  }

  function openActionModal({ title, label, btnText, btnClass, requireReason = true, showSelect = false, onConfirm }) {
    injectModal();
    const modal      = document.getElementById('needActionModal');
    const titleEl    = document.getElementById('needActionModalTitle');
    const labelEl    = document.getElementById('needActionReasonLabel');
    const reasonEl   = document.getElementById('needActionReason');
    const selectWrap = document.getElementById('needActionModalSelect');
    const selectEl   = document.getElementById('needClassifySelect');
    const confirmBtn = document.getElementById('needActionConfirmBtn');
    const errorEl    = document.getElementById('needActionError');

    titleEl.textContent    = title;
    labelEl.textContent    = label || '';
    reasonEl.value         = '';
    selectEl.value         = '';
    errorEl.classList.add('d-none');
    confirmBtn.textContent = btnText;
    confirmBtn.className   = `btn btn-sm fw-semibold ${btnClass}`;
    selectWrap.classList.toggle('d-none', !showSelect);
    reasonEl.classList.toggle('d-none', showSelect && !requireReason);

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    setTimeout(() => (showSelect ? selectEl : reasonEl).focus(), 350);

    const handler = async () => {
      const reason = reasonEl.value.trim();
      const intervention = selectEl.value;

      if (requireReason && !showSelect && !reason) {
        errorEl.textContent = SiteI18n.ta('الرجاء كتابة السبب أولاً');
        errorEl.classList.remove('d-none');
        return;
      }
      if (showSelect && !intervention) {
        errorEl.textContent = SiteI18n.ta('الرجاء اختيار نوع التدخل');
        errorEl.classList.remove('d-none');
        return;
      }

      confirmBtn.disabled = true;
      confirmBtn.innerHTML = SiteI18n.ta('<span class="spinner-border spinner-border-sm me-1"></span>جاري التنفيذ...');

      try {
        await onConfirm({ reason, intervention });
        bsModal.hide();
      } catch (err) {
        errorEl.textContent = err?.message || SiteI18n.ta('حدث خطأ، أعد المحاولة');
        errorEl.classList.remove('d-none');
        confirmBtn.disabled = false;
        confirmBtn.textContent = btnText;
      }
    };

    confirmBtn.onclick = handler;
    modal.addEventListener('hidden.bs.modal', () => { confirmBtn.onclick = null; }, { once: true });
  }

  /* ══ بطاقات الوحدات المرتبطة ══ */
  function buildLinkedCards(need) {
    const cards = [];

    if (need.funding_application) {
      const fa = need.funding_application;
      const statusMap = { pending: SiteI18n.ta('قيد المراجعة'), approved: SiteI18n.ta('موافق عليه'), rejected: SiteI18n.ta('مرفوض'), draft: SiteI18n.ta('مسودة') };
      const href = `../../services/finance/finance-apply.php?id=${fa.id}`;
      cards.push(`
        <div class="col-12">
          <div class="linked-module-card linked-finance">
            <div class="lm-icon"><i class="bi bi-bank2"></i></div>
            <div class="lm-body">
              <div class="lm-label">مرتبط بطلب تمويل</div>
              <div class="lm-title">${window.APP_HELPERS.safe(fa.application_number || '#' + fa.id)}</div>
              <div class="lm-status">${statusMap[fa.status] || fa.status || '—'}</div>
            </div>
            <a href="${href}" class="lm-btn btn btn-sm btn-outline-primary">
              عرض الطلب <i class="bi bi-arrow-left ms-1"></i>
            </a>
          </div>
        </div>`);
    }

    if (need.training_course) {
      const tc = need.training_course;
      const href = `../../services/training/training-course-show.php?id=${tc.id}`;
      cards.push(`
        <div class="col-12">
          <div class="linked-module-card linked-training">
            <div class="lm-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="lm-body">
              <div class="lm-label">مرتبط بدورة تدريبية</div>
              <div class="lm-title">${window.APP_HELPERS.safe(tc.title || '#' + tc.id)}</div>
              <div class="lm-status">${window.APP_HELPERS.safe(tc.status || '')}</div>
            </div>
            <a href="${href}" class="lm-btn btn btn-sm btn-outline-success">
              عرض الدورة <i class="bi bi-arrow-left ms-1"></i>
            </a>
          </div>
        </div>`);
    }

    return cards.join('');
  }

  /* ══ أدوات تنسيق ══ */
  const s = window.APP_HELPERS.safe;

  function fmtDate(v) {
    if (!v) return '—';
    const d = new Date(v);
    if (isNaN(d.getTime())) return s(v);
    try { return d.toLocaleString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }); }
    catch (_) { return d.toISOString().slice(0, 16).replace('T', ' '); }
  }
  function fmtVal(key, val) {
    if (val === null || val === undefined || val === '') return '—';
    if (typeof val === 'boolean') return val ? 'نعم' : 'لا';
    if (/_at$/.test(key)) return fmtDate(val);
    if (Array.isArray(val)) {
      if (!val.length) return '—';
      return typeof val[0] !== 'object' ? val.map(s).join('، ') : s(JSON.stringify(val));
    }
    if (typeof val === 'object') return s(JSON.stringify(val, null, 2));
    return s(val);
  }

  // مراحل البروسس بالترتيب
  const FLOW = [
    { key: 'new',                        label: 'مسودة',                 color: '#94a3b8', icon: 'bi-pencil-square' },
    { key: 'pending_governorate_review', label: 'تدقيق بيانات المحافظة', color: '#f59e0b', icon: 'bi-hourglass-split' },
    { key: 'returned_for_edit',          label: 'معاد للتعديل',          color: '#fb923c', icon: 'bi-arrow-counterclockwise' },
    { key: 'pending_branch_approval',    label: 'موافقة مدير الفرع',     color: '#3b82f6', icon: 'bi-person-check-fill' },
    { key: 'approved',                   label: 'موافق عليه',            color: '#22c55e', icon: 'bi-check-circle-fill' },
    { key: 'classified',                 label: 'مصنّف',                 color: '#14b8a6', icon: 'bi-tags-fill' },
    { key: 'in_progress',                label: 'قيد المعالجة',          color: '#6366f1', icon: 'bi-gear-fill' },
    { key: 'resolved',                   label: 'تم الحل',               color: '#10b981', icon: 'bi-flag-fill' },
  ];
  const STATUS_COLORS = { rejected: '#ef4444', archived: '#64748b' };
  FLOW.forEach((f) => { STATUS_COLORS[f.key] = f.color; });

  // المسار الرئيسي (المسار السعيد) — بدون الحالات الاستثنائية لتفادي الازدحام
  const MAIN_FLOW = ['new', 'pending_governorate_review', 'pending_branch_approval', 'approved', 'classified', 'in_progress', 'resolved'];
  const META = {};
  FLOW.forEach((f) => { META[f.key] = f; });

  function renderPipeline(status) {
    // الحالات الاستثنائية تُعرض كشارة منفصلة فوق المسار
    const EXC = {
      returned_for_edit: { label: 'معاد للتعديل', color: '#fb923c', icon: 'bi-arrow-counterclockwise', at: 'pending_governorate_review' },
      rejected: { label: 'مرفوض', color: '#ef4444', icon: 'bi-x-circle-fill', at: null },
      archived: { label: 'مؤرشف', color: '#64748b', icon: 'bi-archive-fill', at: 'resolved' },
    };
    const exc = EXC[status] || null;
    const currentIdx = exc ? (exc.at ? MAIN_FLOW.indexOf(exc.at) : -1) : MAIN_FLOW.indexOf(status);

    const steps = MAIN_FLOW.map((key, i) => {
      const meta = META[key] || { label: key, icon: 'bi-circle' };
      const cls = currentIdx < 0 ? '' : (i < currentIdx ? 'done' : (i === currentIdx ? 'current' : ''));
      const icon = cls === 'done' ? 'bi-check-lg' : meta.icon;
      const now = (!exc && i === currentIdx) ? '<span class="nv-now-tag">الحالة الآن</span>' : '';
      return `<div class="nv-step ${cls}"><div class="nv-dot"><i class="bi ${icon}"></i></div><div class="nv-step-lbl">${meta.label}${now}</div></div>`;
    }).join('');

    const banner = exc
      ? `<div class="nv-exception" style="background:${exc.color}1f;color:${exc.color}"><i class="bi ${exc.icon}"></i> الحالة الحالية: ${exc.label}</div>`
      : '';
    return `${banner}<div class="nv-flow">${steps}</div>`;
  }

  // تسميات عربية لبقية الحقول (تُستخدم في صف الحقول الإضافية)
  const LABELS = {
    id: 'المعرّف', governorate_id: 'معرّف المحافظة', branch_id: 'معرّف الفرع',
    created_by: 'معرّف المُنشئ', updated_by: 'معرّف آخر مُحدِّث', reviewed_by: 'معرّف المدقّق',
    approved_by: 'معرّف الموافِق', rejected_by: 'معرّف الرافض', returned_by: 'معرّف مُعيد التعديل',
    classified_by: 'معرّف المُصنِّف', resolved_by: 'معرّف مُنجز الحل',
    funding_application_id: 'معرّف طلب التمويل', training_course_id: 'معرّف الدورة',
    approval_status: 'حالة الاعتماد', deleted_at: 'تاريخ الحذف',
  };

  const ACTION_LABELS = {
    created: 'إنشاء', create: 'إنشاء', submitted: 'إرسال', submit: 'إرسال',
    review: 'تدقيق', reviewed: 'تدقيق', approve: 'موافقة', approved: 'موافقة',
    reject: 'رفض', rejected: 'رفض', return: 'إعادة للتعديل', returned: 'إعادة للتعديل',
    classify: 'تصنيف', classified: 'تصنيف', resolve: 'حل', resolved: 'حل',
    update: 'تعديل', updated: 'تعديل',
  };

  /* ══ تحميل وعرض الاحتياج ══ */
  async function reload() {
    const res  = await window.APP_API.get(window.APP_ROUTES.needShow(id));
    const need = res.data || {};

    const linkedCards = buildLinkedCards(need);
    const shown = new Set();

    // حقل بعمود نصفي / كامل
    const F = (label, key, formatted) => {
      shown.add(key);
      const v = formatted !== undefined ? formatted : fmtVal(key, need[key]);
      return `<div class="col-md-6 nv-field"><span class="nv-k">${label}</span><div class="nv-v">${v}</div></div>`;
    };
    const F12 = (label, key, formatted) => {
      shown.add(key);
      const v = formatted !== undefined ? formatted : fmtVal(key, need[key]);
      return `<div class="col-12 nv-field"><span class="nv-k">${label}</span><div class="nv-v">${v}</div></div>`;
    };
    const T = (icon, text) => `<div class="col-12"><div class="nv-section-title"><i class="bi ${icon}"></i>${text}</div><hr class="my-1"></div>`;

    const NP = window.NeedsPlatform;
    const statusColor = STATUS_COLORS[need.status] || '#64748b';
    const statusBadge = `<span class="nv-badge" style="background:${statusColor}22;color:${statusColor}">${NP.statusLabel(need.status)}</span>`;
    const sectorsHtml = (need.sectors || []).map((x) => `<span class="nv-sector-chip">${s(x.name_ar || x.code)}</span>`).join('') || '—';

    // metadata
    let metaHtml = '';
    const md = need.metadata;
    if (md && (Array.isArray(md) ? md.length : Object.keys(md).length)) {
      shown.add('metadata');
      metaHtml = `<div class="row g-3 mt-1">${T('bi-braces', 'بيانات وصفية (metadata)')}<div class="col-12"><pre class="nv-v" style="white-space:pre-wrap;font-size:.8rem;background:#f8fafc;border:1px solid #eef2f5;border-radius:8px;padding:10px;margin:0">${s(JSON.stringify(md, null, 2))}</pre></div></div>`;
    }

    body.innerHTML = `
      <div class="mb-3 d-flex align-items-center flex-wrap gap-2">
        <span class="fw-bold fs-5">${s(need.title)}</span>
        ${statusBadge}
        <span class="text-muted small">${s(need.need_code || '')}</span>
      </div>
      ${renderPipeline(need.status)}
      <div class="row g-3 mt-1">
        ${T('bi-card-text', 'التعريف الأساسي')}
        ${F('الكود', 'need_code')}
        ${F('الحالة', 'status', statusBadge)}
        ${F12('العنوان', 'title')}
        ${F12('ملخّص', 'summary')}
        ${F12('الوصف', 'description')}
        ${F12('سبب الاحتياج', 'need_reason')}

        ${T('bi-tags', 'التصنيف')}
        ${F('نوع المالك', 'need_owner_type', NP.ownerLabel(need.need_owner_type))}
        ${F('نطاق الاحتياج', 'need_scope', NP.scopeLabel(need.need_scope))}
        ${F('نوع الاحتياج', 'need_type')}
        ${F('فئة الاحتياج', 'need_category')}
        ${F('درجة التعقيد', 'need_complexity', need.need_complexity ? NP.complexityLabel(need.need_complexity) : '—')}
        ${F('نوع المنشأة', 'facility_type')}
        ${F('النوع الفرعي للمنشأة', 'facility_subtype')}
        ${F('نوع الاستهداف', 'targeting_type')}
        ${F('القطاع', 'sector')}
        ${F('القطاع الاقتصادي', 'economic_sector')}
        ${F('الأولوية', 'priority')}
        ${F('مستوى الأثر', 'impact_level')}
        ${F('مستوى الإلحاح', 'urgency_level')}
        ${F('المدة المتوقعة', 'expected_duration')}
        ${F('مستوى احتياج الدولة', 'state_need_level')}
        ${F('ملف احتياج المواطن', 'citizen_need_profile')}
        ${F('الجهة المسؤولة', 'responsible_entity')}
        ${F('التدخل المقترح', 'proposed_intervention', need.proposed_intervention ? NP.interventionLabel(need.proposed_intervention) : '—')}
        ${F('درجة المنفعة العامة', 'public_benefit_score')}
        ${F12('القطاعات المرجعية', 'sectors', sectorsHtml)}

        ${T('bi-geo-alt', 'الموقع')}
        ${F('المحافظة', 'governorate', s(need.governorate?.name_ar || '—'))}
        ${F('الفرع', 'branch', s(need.branch?.name || '—'))}
        ${F('المنطقة', 'district_name')}
        ${F('الوحدة الإدارية', 'administrative_unit_name')}
        ${F('الناحية', 'countryside_name')}
        ${F('البلدة', 'locality_name')}
        ${F('القرية/الحي', 'village_or_neighborhood')}
        ${F12('تفاصيل العنوان', 'address_details')}
        ${F('خط العرض', 'latitude')}
        ${F('خط الطول', 'longitude')}
        ${F('مصدر الموقع', 'location_source')}
        ${F('معروض على الخريطة', 'is_mapped')}

        ${T('bi-diagram-2', 'التصنيف الإحصائي SyrSIC')}
        ${F('القسم', 'syrsic_section')}
        ${F('التقسيم', 'syrsic_division')}
        ${F('المجموعة', 'syrsic_group')}
        ${F('الصنف', 'syrsic_class')}
        ${F('النشاط', 'syrsic_activity')}

        ${T('bi-person-vcard', 'مقدّم الاحتياج')}
        ${F('الاسم', 'applicant_name')}
        ${F('الهاتف', 'applicant_phone')}
        ${F('البريد', 'applicant_email')}
        ${F('نوع المتقدم', 'applicant_type')}
        ${F('المنظمة/الجهة', 'organization_name')}

        ${T('bi-graph-up-arrow', 'الأثر المتوقع')}
        ${F('عدد المستفيدين', 'beneficiaries_count')}
        ${F('فرص عمل متوقعة', 'expected_jobs_count')}
        ${F('مشاريع متوقعة', 'expected_projects_count')}

        ${T('bi-clipboard-check', 'شركاء ومتطلبات')}
        ${F12('الشركاء المتاحون', 'available_partners')}
        ${F12('العقبات', 'obstacles')}
        ${F12('المتطلبات', 'requirements')}
        ${F12('ملاحظات', 'notes')}

        ${T('bi-broadcast', 'المصدر')}
        ${F('منصة المصدر', 'source_platform')}
        ${F('وحدة المصدر', 'source_module')}
        ${F('معرّف السجل المصدر', 'source_record_id')}
        ${F('عام/خاص', 'is_public', need.is_public === undefined || need.is_public === null ? '—' : (need.is_public ? 'عام' : 'خاص'))}

        ${T('bi-clock-history', 'الأشخاص والتواريخ')}
        ${F('أنشئ بواسطة', 'creator', s(need.creator?.name || '—'))}
        ${F('تاريخ الإنشاء', 'created_at')}
        ${F('آخر تحديث', 'updated_at')}
        ${F('دقّق بواسطة', 'reviewer', s(need.reviewer?.name || '—'))}
        ${F('تاريخ التدقيق', 'reviewed_at')}
        ${F12('ملاحظة التدقيق', 'reviewer_note')}
        ${F('وافق بواسطة', 'approver', s(need.approver?.name || '—'))}
        ${F('تاريخ الموافقة', 'approved_at')}
        ${F12('ملاحظة الموافقة', 'approval_note')}
        ${F('تاريخ الرفض', 'rejected_at')}
        ${F12('سبب الرفض', 'rejection_reason')}
        ${F('تاريخ الإعادة', 'returned_at')}
        ${F12('سبب الإعادة', 'return_reason')}
        ${F('صنّف بواسطة', 'classifier', s(need.classifier?.name || '—'))}
        ${F('تاريخ التصنيف', 'classified_at')}
        ${F12('ملاحظة التصنيف', 'classification_note')}
        ${F('تاريخ الحل', 'resolved_at')}
        ${F('حالة الاعتماد', 'approval_status', NP.statusLabel(need.approval_status))}
      </div>

      ${linkedCards ? `<div class="row g-2 mt-2">${linkedCards}</div>` : ''}
      ${metaHtml}
      ${(() => {
        // صف احتياطي: أي حقل لم يُعرض بعد (ضمانًا لعدم استثناء أي معلومة)
        const SKIP = new Set(['governorate', 'branch', 'creator', 'reviewer', 'approver', 'classifier', 'sectors', 'actionLogs', 'action_logs', 'fundingApplication', 'funding_application', 'trainingCourse', 'training_course', 'metadata']);
        const extra = Object.keys(need)
          .filter((k) => !shown.has(k) && !SKIP.has(k) && (need[k] === null || typeof need[k] !== 'object'))
          .map((k) => F(LABELS[k] || k, k))
          .join('');
        return extra ? `<div class="row g-3 mt-1">${T('bi-three-dots', 'حقول إضافية')}${extra}</div>` : '';
      })()}
      ${(() => {
        const logs = (need.actionLogs || need.action_logs || []).slice()
          .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        if (!logs.length) return '';
        const items = logs.map((l) => {
          const act = ACTION_LABELS[l.action] || s(l.action || '—');
          const from = l.from_status ? NP.statusLabel(l.from_status) : null;
          const to = l.to_status ? NP.statusLabel(l.to_status) : null;
          const flow = (from || to) ? `<div class="nv-tl-flow">${from ? s(from) : '—'} <i class="bi bi-arrow-left-short"></i> ${to ? s(to) : '—'}</div>` : '';
          const who = s(l.performer?.name || '');
          const note = l.note ? `<div class="nv-tl-note">${s(l.note)}</div>` : '';
          return `<div class="nv-tl-item"><div class="nv-tl-action">${act}</div>${flow}<div class="nv-tl-meta">${who ? who + ' • ' : ''}${fmtDate(l.created_at)}</div>${note}</div>`;
        }).join('');
        return `<div class="mt-3"><div class="nv-section-title mb-2"><i class="bi bi-list-check"></i>سجل سير العمل (البروسس)</div><div class="nv-timeline">${items}</div></div>`;
      })()}
    `;

    actions.innerHTML = '';

    if (window.NeedsPlatform.isReadOnly()) return;

    const btn = (text, cls, action) =>
      `<button type="button" class="btn btn-sm ${cls}" data-action="${action}">${text}</button>`;

    actions.innerHTML += btn(SiteI18n.ta('تعديل'), 'btn-outline-primary', 'edit');

    if (window.NeedsPlatform.canReview()) {
      actions.innerHTML += btn(SiteI18n.ta('تدقيق'), 'btn-brand', 'review');
      actions.innerHTML += btn(SiteI18n.ta('إعادة للتعديل'), 'btn-warning', 'return');
    }
    if (window.NeedsPlatform.canApprove()) {
      actions.innerHTML += btn(SiteI18n.ta('موافقة'), 'btn-success', 'approve');
      actions.innerHTML += btn(SiteI18n.ta('رفض'), 'btn-danger', 'reject');
    }
    if (window.NeedsPlatform.canClassify()) {
      actions.innerHTML += btn(SiteI18n.ta('تصنيف'), 'btn-info text-white', 'classify');
    }
  }

  /* ══ معالج الأزرار ══ */
  actions?.addEventListener('click', async (e) => {
    const btn    = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;

    if (action === 'edit') {
      window.location.href = `need-edit.php?id=${id}`;
      return;
    }

    if (action === 'review') {
      try {
        await window.APP_API.post(window.APP_ROUTES.needReview(id), {});
        showMessage(SiteI18n.ta('تم التدقيق بنجاح'));
        await reload();
      } catch (err) {
        showMessage(err?.message || SiteI18n.ta('تعذّر التدقيق'), 'danger');
      }
      return;
    }

    if (action === 'approve') {
      try {
        await window.APP_API.post(window.APP_ROUTES.needApprove(id), {});
        showMessage(SiteI18n.ta('تمت الموافقة بنجاح'));
        await reload();
      } catch (err) {
        showMessage(err?.message || SiteI18n.ta('تعذّرت الموافقة'), 'danger');
      }
      return;
    }

    if (action === 'reject') {
      openActionModal({
        title: SiteI18n.ta('رفض الاحتياج'),
        label: SiteI18n.ta('سبب الرفض'),
        btnText: SiteI18n.ta('تأكيد الرفض'),
        btnClass: 'btn-danger',
        requireReason: true,
        onConfirm: async ({ reason }) => {
          await window.APP_API.post(window.APP_ROUTES.needReject(id), { rejection_reason: reason });
          showMessage(SiteI18n.ta('تم رفض الاحتياج'));
          await reload();
        },
      });
      return;
    }

    if (action === 'return') {
      openActionModal({
        title: SiteI18n.ta('إعادة الاحتياج للتعديل'),
        label: SiteI18n.ta('سبب الإعادة'),
        btnText: SiteI18n.ta('إعادة للتعديل'),
        btnClass: 'btn-warning',
        requireReason: true,
        onConfirm: async ({ reason }) => {
          await window.APP_API.post(window.APP_ROUTES.needReturn(id), { return_reason: reason });
          showMessage(SiteI18n.ta('أُعيد الاحتياج للتعديل'));
          await reload();
        },
      });
      return;
    }

    if (action === 'classify') {
      openActionModal({
        title: SiteI18n.ta('تصنيف الاحتياج'),
        label: '',
        btnText: SiteI18n.ta('حفظ التصنيف'),
        btnClass: 'btn-info text-white',
        requireReason: false,
        showSelect: true,
        onConfirm: async ({ intervention }) => {
          await window.APP_API.post(window.APP_ROUTES.needClassify(id), { proposed_intervention: intervention });
          showMessage(SiteI18n.ta('تم التصنيف بنجاح'));
          await reload();
        },
      });
    }
  });

  await reload();
});
