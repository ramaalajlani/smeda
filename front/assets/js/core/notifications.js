/**
 * نظام الإشعارات — يُحمَّل في كل الصفحات عبر scripts.php
 * يضيف زر الجرس في الـ topbar ويجلب الإشعارات كل 60 ثانية
 */
(function () {
  'use strict';

  const POLL_INTERVAL = 60000; // 60 ثانية

  async function fetchSummary() {
    const token = window.AppAuth?.getToken?.();
    if (!token) return null;
    try {
      const r = await fetch(window.APP_CONFIG.API_BASE_URL + '/notifications/summary', {
        headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
      });
      return r.ok ? r.json() : null;
    } catch {
      return null;
    }
  }

  async function markRead(id) {
    const token = window.AppAuth?.getToken?.();
    if (!token) return;
    await fetch(window.APP_CONFIG.API_BASE_URL + '/notifications/' + id + '/read', {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
    }).catch(() => {});
  }

  async function markAllRead() {
    const token = window.AppAuth?.getToken?.();
    if (!token) return;
    await fetch(window.APP_CONFIG.API_BASE_URL + '/notifications/read-all', {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
    }).catch(() => {});
    updateBadge(0);
    renderDropdown([]);
  }

  function colorClass(c) {
    const map = { primary: '#17947B', success: '#16a34a', warning: '#d97706', danger: '#dc2626', info: '#0ea5e9' };
    return map[c] || map.primary;
  }

  function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return 'الآن';
    if (diff < 3600) return Math.floor(diff / 60) + ' د';
    if (diff < 86400) return Math.floor(diff / 3600) + ' س';
    return Math.floor(diff / 86400) + ' ي';
  }

  function updateBadge(count) {
    const badge = document.getElementById('notif-badge');
    if (!badge) return;
    badge.textContent = count > 99 ? '99+' : count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }

  function renderDropdown(items) {
    const list = document.getElementById('notif-list');
    if (!list) return;

    if (!items.length) {
      list.innerHTML = `<div style="text-align:center;padding:30px 16px;color:#9ca3af;font-size:.88rem">
        <i class="bi bi-bell-slash" style="font-size:2rem;display:block;margin-bottom:8px"></i>لا توجد إشعارات
      </div>`;
      return;
    }

    list.innerHTML = items.map(n => `
      <div class="notif-item ${n.is_read ? '' : 'notif-unread'}" data-id="${n.id}" onclick="window.AppNotif.handleClick(${n.id}, '${n.action_url || ''}')">
        <div class="notif-icon-wrap" style="background:${colorClass(n.color)}22">
          <i class="${n.icon || 'bi-bell-fill'}" style="color:${colorClass(n.color)}"></i>
        </div>
        <div class="notif-content">
          <div class="notif-title">${window.APP_HELPERS?.e(n.title) || n.title}</div>
          ${n.body ? `<div class="notif-body">${window.APP_HELPERS?.e(n.body) || n.body}</div>` : ''}
          <div class="notif-time">${timeAgo(n.created_at)}</div>
        </div>
      </div>`).join('');
  }

  async function refresh() {
    const data = await fetchSummary();
    if (!data) return;
    updateBadge(data.unread_count || 0);
    renderDropdown(data.latest || []);
  }

  function injectBell() {
    const topbar = document.querySelector('.app-topbar-user') || document.querySelector('.topbar-actions');
    if (!topbar || document.getElementById('notif-btn')) return;

    const btn = document.createElement('div');
    btn.id = 'notif-btn';
    btn.innerHTML = `
      <button class="notif-bell-btn" id="notif-bell" aria-label="الإشعارات" onclick="window.AppNotif.toggleDropdown()">
        <i class="bi bi-bell-fill"></i>
        <span id="notif-badge" style="display:none"></span>
      </button>
      <div id="notif-dropdown" class="notif-dropdown" style="display:none">
        <div class="notif-header">
          <span style="font-weight:800;font-size:.95rem">الإشعارات</span>
          <div style="display:flex;gap:8px;align-items:center">
            <button onclick="window.AppNotif.markAllRead()" class="notif-mark-all">تحديد الكل كمقروء</button>
            <a href="${window.APP_CONFIG?.FRONT_BASE || ''}/front/inbox/inbox-list.php" class="notif-inbox-link"><i class="bi bi-envelope-fill"></i></a>
          </div>
        </div>
        <div id="notif-list" style="max-height:340px;overflow-y:auto"></div>
        <div class="notif-footer">
          <a href="${window.APP_CONFIG?.FRONT_BASE || ''}/front/notifications/notifications-list.php">عرض كل الإشعارات</a>
        </div>
      </div>`;

    topbar.insertBefore(btn, topbar.firstChild);

    document.addEventListener('click', (e) => {
      if (!btn.contains(e.target)) {
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.style.display = 'none';
      }
    });
  }

  function injectStyles() {
    if (document.getElementById('notif-styles')) return;
    const s = document.createElement('style');
    s.id = 'notif-styles';
    s.textContent = `
      #notif-btn { position:relative; }
      .notif-bell-btn { background:rgba(255,255,255,.15); border:none; border-radius:12px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#fff; font-size:1.1rem; position:relative; transition:background .2s; }
      .notif-bell-btn:hover { background:rgba(255,255,255,.25); }
      #notif-badge { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; font-size:.65rem; font-weight:800; min-width:18px; height:18px; border-radius:20px; display:flex; align-items:center; justify-content:center; padding:0 4px; border:2px solid #17947B; }
      .notif-dropdown { position:absolute; top:calc(100% + 8px); left:0; width:340px; background:#fff; border:1px solid rgba(23,148,123,.13); border-radius:18px; box-shadow:0 20px 40px rgba(0,0,0,.12); z-index:9999; overflow:hidden; }
      @media (max-width:400px) { .notif-dropdown { width:calc(100vw - 20px); left:auto; right:-10px; } }
      .notif-header { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid rgba(23,148,123,.1); }
      .notif-mark-all { background:none; border:none; color:#17947B; font-size:.78rem; font-weight:700; cursor:pointer; }
      .notif-inbox-link { color:#17947B; font-size:1rem; }
      .notif-footer { text-align:center; padding:10px; border-top:1px solid rgba(23,148,123,.1); }
      .notif-footer a { color:#17947B; font-weight:700; font-size:.84rem; text-decoration:none; }
      .notif-item { display:flex; gap:12px; padding:12px 16px; cursor:pointer; transition:background .15s; border-bottom:1px solid rgba(0,0,0,.04); }
      .notif-item:hover { background:#f9fafb; }
      .notif-unread { background:#eaf8f4; }
      .notif-unread:hover { background:#d1f5ec; }
      .notif-icon-wrap { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
      .notif-content { flex:1; min-width:0; }
      .notif-title { font-weight:700; font-size:.87rem; color:#16332E; line-height:1.4; margin-bottom:2px; }
      .notif-body { font-size:.78rem; color:#6b7280; margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
      .notif-time { font-size:.72rem; color:#9ca3af; font-weight:700; }
    `;
    document.head.appendChild(s);
  }

  window.AppNotif = {
    toggleDropdown() {
      const dd = document.getElementById('notif-dropdown');
      if (!dd) return;
      const visible = dd.style.display !== 'none';
      dd.style.display = visible ? 'none' : 'block';
      if (!visible) refresh();
    },
    async handleClick(id, url) {
      await markRead(id);
      const item = document.querySelector(`.notif-item[data-id="${id}"]`);
      if (item) item.classList.remove('notif-unread');
      const badge = document.getElementById('notif-badge');
      if (badge && badge.style.display !== 'none') {
        const cur = parseInt(badge.textContent, 10) || 1;
        updateBadge(Math.max(0, cur - 1));
      }
      if (url && url !== 'undefined') {
        document.getElementById('notif-dropdown').style.display = 'none';
        location.href = url;
      }
    },
    async markAllRead() {
      await markAllRead();
    },
    refresh,
  };

  function init() {
    if (!window.AppAuth?.isLoggedIn?.()) return;
    injectStyles();
    injectBell();
    refresh();
    setInterval(refresh, POLL_INTERVAL);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(init, 500));
  } else {
    setTimeout(init, 500);
  }
})();
