function initMobileNavbarClose() {
  const anchorLinks = document.querySelectorAll('.main-nav .nav-link[href*="#"]');

  anchorLinks.forEach((link) => {
    link.addEventListener("click", function () {
      const navbarCollapse = document.querySelector(".navbar-collapse");
      const navbarToggler = document.querySelector(".navbar-toggler");

      if (
        navbarCollapse &&
        navbarCollapse.classList.contains("show") &&
        window.innerWidth < 1200
      ) {
        const parentMegaItem = link.closest(".mega-menu-item");
        if (!parentMegaItem) {
          navbarToggler.click();
        }
      }
    });
  });
}

function initMobileMegaMenus() {
  const megaItems = document.querySelectorAll(".mega-menu-item");

  megaItems.forEach((item) => {
    const trigger = item.querySelector(".nav-link");
    const panel = item.querySelector(".mega-menu-panel");

    if (!trigger || !panel) return;

    trigger.addEventListener("click", function (e) {
      if (window.innerWidth >= 1200) return;

      e.preventDefault();

      megaItems.forEach((otherItem) => {
        if (otherItem !== item) {
          otherItem.classList.remove("mega-open");
        }
      });

      item.classList.toggle("mega-open");
    });
  });

  document.addEventListener("click", function (e) {
    if (window.innerWidth >= 1200) return;

    megaItems.forEach((item) => {
      if (!item.contains(e.target)) {
        item.classList.remove("mega-open");
      }
    });
  });
}

function resetMegaMenusOnResize() {
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1200) {
      document.querySelectorAll(".mega-menu-item").forEach((item) => {
        item.classList.remove("mega-open");
      });
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initMobileNavbarClose();
  initMobileMegaMenus();
  resetMegaMenusOnResize();
});