/**
 * زر عائم لاقتراح تصنيف الاحتياج عبر ميكروسيرفس AI (بروكسي Laravel).
 * يعمل على صفحات إنشاء/عرض الاحتياج.
 */
(function initNeedsAiFab() {
  const STYLE_ID = 'needs-ai-fab-style';
  const ROOT_ID = 'needsAiFabRoot';

  function ensureStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
      #${ROOT_ID}{position:fixed;inset-inline-end:20px;bottom:24px;z-index:1080;font-family:inherit}
      #${ROOT_ID} .nai-fab{
        width:56px;height:56px;border:0;border-radius:50%;
        background:#0f766e;color:#fff;box-shadow:0 8px 24px rgba(15,118,110,.35);
        display:flex;align-items:center;justify-content:center;font-size:1.35rem;
        cursor:pointer;transition:transform .15s ease,box-shadow .15s ease
      }
      #${ROOT_ID} .nai-fab:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,118,110,.4)}
      #${ROOT_ID} .nai-fab:disabled{opacity:.65;cursor:wait;transform:none}
      #${ROOT_ID} .nai-panel{
        position:absolute;inset-inline-end:0;bottom:68px;width:min(340px,calc(100vw - 32px));
        background:#fff;border:1px solid #e2e8f0;border-radius:16px;
        box-shadow:0 16px 40px rgba(15,23,42,.18);padding:14px 14px 12px;display:none
      }
      #${ROOT_ID}.open .nai-panel{display:block}
      #${ROOT_ID} .nai-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
      #${ROOT_ID} .nai-title{font-weight:800;font-size:.95rem;color:#0f172a;margin:0}
      #${ROOT_ID} .nai-close{border:0;background:transparent;color:#94a3b8;font-size:1.2rem;line-height:1;cursor:pointer}
      #${ROOT_ID} .nai-body{font-size:.85rem;color:#334155;max-height:280px;overflow:auto}
      #${ROOT_ID} .nai-row{display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid #f1f5f9}
      #${ROOT_ID} .nai-row:last-child{border-bottom:0}
      #${ROOT_ID} .nai-k{color:#94a3b8;font-weight:700;font-size:.75rem}
      #${ROOT_ID} .nai-v{font-weight:700;text-align:start}
      #${ROOT_ID} .nai-msg{padding:8px 0;color:#64748b}
      #${ROOT_ID} .nai-err{color:#b91c1c}
      #${ROOT_ID} .nai-actions{display:flex;gap:8px;margin-top:10px}
      #${ROOT_ID} .nai-actions .btn{flex:1;font-size:.82rem;font-weight:700}
      @media (max-width:575px){
        #${ROOT_ID}{inset-inline-end:14px;bottom:18px}
        #${ROOT_ID} .nai-fab{width:52px;height:52px}
      }
    `;
    document.head.appendChild(style);
  }

  function needIdFromPage() {
    const params = new URLSearchParams(window.location.search);
    const id = parseInt(params.get('id') || params.get('need_id') || '', 10);
    return Number.isFinite(id) && id > 0 ? id : null;
  }

  function collectCreatePayload() {
    const form = document.querySelector('#needCreateForm, form[name="needForm"], form');
    const title = form?.querySelector('[name="title"]')?.value?.trim()
      || document.querySelector('[name="title"]')?.value?.trim()
      || '';
    const description = form?.querySelector('[name="description"]')?.value?.trim()
      || document.querySelector('[name="description"]')?.value?.trim()
      || '';
    const sector = form?.querySelector('[name="sector"]')?.value?.trim()
      || document.querySelector('#hSector')?.value?.trim()
      || '';
    const district = form?.querySelector('[name="district_name"]')?.value?.trim()
      || document.querySelector('#districtNameInput, [name="district_name"]')?.value?.trim()
      || '';
    return { title, description, sector, district_name: district };
  }

  function setSelectValue(select, value) {
    if (!select || value == null || value === '') return false;
    const ok = [...select.options].some((o) => o.value === value);
    if (!ok) return false;
    select.value = value;
    select.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
  }

  function applyToCreateForm(suggestion) {
    if (!suggestion) return false;
    let applied = false;
    applied = setSelectValue(document.getElementById('needCategorySelect'), suggestion.need_category) || applied;
    applied = setSelectValue(document.getElementById('facilityTypeSelect'), suggestion.facility_type) || applied;
    applied = setSelectValue(document.getElementById('targetingTypeSelect'), suggestion.targeting_type) || applied;

    const sectors = Array.isArray(suggestion.sector_codes) ? suggestion.sector_codes : [];
    const box = document.getElementById('sectorChips');
    if (box && sectors.length) {
      box.querySelectorAll('.sector-chip').forEach((chip) => {
        const on = sectors.includes(chip.dataset.value);
        chip.classList.toggle('selected', on);
      });
      applied = true;
    }

    const interventionSelect = document.querySelector('[name="proposed_intervention"]');
    if (interventionSelect && suggestion.proposed_intervention) {
      const val = suggestion.proposed_intervention;
      const matched = [...interventionSelect.options].find(
        (o) => o.value === val || o.textContent.trim() === val
      );
      if (matched) {
        interventionSelect.value = matched.value;
        applied = true;
      }
    }

    return applied;
  }

  function row(label, value) {
    if (value == null || value === '' || (Array.isArray(value) && !value.length)) return '';
    const text = Array.isArray(value) ? value.join('، ') : String(value);
    return `<div class="nai-row"><span class="nai-k">${label}</span><span class="nai-v">${escapeHtml(text)}</span></div>`;
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderSuggestion(bodyEl, data) {
    const s = data?.suggestion || {};
    const conf = data?.confidence != null ? `${Math.round(Number(data.confidence) * 100)}%` : null;
    bodyEl.innerHTML = [
      row('الثقة', conf),
      row('فئة الاحتياج', s.need_category_label || s.need_category),
      row('نوع المنشأة', s.facility_type_label || s.facility_type),
      row('الاستهداف', s.targeting_type_label || s.targeting_type),
      row('القطاعات', s.sector_labels || s.sector_codes),
      row('التدخل المقترح', s.proposed_intervention),
      row('التبرير', s.rationale),
    ].join('') || '<div class="nai-msg">لا توجد حقول مقترحة في الاستجابة.</div>';
  }

  async function runSuggest(root) {
    const fab = root.querySelector('.nai-fab');
    const body = root.querySelector('.nai-body');
    const applyBtn = root.querySelector('[data-nai-apply]');
    fab.disabled = true;
    applyBtn.classList.add('d-none');
    applyBtn.dataset.suggestion = '';
    body.innerHTML = '<div class="nai-msg">جاري طلب الاقتراح من الميكروسيرفس...</div>';
    root.classList.add('open');

    try {
      const needId = needIdFromPage();
      let res;
      if (needId && /need-view/i.test(window.location.pathname)) {
        res = await window.APP_API.post(window.APP_ROUTES.needAiSuggest(needId), {});
      } else {
        const payload = collectCreatePayload();
        if (!payload.title && !payload.description) {
          body.innerHTML = '<div class="nai-msg nai-err">أدخل اسم الاحتياج أو الوصف أولاً.</div>';
          return;
        }
        res = await window.APP_API.post(window.APP_ROUTES.needsAiSuggest(), payload);
      }

      const data = res?.data || res;
      renderSuggestion(body, data);
      applyBtn.dataset.suggestion = JSON.stringify(data?.suggestion || {});
      if (/need-create/i.test(window.location.pathname)) {
        applyBtn.classList.remove('d-none');
      }
      root._lastSuggestion = data?.suggestion || null;
    } catch (err) {
      const msg = err?.data?.message || err?.message || 'تعذر الاتصال بميكروسيرفس التصنيف.';
      body.innerHTML = `<div class="nai-msg nai-err">${escapeHtml(msg)}</div>`;
    } finally {
      fab.disabled = false;
    }
  }

  function mount() {
    if (!window.APP_API || !window.APP_ROUTES?.needsAiSuggest) return;
    const path = window.location.pathname || '';
    if (!/need-create|need-view/i.test(path)) return;
    if (document.getElementById(ROOT_ID)) return;

    ensureStyles();
    const root = document.createElement('div');
    root.id = ROOT_ID;
    root.innerHTML = `
      <div class="nai-panel" role="dialog" aria-label="اقتراح تصنيف">
        <div class="nai-head">
          <h3 class="nai-title">اقتراح تصنيف</h3>
          <button type="button" class="nai-close" aria-label="إغلاق">&times;</button>
        </div>
        <div class="nai-body"><div class="nai-msg">اضغط الزر لطلب اقتراح من ميكروسيرفس التصنيف.</div></div>
        <div class="nai-actions">
          <button type="button" class="btn btn-outline-secondary d-none" data-nai-apply>تطبيق على النموذج</button>
        </div>
      </div>
      <button type="button" class="nai-fab" title="اقتراح تصنيف AI" aria-label="اقتراح تصنيف AI">
        <i class="bi bi-stars"></i>
      </button>
    `;
    document.body.appendChild(root);

    root.querySelector('.nai-fab').addEventListener('click', () => {
      if (root.classList.contains('open') && root.querySelector('.nai-body .nai-row')) {
        root.classList.remove('open');
        return;
      }
      runSuggest(root);
    });
    root.querySelector('.nai-close').addEventListener('click', () => root.classList.remove('open'));
    root.querySelector('[data-nai-apply]').addEventListener('click', () => {
      const suggestion = root._lastSuggestion
        || JSON.parse(root.querySelector('[data-nai-apply]').dataset.suggestion || '{}');
      const ok = applyToCreateForm(suggestion);
      const body = root.querySelector('.nai-body');
      if (ok) {
        body.insertAdjacentHTML('afterbegin', '<div class="nai-msg" style="color:#15803d">تم تطبيق الاقتراح على الحقول.</div>');
      } else {
        body.insertAdjacentHTML('afterbegin', '<div class="nai-msg nai-err">تعذر تطبيق بعض الحقول — راجع القيم يدوياً.</div>');
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
  } else {
    mount();
  }
})();
