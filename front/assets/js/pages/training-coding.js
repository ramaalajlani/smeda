document.addEventListener('DOMContentLoaded', async () => {
  const loadingBox = document.getElementById('codingLoadingBox');
  const tbody = document.getElementById('trainingCodingTableBody');

  if (!tbody) {
    return;
  }

  function safe(value, fallback = '—') {
    return window.APP_HELPERS?.e(window.APP_HELPERS?.safe(value, fallback) ?? fallback);
  }

  function renderRows(rows) {
    tbody.innerHTML = '';

    if (!rows.length) {
      window.APP_UI?.renderEmptyTable(tbody, 6, SiteI18n.ta('لا توجد بيانات تكويد متاحة حالياً.'));
      return;
    }

    rows.forEach((item) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${safe(item.sector)}</td>
        <td>${safe(item.category)}</td>
        <td>${safe(item.type)}</td>
        <td>${safe(item.subject)}</td>
        <td>${safe(item.level)}</td>
        <td><code>${safe(item.code)}</code></td>
      `;
      tbody.appendChild(row);
    });
  }

  try {
    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/map/training-centers?limit=50`, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      throw new Error('map request failed');
    }

    const payload = await response.json();
    const centers = Array.isArray(payload?.data) ? payload.data : [];

    const rows = centers.flatMap((center) => {
      const kits = Array.isArray(center.training_kits) ? center.training_kits : [];

      if (!kits.length) {
        return [{
          sector: center.city || SiteI18n.ta('عام'),
          category: center.name,
          type: SiteI18n.ta('مركز تدريبي'),
          subject: '—',
          level: '—',
          code: center.code || `CTR-${center.id}`,
        }];
      }

      return kits.map((kit) => ({
        sector: center.city || SiteI18n.ta('عام'),
        category: center.name,
        type: kit.program?.name || SiteI18n.ta('حقيبة تدريبية'),
        subject: kit.name,
        level: kit.level || '—',
        code: kit.code || '—',
      }));
    });

    window.APP_UI?.hideLoadingState(loadingBox);
    renderRows(rows);
  } catch (error) {
    console.error('Training coding load error:', error);
    window.APP_UI?.hideLoadingState(loadingBox);
    renderRows([]);
  }
});
