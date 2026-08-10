document.addEventListener('DOMContentLoaded', async () => {
  const ok = await window.AppBootstrapAuth.init({ requireAuth: false });
  if (!ok) return;

  if (!window.FinancePlatform.canViewApplications() && !window.FinancePlatform.canCreateApplication()) {
    return;
  }

  try {
    const metrics = await window.APP_API.get(window.APP_ROUTES.fundingMetrics());
    const d = metrics.data || metrics;
    document.querySelectorAll('[data-finance-metric="total_applications"]').forEach((el) => { el.textContent = d.total_applications ?? '0'; });
    document.querySelectorAll('[data-finance-metric="funded_applications"]').forEach((el) => { el.textContent = d.funded_applications ?? '0'; });
    document.querySelectorAll('[data-finance-metric="pending_applications"]').forEach((el) => { el.textContent = d.pending_applications ?? '0'; });
    document.querySelectorAll('[data-finance-metric="defaulted_loans"]').forEach((el) => { el.textContent = d.defaulted_loans ?? '0'; });
    document.querySelectorAll('[data-finance-metric="active_loans"]').forEach((el) => { el.textContent = d.active_loans ?? '0'; });
    document.querySelectorAll('[data-finance-metric="repayment_rate"]').forEach((el) => { el.textContent = `${d.repayment_rate ?? 0}%`; });
  } catch (_) {
    /* public landing may load without auth */
  }

  window.FinancePlatform.applyRoleNav('financeRoleNav');
});
