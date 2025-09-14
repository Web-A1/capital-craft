document.addEventListener("DOMContentLoaded", function () {
  const questions = document.querySelectorAll(".faq__question");
  const items = Array.from(document.querySelectorAll(".faq__item"));
  const tagLinks = Array.from(document.querySelectorAll(".faq-tags__link"));
  const chipLinks = Array.from(document.querySelectorAll(".faq__tag-chip"));

  function getAnswerForQuestion(question) {
    // Ответ — следующий элемент после кнопки (как на прод)
    return question.nextElementSibling;
  }

  function collapseQuestion(q) {
    const ans = getAnswerForQuestion(q);
    q.setAttribute("aria-expanded", "false");
    if (!ans) return;
    ans.classList.remove("open");
    ans.style.maxHeight = "0px";
    ans.addEventListener("transitionend", function handler(e) {
      if (
        e.propertyName === "max-height" &&
        q.getAttribute("aria-expanded") === "false"
      ) {
        ans.style.removeProperty("max-height");
        ans.removeEventListener("transitionend", handler);
      }
    });
  }

  function expandQuestion(q) {
    const ans = getAnswerForQuestion(q);
    q.setAttribute("aria-expanded", "true");
    if (!ans) return;
    // force reflow then set height
    void ans.offsetHeight;
    ans.classList.add("open");
    ans.style.maxHeight = ans.scrollHeight + 20 + "px";
  }

  questions.forEach(function (question) {
    question.addEventListener("click", function (e) {
      const isExpanded = question.getAttribute("aria-expanded") === "true";

      // Закрываем другие
      questions.forEach(function (q) {
        if (q !== question && q.getAttribute("aria-expanded") === "true") {
          collapseQuestion(q);
        }
      });

      if (isExpanded) {
        collapseQuestion(question);
      } else {
        expandQuestion(question);
      }
    });

    question.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        question.click();
      }
    });
  });

  // Tag filtering without reload
  function applyTagFilter(alias) {
    const norm = (alias || "").toLowerCase();
    items.forEach(function (it) {
      const tags = (it.getAttribute("data-tags") || "")
        .toLowerCase()
        .split(/\s+/)
        .filter(Boolean);
      const show = !norm || tags.includes(norm);
      it.style.display = show ? "" : "none";
    });
    tagLinks.forEach(function (lnk) {
      lnk.classList.remove("is-active");
      const href = lnk.getAttribute("href") || "";
      const m = href.match(/\btag=([^&#]+)/);
      const lnkAlias = m ? decodeURIComponent(m[1]).toLowerCase() : "";
      if ((!norm && !lnkAlias) || (norm && lnkAlias === norm)) {
        lnk.classList.add("is-active");
      }
    });
  }

  // Intercept tag clicks (header pills)
  tagLinks.forEach(function (lnk) {
    lnk.addEventListener("click", function (e) {
      e.preventDefault();
      const href = lnk.getAttribute("href") || "";
      const m = href.match(/\btag=([^&#]+)/);
      const alias = m ? decodeURIComponent(m[1]) : "";
      applyTagFilter(alias);
      // Update URL without reload
      const newUrl = alias ? "/faq?tag=" + encodeURIComponent(alias) : "/faq";
      if (history && history.pushState) {
        history.pushState({ tag: alias }, "", newUrl);
      }
      // Collapse any open answers to avoid odd focus states
      questions.forEach(function (q) {
        if (q.getAttribute("aria-expanded") === "true") {
          q.click();
        }
      });
    });
  });

  // Intercept per-question tag chips (desktop)
  chipLinks.forEach(function (lnk) {
    lnk.addEventListener("click", function (e) {
      e.preventDefault();
      const href = lnk.getAttribute("href") || "";
      const m = href.match(/\btag=([^&#]+)/);
      const alias = m ? decodeURIComponent(m[1]) : "";
      applyTagFilter(alias);
      const newUrl = alias ? "/faq?tag=" + encodeURIComponent(alias) : "/faq";
      if (history && history.pushState) {
        history.pushState({ tag: alias }, "", newUrl);
      }
      questions.forEach(function (q) {
        if (q.getAttribute("aria-expanded") === "true") q.click();
      });
      // Убираем автоскролл: оставляем позицию страницы неизменной при сортировке
    });
  });

  function openFromHash() {
    const m = location.hash.match(/faq-q-(\d+)/);
    if (!m) return;
    const el = document.getElementById("faq-q-" + m[1]);
    if (!el) return;
    const q = el.querySelector(".faq__question");
    if (q && q.getAttribute("aria-expanded") !== "true") {
      q.click();
    }
    // Центрируем открытый вопрос и показываем хедер после перехода по якорю
    setTimeout(function () {
      if (window.headerControl) {
        window.headerControl.freeze();
      }
      el.scrollIntoView({ block: "center", behavior: "instant" });
      if (window.matchMedia("(max-width: 767px)").matches) {
        const header = document.querySelector(".site-header");
        if (header) {
          window.scrollBy(0, -header.offsetHeight);
        }
      }
      if (window.headerControl) {
        window.headerControl.pin();
        window.headerControl.unfreeze();
      }
    }, 0);
  }

  // Apply initial filter from URL
  (function initFromUrl() {
    const m = location.search.match(/\btag=([^&#]+)/);
    const alias = m ? decodeURIComponent(m[1]) : "";
    applyTagFilter(alias);
    openFromHash();
  })();

  // Handle back/forward
  window.addEventListener("popstate", function () {
    const m = location.search.match(/\btag=([^&#]+)/);
    const alias = m ? decodeURIComponent(m[1]) : "";
    applyTagFilter(alias);
    openFromHash();
  });

  window.addEventListener("hashchange", openFromHash);
});
