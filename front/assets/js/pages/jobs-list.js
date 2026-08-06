document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: true });
  if (!ok) return;

  if (!window.AppAuth.hasPermission('workforce.jobs.view')) {
    window.location.href = window.APP_CONFIG.FORBIDDEN_PAGE;
    return;
  }

  const grid = document.getElementById('jobsGrid');
  const loading = document.getElementById('jobsLoading');
  const messageBox = document.getElementById('jobsMessage');
  const searchInput = document.getElementById('jobsSearchInput');
  const sectorFilter = document.getElementById('jobsSectorFilter');
  const cityFilter = document.getElementById('jobsCityFilter');
  const searchBtn = document.getElementById('jobsSearchBtn');

  const employmentLabels = {
    full_time: SiteI18n.ta('دوام كامل'),
    part_time: SiteI18n.ta('دوام جزئي'),
    contract: SiteI18n.ta('عقد مؤقت'),
    freelance: SiteI18n.ta('عمل حر'),
  };

  function showMessage(text, type = 'info') {
    if (!messageBox) return;
    messageBox.className = `alert alert-${type}`;
    messageBox.textContent = text;
    messageBox.classList.remove('d-none');
  }

  async function loadJobs() {
    loading?.classList.remove('d-none');
    grid.innerHTML = '';

    try {
      const res = await window.APP_API.get(window.APP_ROUTES.jobPostings({
        search: searchInput?.value?.trim() || '',
        sector: sectorFilter?.value || '',
        city: cityFilter?.value?.trim() || '',
        per_page: 50,
      }));

      const rows = res.data || [];
      if (!rows.length) {
        grid.innerHTML = SiteI18n.ta('<div class="col-12"><div class="alert alert-light border">لا توجد فرص عمل منشورة حالياً.</div></div>');
        return;
      }

      grid.innerHTML = rows.map((job) => `
        <div class="col-md-6">
          <div class="services-grid-card h-100">
            <h3>${window.APP_HELPERS.e(job.title)}</h3>
            <p>${window.APP_HELPERS.e(job.description || '—')}</p>
            <div class="meta">
              <span>${window.APP_HELPERS.e(job.sector || '—')}</span>
              <span>${window.APP_HELPERS.e(job.city || '—')}</span>
              <span>${employmentLabels[job.employment_type] || job.employment_type}</span>
            </div>
            <div class="actions">
              <a href="job-request.php?job_id=${job.id}" class="btn btn-brand">اطلب الوظيفة</a>
              <a href="job-post.php?id=${job.id}" class="soft-btn">تفاصيل</a>
            </div>
          </div>
        </div>
      `).join('');
    } catch (error) {
      showMessage(error?.data?.message || SiteI18n.ta('تعذر تحميل فرص العمل.'), 'danger');
    } finally {
      loading?.classList.add('d-none');
    }
  }

  searchBtn?.addEventListener('click', loadJobs);
  await loadJobs();
});
