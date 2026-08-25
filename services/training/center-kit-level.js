/** يحوّل حقل المستوى في نموذج الحقيبة إلى قائمة منسدلة. */
(function () {
  function upgradeKitLevelSelect() {
    var input = document.getElementById('level');
    if (!input || input.tagName !== 'INPUT') return false;
    if (!document.getElementById('category_id') || !document.getElementById('form')) return false;

    var map = { 'مبتدئ': 'beginner', 'متوسط': 'intermediate', 'متقدم': 'advanced' };
    var current = map[input.value] || input.value || '';
    var select = document.createElement('select');
    select.id = 'level';
    select.className = input.className || '';
    select.innerHTML =
      '<option value="">— بدون تحديد —</option>' +
      '<option value="beginner">مبتدئ</option>' +
      '<option value="intermediate">متوسط</option>' +
      '<option value="advanced">متقدم</option>';
    if (current && select.querySelector('option[value="' + current + '"]')) {
      select.value = current;
    }
    input.replaceWith(select);
    return true;
  }

  function boot() {
    if (upgradeKitLevelSelect()) return;
    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (upgradeKitLevelSelect() || tries >= 20) clearInterval(timer);
    }, 150);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
