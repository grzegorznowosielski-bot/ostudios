(function () {
  var header = document.querySelector(".site-header");
  var toggle = document.querySelector(".menu-toggle");
  var mobileNav = document.querySelector(".nav-mobile");
  var mobileLinks = mobileNav ? mobileNav.querySelectorAll("a") : [];

  function onScroll() {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 24);
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  function closeMenu() {
    if (!toggle || !mobileNav) return;
    toggle.setAttribute("aria-expanded", "false");
    mobileNav.classList.remove("is-open");
    mobileNav.setAttribute("aria-hidden", "true");
    document.body.classList.remove("menu-open");
    document.body.style.overflow = "";
  }

  function openMenu() {
    if (!toggle || !mobileNav) return;
    toggle.setAttribute("aria-expanded", "true");
    mobileNav.classList.add("is-open");
    mobileNav.setAttribute("aria-hidden", "false");
    document.body.classList.add("menu-open");
    document.body.style.overflow = "hidden";
  }

  if (toggle && mobileNav) {
    mobileNav.setAttribute("aria-hidden", "true");

    toggle.addEventListener("click", function () {
      var expanded = toggle.getAttribute("aria-expanded") === "true";
      if (expanded) closeMenu();
      else openMenu();
    });

    mobileLinks.forEach(function (link) {
      link.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth >= 880) closeMenu();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeMenu();
    });
  }

  var reveals = document.querySelectorAll("[data-reveal]");
  if (reveals.length && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            e.target.classList.add("is-visible");
            io.unobserve(e.target);
          }
        });
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.05 }
    );
    reveals.forEach(function (el) {
      io.observe(el);
    });
  } else {
    reveals.forEach(function (el) {
      el.classList.add("is-visible");
    });
  }

  document.querySelectorAll('form[action="#"]').forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
    });
  });

  /* Hero: rotacja wybranych klipów Vimeo (kolejność = najpierw najświeższy, potem wybrane realizacje) */
  var HERO_REEL = [
    {
      id: "1164039909",
      h: "c3aef9ae08",
      titlePl: "Gepardzik Leo i zgaszona polana",
      titleEn: "Gepardzik Leo i zgaszona polana",
    },
    {
      id: "171885635",
      titlePl: "Spot Krakowa 00'30\"",
      titleEn: "Kraków spot 00'30\"",
    },
    {
      id: "122529232",
      titlePl: "Film jubileuszowy Uniwersytetu Jagiellońskiego",
      titleEn: "Jagiellonian University jubilee film",
    },
    {
      id: "183860975",
      titlePl: "SMOG",
      titleEn: "SMOG",
    },
  ];

  function heroReelSrc(entry) {
    var q = "badge=0&autopause=0&title=0&byline=0&portrait=0";
    if (entry.h) {
      return (
        "https://player.vimeo.com/video/" +
        entry.id +
        "?h=" +
        encodeURIComponent(entry.h) +
        "&" +
        q
      );
    }
    return "https://player.vimeo.com/video/" + entry.id + "?" + q;
  }

  var iframe = document.getElementById("hero-reel-iframe");
  var titleEl = document.getElementById("hero-reel-title");
  var dotsEl = document.getElementById("hero-reel-dots");
  var btnPrev = document.getElementById("hero-reel-prev");
  var btnNext = document.getElementById("hero-reel-next");
  if (iframe && titleEl && dotsEl && btnPrev && btnNext && HERO_REEL.length) {
    var lang = (document.documentElement.getAttribute("lang") || "pl").toLowerCase().indexOf("en") === 0 ? "en" : "pl";
    var idx = 0;
    var autoMs = 14000;
    var timer = null;

    function titleFor(entry) {
      return lang === "en" ? entry.titleEn : entry.titlePl;
    }

    function show(at) {
      idx = (at + HERO_REEL.length) % HERO_REEL.length;
      var entry = HERO_REEL[idx];
      iframe.src = heroReelSrc(entry);
      iframe.setAttribute("title", titleFor(entry) + " — Outstanding Studios");
      titleEl.textContent = titleFor(entry);
      var dotBtns = dotsEl.querySelectorAll(".hero-reel-dot");
      dotBtns.forEach(function (b, i) {
        b.setAttribute("aria-current", i === idx ? "true" : "false");
      });
    }

    function buildDots() {
      dotsEl.innerHTML = "";
      HERO_REEL.forEach(function (entry, i) {
        var b = document.createElement("button");
        b.type = "button";
        b.className = "hero-reel-dot";
        b.setAttribute("aria-label", (lang === "en" ? "Play: " : "Odtwórz: ") + titleFor(entry));
        b.addEventListener("click", function () {
          show(i);
          restartAuto();
        });
        dotsEl.appendChild(b);
      });
    }

    function next() {
      show(idx + 1);
    }

    function prev() {
      show(idx - 1);
    }

    function clearAuto() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function startAuto() {
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
      clearAuto();
      timer = setInterval(next, autoMs);
    }

    function restartAuto() {
      clearAuto();
      startAuto();
    }

    buildDots();
    show(0);

    btnNext.addEventListener("click", function () {
      next();
      restartAuto();
    });
    btnPrev.addEventListener("click", function () {
      prev();
      restartAuto();
    });

    document.addEventListener("visibilitychange", function () {
      if (document.hidden) clearAuto();
      else startAuto();
    });

    startAuto();
  }

  var formStatus = document.getElementById("form-status");
  if (formStatus) {
    var params = new URLSearchParams(window.location.search);
    var pageLang = document.documentElement.lang || "pl";
    if (params.get("sent") === "1") {
      formStatus.textContent =
        pageLang === "en"
          ? "Thank you — we’ve received your message and will get back to you soon."
          : "Dziękujemy — wiadomość została wysłana. Odezwiemy się wkrótce.";
      formStatus.hidden = false;
      formStatus.className = "form-status form-status--success";
      formStatus.focus();
      var goTo =
        pageLang === "en" ? document.getElementById("contact") : document.getElementById("kontakt");
      if (goTo) goTo.scrollIntoView({ behavior: "smooth", block: "start" });
    } else if (params.get("error") === "1") {
      formStatus.textContent =
        pageLang === "en"
          ? "Something went wrong. Please try again or email contact@ostudios.pl."
          : "Coś poszło nie tak. Spróbuj ponownie lub napisz na contact@ostudios.pl.";
      formStatus.hidden = false;
      formStatus.className = "form-status form-status--error";
      formStatus.focus();
      var goErr =
        pageLang === "en" ? document.getElementById("contact") : document.getElementById("kontakt");
      if (goErr) goErr.scrollIntoView({ behavior: "smooth", block: "start" });
    }
    if (params.get("sent") === "1" || params.get("error") === "1") {
      params.delete("sent");
      params.delete("error");
      var qs = params.toString();
      var clean = window.location.pathname + (qs ? "?" + qs : "") + window.location.hash;
      window.history.replaceState(null, "", clean);
    }
  }

  var COOKIE_KEY = "os_cookie_consent_v1";
  function getCookieConsent() {
    try {
      return localStorage.getItem(COOKIE_KEY);
    } catch (e) {
      return "essential";
    }
  }
  function setCookieConsent(value) {
    try {
      localStorage.setItem(COOKIE_KEY, value);
    } catch (e) {}
  }
  if (!getCookieConsent()) {
    var path = window.location.pathname || "";
    var inEnFolder = /\/en(\/|$)/.test(path);
    var policyHref = inEnFolder ? "privacy-policy.html" : "polityka-prywatnosci.html";
    var pageLang = document.documentElement.lang || "pl";
    var isPl = pageLang.slice(0, 2).toLowerCase() !== "en";

    var wrap = document.createElement("div");
    wrap.className = "cookie-banner";
    wrap.setAttribute("role", "region");
    wrap.setAttribute(
      "aria-label",
      isPl ? "Informacja o plikach cookies" : "Cookie and privacy notice"
    );

    var inner = document.createElement("div");
    inner.className = "cookie-banner__inner wrap";

    var text = document.createElement("p");
    text.className = "cookie-banner__text";
    text.textContent = isPl
      ? "Używamy plików cookies i podobnych technologii (w tym zapis wyboru w przeglądarce, treści osadzone z Vimeo, czcionki z Google). Możesz zaakceptować wszystkie lub ograniczyć się do niezbędnych. Szczegóły: polityka prywatności."
      : "We use cookies and similar technologies (including to store your choice, embedded Vimeo content, and Google Fonts). You can accept all or only essential cookies. Details are in the privacy policy.";

    var actions = document.createElement("div");
    actions.className = "cookie-banner__actions";

    var link = document.createElement("a");
    link.href = policyHref;
    link.className = "cookie-banner__link";
    link.textContent = isPl ? "Polityka prywatności" : "Privacy policy";

    var btnEssential = document.createElement("button");
    btnEssential.type = "button";
    btnEssential.className = "btn btn-ghost cookie-banner__btn";
    btnEssential.textContent = isPl ? "Tylko niezbędne" : "Essential only";

    var btnAll = document.createElement("button");
    btnAll.type = "button";
    btnAll.className = "btn btn-primary cookie-banner__btn";
    btnAll.textContent = isPl ? "Akceptuję wszystkie" : "Accept all";

    function dismissBanner() {
      wrap.remove();
    }

    btnEssential.addEventListener("click", function () {
      setCookieConsent("essential");
      dismissBanner();
    });
    btnAll.addEventListener("click", function () {
      setCookieConsent("all");
      dismissBanner();
    });

    actions.appendChild(link);
    actions.appendChild(btnEssential);
    actions.appendChild(btnAll);
    inner.appendChild(text);
    inner.appendChild(actions);
    wrap.appendChild(inner);
    document.body.appendChild(wrap);
  }
})();
