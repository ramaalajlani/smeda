(function (window) {
  'use strict';

  var _index = null;
  var _db    = null;
  var _ready = false;
  var _loading = false;
  var _cbs   = [];
  var _failCbs = [];

  var _scriptSrc = (document.currentScript && document.currentScript.src)
    ? document.currentScript.src
    : null;

  function _load() {
    if (_ready || _loading) return;
    _loading = true;
    var url = _scriptSrc
      ? new URL('syrsic_full.json', _scriptSrc).href
      : '/assets/js/pages/syrsic_full.json';
    fetch(url)
      .then(function(r) { return r.json(); })
      .then(function(db) {
        _db = db;
        _buildIndex(db);
        _ready = true;
        _loading = false;
        _cbs.forEach(function(cb) { cb(); });
        _cbs = [];
        _failCbs = [];
      })
      .catch(function(err) {
        console.error(err);
        _loading = false;
        _failCbs.forEach(function(cb) { cb(err); });
        _failCbs = [];
      });
  }

  function _buildIndex(db) {
    _index = [];
    db.forEach(function(s) {
      s.divisions.forEach(function(d) {
        d.groups.forEach(function(g) {
          g.branches.forEach(function(b) {
            b.classes.forEach(function(c) {
              var classPath = s.name + ' ← ' + d.name + ' ← ' + g.name + ' ← ' + b.name;
              _index.push({
                code: c.code, name: c.name, path: classPath, level: 'class',
                sectionCode: s.code, sectionName: s.name,
                divCode: d.code, grpCode: g.code, branchCode: b.code,
                classCode: c.code, activityCode: ''
              });
              c.activities.forEach(function(a) {
                _index.push({
                  code: a.code, name: a.name,
                  path: classPath + ' ← ' + c.name, level: 'activity',
                  sectionCode: s.code, sectionName: s.name,
                  divCode: d.code, grpCode: g.code, branchCode: b.code,
                  classCode: c.code, activityCode: a.code
                });
              });
            });
          });
        });
      });
    });
  }

  function search(q, max) {
    max = max || 15;
    q = (q || '').trim();
    if (!q || q.length < 2 || !_index) return [];
    var isNum = /^\d/.test(q);
    var out = [], seen = {};
    for (var i = 0; i < _index.length; i++) {
      var item  = _index[i];
      var match = isNum
        ? item.code.indexOf(q) === 0
        : item.name.indexOf(q) !== -1 || item.path.indexOf(q) !== -1;
      if (match && !seen[item.code]) {
        seen[item.code] = 1;
        out.push(item);
        if (out.length >= max * 2) break;
      }
    }
    out.sort(function(a, b) {
      return a.level === b.level ? 0 : (a.level === 'activity' ? -1 : 1);
    });
    return out.slice(0, max);
  }

  function byCode(code) {
    if (!_index) return null;
    for (var i = 0; i < _index.length; i++) {
      if (_index[i].code === code) return _index[i];
    }
    return null;
  }

  function onReady(cb) { if (_ready) { cb(); } else { _cbs.push(cb); } }
  function isReady()   { return _ready; }
  function isLoading() { return _loading; }

  /** تحميل كسول — لا يبدأ إلا عند الحاجة (خطوة التصنيف / البحث). */
  function ensureLoaded() {
    if (_ready) return Promise.resolve();
    return new Promise(function(resolve, reject) {
      onReady(resolve);
      _failCbs.push(reject);
      _load();
    });
  }

  window.SYRSIC = {
    search: search,
    byCode: byCode,
    onReady: onReady,
    isReady: isReady,
    isLoading: isLoading,
    ensureLoaded: ensureLoaded,
    db: function() { return _db; }
  };

  // لا تحميل تلقائي عند فتح الصفحة — يُحمَّل عند الخطوة 3 أو أول تركيز على البحث.

}(window));
