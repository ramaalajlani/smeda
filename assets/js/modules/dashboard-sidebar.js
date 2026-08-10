/**
 * SMEDA — Dashboard Sidebar Module
 * Handles mobile toggle, overlay, active link highlighting
 */
(function () {
  'use strict';

  function init() {
    const sidebar   = document.getElementById('dsSidebar');
    const overlay   = document.getElementById('dsOverlay');
    const hamburger = document.getElementById('dsHamburger');
    if (!sidebar) return;

    /* ── Open / Close ── */
    function open() {
      sidebar.classList.add('open');
      if (overlay) overlay.classList.add('visible');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('visible');
      document.body.style.overflow = '';
    }
    function toggle() { sidebar.classList.contains('open') ? close() : open(); }

    if (hamburger) hamburger.addEventListener('click', toggle);
    if (overlay)   overlay.addEventListener('click', close);

    /* Close on ESC */
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

    /* Close on nav click (mobile) */
    sidebar.querySelectorAll('.ds-nav-item').forEach(link => {
      link.addEventListener('click', (e) => {
        if (link.getAttribute('data-open-ai-chat') === '1') {
          e.preventDefault();
          if (window.AiChatFab?.open) window.AiChatFab.open();
          else document.querySelector('#aiChatFabRoot .aic-fab')?.click();
        }
        if (window.innerWidth < 1024) close();
      });
    });

    /* ── Active link ── */
    const current = window.location.pathname.split('/').pop() || 'index.php';
    const currentBase = current.replace(/\.php$/i, '');
    sidebar.querySelectorAll('.ds-nav-item[href]').forEach(link => {
      const href = link.getAttribute('href') || '';
      const page = (href.split('/').pop() || '').split('?')[0];
      const pageBase = page.replace(/\.php$/i, '');
      if (page && (current === page || currentBase === pageBase)) {
        link.classList.add('active');
      }
    });

    /* ── User info ── */
    const user = window.AppAuth?.getUser?.() || {};
    const nameEl   = document.getElementById('dsUserName');
    const emailEl  = document.getElementById('dsUserEmail');
    const roleEl   = document.getElementById('dsUserRole');
    const avatarEl = document.getElementById('dsUserAvatar');
    const topbarNameEl   = document.getElementById('dsTopbarName');
    const topbarAvatarEl = document.getElementById('dsTopbarAvatar');

    if (nameEl)   nameEl.textContent   = user.name  || '—';
    if (emailEl)  emailEl.textContent  = user.email || '—';
    if (roleEl)   roleEl.textContent   = (user.roles || [])[0] || '';
    if (avatarEl) avatarEl.textContent = (user.name || '?')[0].toUpperCase();
    if (topbarNameEl)   topbarNameEl.textContent   = user.name || '—';
    if (topbarAvatarEl) topbarAvatarEl.textContent = (user.name || '?')[0].toUpperCase();

    /* ── Logout ── */
    document.querySelectorAll('.ds-logout-btn').forEach(btn => {
      btn.addEventListener('click', () => window.AppAuth?.logout?.());
    });

    /* ── Resize: reset on desktop ── */
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (window.innerWidth >= 1024) {
          close();
          document.body.style.overflow = '';
        }
      }, 150);
    });

    /* تنظيف باراميتر قديم إن وُجد */
    if (new URLSearchParams(window.location.search).get('open_sidebar') === '1') {
      const url = new URL(window.location.href);
      url.searchParams.delete('open_sidebar');
      window.history.replaceState({}, '', url.toString());
    }
  }

  /* Wait for DOM */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
