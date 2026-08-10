/**
 * أدوات مشتركة لتطبيق المركز التدريبي.
 * v1.4 — نطاق الصف، بحث، حماية التعديلات غير المحفوظة.
 */
(function (w) {
  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    });
  }

  w.TC = {
    esc: esc,

    cacheCourse: function (c) {
      if (!c || !c.id) return;
      try {
        sessionStorage.setItem('tc_c_' + c.id, JSON.stringify({
          title: c.title || '',
          code: c.course_code || '',
          trainer: (c.trainer && c.trainer.name) || '',
        }));
      } catch (e) {}
    },
    cacheCourses: function (list) {
      (list || []).forEach(this.cacheCourse, this);
    },
    getCourse: function (id) {
      try { return JSON.parse(sessionStorage.getItem('tc_c_' + id) || 'null'); } catch (e) { return null; }
    },

    cacheKit: function (k) {
      if (!k || !k.id) return;
      try {
        sessionStorage.setItem('tc_k_' + k.id, JSON.stringify({
          name: k.name || '',
          code: k.code || '',
          sector: k.sector || '',
          level: k.level || '',
          hours: k.hours != null ? k.hours : '',
        }));
      } catch (e) {}
    },
    cacheKits: function (list) {
      (list || []).forEach(this.cacheKit, this);
    },
    getKit: function (id) {
      try { return JSON.parse(sessionStorage.getItem('tc_k_' + id) || 'null'); } catch (e) { return null; }
    },

    cacheGroup: function (g) {
      if (!g || !g.id) return;
      try {
        sessionStorage.setItem('tc_g_' + g.id, JSON.stringify({
          name: g.name || '',
          code: g.code || '',
          course_id: g.course_id || '',
        }));
      } catch (e) {}
    },
    getGroup: function (id) {
      try { return JSON.parse(sessionStorage.getItem('tc_g_' + id) || 'null'); } catch (e) { return null; }
    },

    /** ?group= من الرابط */
    groupId: function () {
      try { return new URLSearchParams(location.search).get('group') || ''; } catch (e) { return ''; }
    },

    /** أضف/احذف group من رابط نسبي */
    withGroup: function (url, groupId) {
      var u = String(url || '');
      var gid = groupId === undefined ? this.groupId() : (groupId || '');
      if (!gid) return u.replace(/([?&])group=\d+&?/g, '$1').replace(/[?&]$/, '');
      if (/[?&]group=/.test(u)) return u.replace(/([?&])group=\d+/, '$1group=' + gid);
      return u + (u.indexOf('?') >= 0 ? '&' : '?') + 'group=' + gid;
    },

    /**
     * شارة النطاق أعلى المحتوى.
     * mount: عنصر أو id
     * opts: { groupId, groupName, courseHref, groupHref }
     */
    renderScope: function (mount, opts) {
      opts = opts || {};
      var el = typeof mount === 'string' ? document.getElementById(mount) : mount;
      if (!el) return;
      var gid = opts.groupId || this.groupId();
      var gName = opts.groupName || (gid && this.getGroup(gid) && this.getGroup(gid).name) || '';
      if (gid) {
        el.innerHTML =
          '<div class="tc-scope is-group">' +
            '<div class="tc-scope-txt"><i class="bi bi-collection"></i> النطاق: <strong>الصف' +
            (gName ? ' «' + esc(gName) + '»' : '') +
            '</strong> — الحضور والدرجات لهذا الصف فقط</div>' +
            '<div class="tc-scope-acts">' +
              (opts.groupHref ? '<a href="' + esc(opts.groupHref) + '">متدربو الصف</a>' : '') +
              (opts.courseHref ? '<a class="alt" href="' + esc(opts.courseHref) + '">عرض كل الدورة</a>' : '') +
            '</div>' +
          '</div>';
      } else {
        el.innerHTML =
          '<div class="tc-scope is-course">' +
            '<div class="tc-scope-txt"><i class="bi bi-mortarboard"></i> النطاق: <strong>كل الدورة</strong> — كل المتدربين' +
            (opts.groupsHref ? ' · <a href="' + esc(opts.groupsHref) + '">للعمل على صف محدد</a>' : '') +
            '</div>' +
          '</div>';
      }
    },

    /** بحث فوري داخل عناصر قائمة */
    bindListSearch: function (input, itemSelector) {
      var inp = typeof input === 'string' ? document.querySelector(input) : input;
      if (!inp) return;
      var run = function () {
        var q = (inp.value || '').trim().toLowerCase();
        document.querySelectorAll(itemSelector).forEach(function (item) {
          var hay = (item.getAttribute('data-search') || item.textContent || '').toLowerCase();
          item.style.display = (!q || hay.indexOf(q) >= 0) ? '' : 'none';
        });
      };
      inp.addEventListener('input', run);
    },

    searchBoxHtml: function (id, placeholder) {
      id = id || 'tcSearch';
      placeholder = placeholder || 'بحث...';
      return (
        '<div class="tc-search">' +
          '<i class="bi bi-search"></i>' +
          '<input type="search" id="' + esc(id) + '" placeholder="' + esc(placeholder) + '" autocomplete="off">' +
        '</div>'
      );
    },

    /* ---- حماية التعديلات غير المحفوظة ---- */
    _dirty: false,
    markDirty: function () { this._dirty = true; },
    clearDirty: function () { this._dirty = false; },
    isDirty: function () { return !!this._dirty; },

    watchDirty: function (root) {
      var self = this;
      var el = typeof root === 'string' ? document.querySelector(root) : root;
      if (!el) return;
      var mark = function () { self.markDirty(); };
      el.addEventListener('input', mark);
      el.addEventListener('change', mark);
    },

    /** اعتراض روابط الرجوع/التنقّل عند وجود تعديلات */
    guardNav: function (selector) {
      var self = this;
      document.querySelectorAll(selector || 'a[href]').forEach(function (a) {
        if (a.dataset.tcGuard === '1') return;
        a.dataset.tcGuard = '1';
        a.addEventListener('click', function (e) {
          if (!self.isDirty()) return;
          if (!confirm('لديك تعديلات غير محفوظة. مغادرة الصفحة؟')) {
            e.preventDefault();
            e.stopPropagation();
          } else {
            self.clearDirty();
          }
        });
      });
      if (!self._unloadBound) {
        self._unloadBound = true;
        w.addEventListener('beforeunload', function (e) {
          if (!self.isDirty()) return;
          e.preventDefault();
          e.returnValue = '';
        });
      }
    },

    toast: function (msg, type) {
      type = type || 'ok';
      var wrap = document.getElementById('tcToastWrap');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'tcToastWrap';
        wrap.className = 'tc-toast-wrap';
        document.body.appendChild(wrap);
      }
      var icon = type === 'err' ? 'bi-x-circle-fill' : (type === 'info' ? 'bi-info-circle-fill' : 'bi-check-circle-fill');
      var el = document.createElement('div');
      el.className = 'tc-toast ' + (type === 'err' ? 'err' : (type === 'info' ? 'info' : 'ok'));
      var i = document.createElement('i'); i.className = 'bi ' + icon;
      var span = document.createElement('span'); span.textContent = String(msg || '');
      var x = document.createElement('i'); x.className = 'bi bi-x x';
      el.appendChild(i); el.appendChild(span); el.appendChild(x);
      wrap.appendChild(el);
      var rm = function () { el.classList.add('out'); setTimeout(function () { el.remove(); }, 250); };
      x.addEventListener('click', rm);
      setTimeout(rm, type === 'err' ? 4500 : 2800);
    },

    confirm: function (msg) {
      return new Promise(function (res) {
        var ov = document.createElement('div');
        ov.className = 'tc-modal-ov';
        var box = document.createElement('div'); box.className = 'tc-modal';
        var mi = document.createElement('div'); mi.className = 'mi'; mi.innerHTML = '<i class="bi bi-question-lg"></i>';
        var p = document.createElement('p'); p.textContent = String(msg || '');
        var acts = document.createElement('div'); acts.className = 'acts';
        var no = document.createElement('button'); no.className = 'no'; no.textContent = 'إلغاء';
        var yes = document.createElement('button'); yes.className = 'yes'; yes.textContent = 'تأكيد';
        acts.appendChild(no); acts.appendChild(yes);
        box.appendChild(mi); box.appendChild(p); box.appendChild(acts);
        ov.appendChild(box); document.body.appendChild(ov);
        requestAnimationFrame(function () { ov.classList.add('open'); });
        var done = function (v) { ov.classList.remove('open'); setTimeout(function () { ov.remove(); }, 200); res(v); };
        yes.addEventListener('click', function () { done(true); });
        no.addEventListener('click', function () { done(false); });
        ov.addEventListener('click', function (e) { if (e.target === ov) done(false); });
      });
    },
  };
})(window);
