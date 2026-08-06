/**
 * Global motion — SSF-style scroll reveal & counters
 */
(function () {
  'use strict';

  function initReveal() {
    var els = document.querySelectorAll('[data-reveal], [data-motion="fade-up"]');
    if (!els.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(function (el) { el.classList.add('is-visible', 'visible'); });
      return;
    }

    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible', 'visible');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    els.forEach(function (el) { obs.observe(el); });
  }

  function initCounters() {
    var els = document.querySelectorAll('[data-count], .hero-stat-val[data-count-target]');
    if (!els.length) return;

    function animate(el, target) {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        el.textContent = target;
        return;
      }
      var start = performance.now();
      var dur = 1400;
      function tick(now) {
        var p = Math.min((now - start) / dur, 1);
        var ease = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * ease).toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    }

    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var target = parseInt(el.getAttribute('data-count') || el.getAttribute('data-count-target') || '0', 10);
        animate(el, target);
        obs.unobserve(el);
      });
    }, { threshold: 0.3 });

    els.forEach(function (el) { obs.observe(el); });
  }

  function initStaggerCards() {
    document.querySelectorAll('.inc-grid, .service-card-row, .row.g-4').forEach(function (grid) {
      var cards = grid.querySelectorAll('.inc-card:not(.is-visible), .service-card, .news-card');
      cards.forEach(function (card, i) {
        if (!card.hasAttribute('data-reveal')) {
          card.setAttribute('data-reveal', '');
          card.setAttribute('data-reveal-delay', String(Math.min((i % 5) + 1, 5)));
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initStaggerCards();
      initReveal();
      initCounters();
    });
  } else {
    initStaggerCards();
    initReveal();
    initCounters();
  }
})();
