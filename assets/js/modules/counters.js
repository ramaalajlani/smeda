function initCounters() {
  const counters = document.querySelectorAll(".counter");
  let countersStarted = false;

  function animateCounter(counter) {
    const target = Number(counter.getAttribute("data-target")) || 0;
    const duration = 1600;
    const startTime = performance.now();

    function updateCounter(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const currentValue = Math.floor(progress * target);

      counter.textContent = currentValue.toLocaleString("en-US");

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target.toLocaleString("en-US");
      }
    }

    requestAnimationFrame(updateCounter);
  }

  function runCountersOnView() {
    const statsSection = document.querySelector(".stats-section");
    if (!statsSection || countersStarted) return;

    const rect = statsSection.getBoundingClientRect();
    const triggerPoint = window.innerHeight - 120;

    if (rect.top < triggerPoint) {
      counters.forEach((counter) => animateCounter(counter));
      countersStarted = true;
    }
  }

  runCountersOnView();
  window.addEventListener("scroll", runCountersOnView, { passive: true });
}