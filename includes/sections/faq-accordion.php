<?php if (!defined('FAQ_ACCORDION_JS')): define('FAQ_ACCORDION_JS', true); ?>
<script>
(function () {
  function bindAccordion(root) {
    root.querySelectorAll('.faq-item').forEach(function (item) {
      var btn = item.querySelector('.faq-q');
      if (!btn || btn.dataset.faqBound) return;
      btn.dataset.faqBound = '1';
      btn.addEventListener('click', function () {
        var list = item.closest('.faq-list, .faq-block-list');
        var isOpen = item.classList.contains('is-open');
        if (list) {
          list.querySelectorAll('.faq-item').forEach(function (other) {
            other.classList.remove('is-open');
            var b = other.querySelector('.faq-q');
            if (b) b.setAttribute('aria-expanded', 'false');
          });
        }
        if (!isOpen) {
          item.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  bindAccordion(document);

  var filterBtns = document.querySelectorAll('.faq-filter-btn');
  var groups = document.querySelectorAll('[data-faq-group]');

  function applyFilter(cat) {
    filterBtns.forEach(function (btn) {
      var active = btn.getAttribute('data-faq-filter') === cat;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    groups.forEach(function (group) {
      var show = cat === 'all' || group.getAttribute('data-faq-group') === cat;
      group.classList.toggle('is-hidden', !show);
    });
  }

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyFilter(btn.getAttribute('data-faq-filter'));
    });
  });

  document.querySelectorAll('[data-faq-cat]').forEach(function (link) {
    link.addEventListener('click', function () {
      var cat = link.getAttribute('data-faq-cat');
      if (cat) sessionStorage.setItem('faqFilter', cat);
    });
  });

  var saved = sessionStorage.getItem('faqFilter');
  if (saved && document.querySelector('[data-faq-filter="' + saved + '"]')) {
    applyFilter(saved);
    sessionStorage.removeItem('faqFilter');
    if (location.hash === '#faq') {
      setTimeout(function () {
        var el = document.getElementById('faq');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 120);
    }
  }
})();
</script>
<?php endif; ?>
