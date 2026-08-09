/**
 * بوابة المستشار الذكي — تبويبات الرئيسية/قاعدة المعرفة + فتح المحادثة.
 * كل طلبات المعرفة تمر عبر Laravel (لا اتصال مباشر بالخدمة الخارجية).
 */
(function initAiAdvisorPortal() {
  if (!document.body.classList.contains('aic-portal')) return;
  if (!window.APP_API || !window.APP_ROUTES) return;

  const pillsEl = document.getElementById('kbPills');
  const filesEl = document.getElementById('kbFilesList');
  const form = document.getElementById('kbIngestForm');
  const resultEl = document.getElementById('kbIngestResult');
  const deptInput = document.getElementById('kbDept');
  const ingestCard = document.getElementById('kbIngestCard');
  const browseCard = document.getElementById('kbBrowseCard');

  let currentDept = null;
  let requestId = 0;
  let canManageKnowledge = false;

  async function refreshKnowledgePermission() {
    try {
      const cfg = await window.APP_API.get(window.APP_ROUTES.aiConfig());
      canManageKnowledge = cfg?.can_manage_knowledge === true;
    } catch (_) {
      canManageKnowledge = false;
    }
    if (ingestCard) ingestCard.hidden = !canManageKnowledge;
    if (browseCard && canManageKnowledge) browseCard.style.marginTop = '22px';
    else if (browseCard) browseCard.style.marginTop = '0';
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function typeLabel(type) {
    const map = {
      excel: 'Excel',
      pdf: 'PDF',
      word: 'Word',
      text: 'نص',
      image: 'صورة',
      video: 'فيديو',
    };
    return map[String(type || '').toLowerCase()] || (type || 'ملف');
  }

  function openChat() {
    const root = document.getElementById('aiChatFabRoot');
    if (!root) return;
    if (!root.classList.contains('aic-open')) {
      root.querySelector('.aic-fab')?.click();
    }
  }

  document.querySelectorAll('[data-portal-tab]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.portalTab;
      document.querySelectorAll('[data-portal-tab]').forEach((b) => {
        b.classList.toggle('active', b === btn);
      });
      document.querySelectorAll('[data-portal-view]').forEach((view) => {
        view.classList.toggle('active', view.dataset.portalView === name);
      });
      if (name === 'kb') {
        refreshKnowledgePermission().then(() => loadDepartments());
      }
    });
  });

  document.querySelector('[data-aic-open-chat]')?.addEventListener('click', openChat);

  refreshKnowledgePermission();

  async function loadDepartments() {
    if (!pillsEl) return;
    pillsEl.innerHTML = '<div class="spinner">جارٍ التحميل...</div>';
    try {
      const res = await window.APP_API.get(window.APP_ROUTES.aiKnowledgeDepartments());
      const items = Array.isArray(res?.items) ? res.items : [];
      if (!items.length) {
        pillsEl.innerHTML = '<div class="placeholder">لا توجد أقسام مدرَّبة بعد.</div>';
        if (filesEl) filesEl.innerHTML = '';
        return;
      }

      pillsEl.innerHTML = '';
      items.forEach((item, index) => {
        const pill = document.createElement('button');
        pill.type = 'button';
        pill.className = 'dept-pill' + (index === 0 ? ' active' : '');
        pill.innerHTML = `${escapeHtml(item.department_id)}<span class="n">${Number(item.count) || 0}</span>`;
        pill.addEventListener('click', () => {
          pillsEl.querySelectorAll('.dept-pill').forEach((p) => p.classList.remove('active'));
          pill.classList.add('active');
          if (deptInput) deptInput.value = item.department_id;
          loadGrouped(item.department_id);
        });
        pillsEl.appendChild(pill);
      });

      if (deptInput && !deptInput.value) deptInput.value = items[0].department_id;
      loadGrouped(items[0].department_id);
    } catch (err) {
      const msg = err?.data?.message || err?.message || 'تعذر تحميل الأقسام.';
      pillsEl.innerHTML = `<span style="color:var(--bad)">${escapeHtml(msg)}</span>`;
    }
  }

  async function loadGrouped(departmentId) {
    currentDept = departmentId;
    const rid = ++requestId;
    if (!filesEl) return;
    filesEl.innerHTML = '<div class="spinner">جارٍ التحميل...</div>';

    try {
      const typeGroups = new Map();
      let offset = null;
      let pages = 0;

      do {
        const params = new URLSearchParams({ limit: '200' });
        if (offset) params.set('offset', offset);
        const url = `${window.APP_ROUTES.aiKnowledgeItems(departmentId)}?${params}`;
        const res = await window.APP_API.get(url);
        if (rid !== requestId) return;

        (res.items || []).forEach((chunk) => {
          const type = chunk.source_type || 'other';
          const name = chunk.source_name || 'بدون اسم';
          if (!typeGroups.has(type)) typeGroups.set(type, new Map());
          const files = typeGroups.get(type);
          if (!files.has(name)) files.set(name, []);
          files.get(name).push(chunk);
        });

        offset = res.next_offset || null;
        pages += 1;
      } while (offset && pages < 40);

      if (rid !== requestId) return;
      renderGroups(typeGroups);
    } catch (err) {
      if (rid !== requestId) return;
      const msg = err?.data?.message || err?.message || 'تعذر تحميل قاعدة المعرفة.';
      filesEl.innerHTML = `<div class="placeholder" style="color:var(--bad)">${escapeHtml(msg)}</div>`;
    }
  }

  function renderGroups(typeGroups) {
    if (!filesEl) return;
    if (!typeGroups.size) {
      filesEl.innerHTML = '<div class="placeholder">لا توجد ملفات في هذا القسم.</div>';
      return;
    }

    filesEl.innerHTML = '';
    typeGroups.forEach((files, type) => {
      let chunkCount = 0;
      files.forEach((chunks) => { chunkCount += chunks.length; });

      const box = document.createElement('div');
      box.className = 'nd-kb-type';
      box.innerHTML = `
        <div class="nd-kb-type-row">
          <div class="nd-kb-type-name"><i class="bi bi-folder2-open"></i>${escapeHtml(typeLabel(type))}</div>
          <div class="nd-kb-type-meta"><span class="badge">${chunkCount} جزء — ${files.size} ملف</span></div>
        </div>
        <div class="nd-kb-type-files"></div>
      `;

      const row = box.querySelector('.nd-kb-type-row');
      const list = box.querySelector('.nd-kb-type-files');
      row.addEventListener('click', () => box.classList.toggle('open'));

      files.forEach((chunks, name) => {
        const file = document.createElement('div');
        file.className = 'nd-kb-file';
        const preview = (chunks[0]?.text || '').slice(0, 500);
        file.innerHTML = `
          <div class="nd-kb-file-row">
            <div class="nd-kb-file-name"><i class="bi bi-file-earmark"></i>${escapeHtml(name)}</div>
            <div class="nd-kb-file-meta"><span class="badge">${chunks.length} جزء</span></div>
          </div>
          <div class="nd-kb-file-details">${escapeHtml(preview)}${(chunks[0]?.text || '').length > 500 ? '…' : ''}</div>
        `;
        file.querySelector('.nd-kb-file-row').addEventListener('click', (e) => {
          e.stopPropagation();
          file.classList.toggle('open');
        });
        list.appendChild(file);
      });

      filesEl.appendChild(box);
    });
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const departmentId = (deptInput?.value || '').trim();
    const text = (document.getElementById('kbText')?.value || '').trim();
    const files = [...(document.getElementById('kbFiles')?.files || [])];
    if (!departmentId) return;
    if (!text && !files.length) {
      if (resultEl) resultEl.innerHTML = '<div style="color:var(--bad)">أرفق ملفاً أو ألصق نصاً.</div>';
      return;
    }

    const submitBtn = document.getElementById('kbSubmit');
    const defaultLabel = submitBtn?.textContent || 'رفع وتدريب';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'جارٍ الرفع...';
    }
    if (resultEl) {
      resultEl.innerHTML = '<div class="spinner">جارٍ رفع الملفات ومعالجتها، قد يستغرق ذلك بعض الوقت...</div>';
    }

    try {
      const fd = new FormData();
      fd.append('department_id', departmentId);
      if (text) fd.append('text', text);
      files.forEach((f) => fd.append('files[]', f));

      const res = await window.APP_API.post(window.APP_ROUTES.aiKnowledgeIngest(), fd);
      const d = res?.data || {};
      let html = '<div class="card" style="margin-top:0"><h3>تم بنجاح</h3>';
      if (d.status === 'queued') {
        html += `<p style="margin:0">تم جدولة المعالجة (job: <span class="code">${escapeHtml(d.job_id || '')}</span>)</p>`;
      } else {
        html += `<p style="margin:0">تمت إضافة <b>${Number(d.chunks_ingested) || 0}</b> جزء معرفي إلى قسم <span class="code">${escapeHtml(d.department_id || departmentId)}</span></p>`;
      }
      html += '</div>';
      if (resultEl) resultEl.innerHTML = html;
      await loadDepartments();
      if (currentDept === departmentId || !currentDept) loadGrouped(departmentId);
    } catch (err) {
      const msg = err?.data?.message || err?.message || 'فشل الرفع';
      if (resultEl) resultEl.innerHTML = `<div style="color:var(--bad)">${escapeHtml(msg)}</div>`;
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = defaultLabel;
      }
    }
  });

  // فتح المحادثة تلقائياً عند القدوم من زر المنصة
  if (/[?&]open=1(?:&|$)/.test(window.location.search)) {
    const tryOpen = () => {
      if (document.getElementById('aiChatFabRoot')) openChat();
      else setTimeout(tryOpen, 120);
    };
    setTimeout(tryOpen, 200);
  }
})();
