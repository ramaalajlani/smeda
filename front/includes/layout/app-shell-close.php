<?php
/*
|--------------------------------------------------------------------------
| قوقعة لوحة التحكم الموحّدة — الإغلاق + السكربتات + سلوك القوقعة المشترك
|--------------------------------------------------------------------------
| يُغلق <main> و .app-shell، ثم يحمّل السكربتات الأساسية، ثم سلوك القوقعة
| (بيانات المستخدم، تبديل السايدبار، الخروج، إظهار الروابط حسب الصلاحية).
| تُدرَج هذه الأزرار في كل صفحات المستخدمين المسجّلين لتوحيد الشكل والتنقّل.
*/
$basePath = isset($basePath) ? $basePath : '';
require_once __DIR__ . '/shell-preference.php';

if (empty($forceAppShell) && authority_use_dashboard_shell()) {
    ?>
  </div><!-- .ds-content.app-main -->
</div><!-- .ds-main -->
</div><!-- .ds-layout -->

<?php include __DIR__ . '/scripts.php'; ?>
<script src="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/js/modules/dashboard-sidebar.js?v=2.4"></script>
<?php
    return;
}
?>
    </main>
  </div>

<?php include __DIR__ . '/scripts.php'; ?>

<script>
(function () {
  /* ── تسميات الأدوار/الأنواع ── */
  const ROLE_LABELS = {
    admin:'المدير العام', super_admin:'المدير العام', system_admin:'المدير العام',
    general_director:'المدير العام', deputy_general_director:'نائب المدير العام',
    deputy_director:'نائب المدير', training_manager:'مدير التدريب',
    branch_manager:'مدير فرع', branch_officer:'موظف فرع', governor:'محافظ',
    center_user:'مستخدم مركز', trainer_user:'مدرب', trainee_user:'متدرب', auditor:'مدقق',
    finance_manager:'مدير التمويل', finance_officer:'موظف تمويل',
    consultant_office:'مكتب استشاري', funding_partner:'شريك تمويل',
    workforce_manager:'مدير القوى العاملة', project_owner:'صاحب مشروع'
  };
  const TYPE_LABELS = {
    admin:'حساب المدير العام', super_admin:'حساب المدير العام', system_admin:'حساب المدير العام',
    general_director:'الإدارة العليا', deputy_general_director:'الإدارة العليا',
    deputy_director:'الإدارة العليا', training_manager:'إدارة التدريب',
    center_user:'مركز تدريبي', trainer_user:'مدرب', trainee_user:'متدرب', auditor:'تدقيق'
  };

  function roleLabel(r){ return ROLE_LABELS[r] || 'مستخدم النظام'; }
  function typeLabel(t){ return TYPE_LABELS[t] || 'حساب عام'; }
  function firstLetter(n){ return String(n||'م').trim().charAt(0) || 'م'; }

  /* ── تعبئة بيانات المستخدم ── */
  function fillUser() {
    try {
      if (!window.AppAuth?.getUser) return false;
      const u = window.AppAuth.getUser();
      if (!u) return false;
      const name  = u.name || u.full_name || u.username || 'مستخدم النظام';
      const email = u.email || '—';
      const role  = ((u.roles || [])[0] || u.role || '').toString();
      const type  = (u.entity_type || role || '').toString();
      const ltr   = firstLetter(name);

      ['sbAvatar','topbarAvatar'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent = ltr; });
      const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
      set('sbUserName',  name);   set('sbUserRole',  roleLabel(role));
      set('sbUserEmail', email);  set('sbUserType',  typeLabel(type));
      set('topbarUserName', name); set('topbarUserRole', roleLabel(role));
      return true;
    } catch (e) { return false; }
  }

  /* ── تبديل السايدبار (موبايل) ── */
  const sidebar   = document.getElementById('appSidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggleBtn');

  function openSidebar(){
    if (!sidebar) return;
    sidebar.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar(){
    if (!sidebar) return;
    sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!sidebar) return;
      if (sidebar.classList.contains('open')) closeSidebar();
      else openSidebar();
    });
  }
  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
    overlay.addEventListener('touchend', (e) => {
      e.preventDefault();
      closeSidebar();
    }, { passive: false });
  }

  // إغلاق عند الضغط خارج السايدبار (احتياطي)
  document.addEventListener('click', (e) => {
    if (window.innerWidth >= 992) return;
    if (!sidebar?.classList.contains('open')) return;
    if (sidebar.contains(e.target) || toggleBtn?.contains(e.target)) return;
    closeSidebar();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSidebar();
  });

  // عند الانتقال لصفحة أخرى من السايدبار: أغلقه على الموبايل
  if (sidebar) {
    sidebar.querySelectorAll('a.sb-link').forEach((a) => {
      a.addEventListener('click', () => {
        if (window.innerWidth < 992) closeSidebar();
      });
    });
  }

  /* ── تسجيل الخروج ── */
  function doLogout(){ if (window.AppAuth?.logout) window.AppAuth.logout(); }
  ['sbLogoutBtn','topbarLogoutBtn'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', doLogout);
  });

  /* ── مزامنة سهم الأقسام القابلة للطي ── */
  document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
    const target = document.querySelector(btn.getAttribute('data-bs-target'));
    if (!target) return;
    target.addEventListener('show.bs.collapse', () => btn.setAttribute('aria-expanded','true'));
    target.addEventListener('hide.bs.collapse', () => btn.setAttribute('aria-expanded','false'));
  });

  /* ── إبراز الرابط النشط حسب الصفحة الحالية ── */
  function markActiveLink() {
    if (!sidebar) return;
    const path = window.location.pathname.replace(/\/+$/, '');
    const file = path.substring(path.lastIndexOf('/') + 1);
    let matched = false;
    sidebar.querySelectorAll('a.sb-link').forEach(a => {
      const href = a.getAttribute('href') || '';
      const hrefFile = href.split('/').pop().split('?')[0];
      if (hrefFile && file && hrefFile === file) {
        a.classList.add('active');
        matched = true;
        // افتح القسم المطوي الذي يحوي الرابط النشط
        const collapse = a.closest('.collapse');
        if (collapse) collapse.classList.add('show');
      } else {
        a.classList.remove('active');
      }
    });
    return matched;
  }

  /* ── التطبيق حسب الصلاحية (نظام واحد) + إخفاء الأقسام الفارغة ── */
  function applyShellVisibility() {
    if (window.AppAccess?.toggleByPermission) {
      window.AppAccess.toggleByPermission(document.getElementById('appSidebar') || document);
    } else if (window.APP_UI?.applyPermissionVisibility) {
      window.APP_UI.applyPermissionVisibility(document.getElementById('appSidebar') || document);
    }

    document.querySelectorAll('#appSidebar .sb-section').forEach((section) => {
      const links = Array.from(section.querySelectorAll('.sb-links .sb-link'));
      if (!links.length) return;
      const hasVisibleLink = links.some((link) => !link.classList.contains('d-none'));
      if (!hasVisibleLink) section.classList.add('d-none');
      else section.classList.remove('d-none');
    });
  }

  /* ── التهيئة ── */
  document.addEventListener('DOMContentLoaded', () => {
    fillUser();
    applyShellVisibility();
    markActiveLink();

    // تحديث خفيف إذا البيانات بعد ما وصلت من /me
    let tries = 0;
    const t = setInterval(() => {
      tries++;
      const ok = fillUser();
      applyShellVisibility();
      if (ok || tries >= 8) {
        applyShellVisibility();
        clearInterval(t);
      }
    }, 120);

    // تنظيف باراميتر قديم إن وُجد — لا تُبقِ السايدبار مفتوحاً تلقائياً
    if (new URLSearchParams(window.location.search).get('open_sidebar') === '1') {
      const url = new URL(window.location.href);
      url.searchParams.delete('open_sidebar');
      window.history.replaceState({}, '', url.toString());
    }
  });
})();
</script>
