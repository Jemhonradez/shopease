document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".navbar");
  let prevScroll = window.scrollY;

  window.addEventListener("scroll", () => {
    const currentScrollY = window.scrollY;

    if (currentScrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }

    if (currentScrollY > prevScroll && currentScrollY > 700) {
      navbar.classList.add("hidden");
    } else {
      navbar.classList.remove("hidden");
    }

    prevScroll = currentScrollY;

  });
});
