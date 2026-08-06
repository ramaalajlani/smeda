function initLogoFallback() {
  const logo = document.querySelector(".brand-logo-img");
  const fallback = document.querySelector(".brand-logo-fallback");

  if (!logo || !fallback) return;

  logo.addEventListener("error", () => {
    logo.style.display = "none";
    fallback.style.display = "flex";
  });
}