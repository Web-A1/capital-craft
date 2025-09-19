(function () {
  function extractAlias(href) {
    if (!href) {
      return "";
    }

    try {
      const url = new URL(href, window.location.origin);
      return (url.searchParams.get("tag") || "").toLowerCase();
    } catch (e) {
      return "";
    }
  }

  function syncHead(doc) {
    const titleEl = doc.querySelector("title");
    if (titleEl) {
      document.title = titleEl.textContent;
    }

    const selectors = [
      'meta[name="description"]',
      'meta[property="og:url"]',
      'meta[property="og:title"]',
      'meta[property="og:description"]',
      'meta[name="twitter:title"]',
      'meta[name="twitter:description"]'
    ];

    selectors.forEach(selector => {
      const fresh = doc.querySelector(selector);
      const current = document.querySelector(selector);
      if (fresh && current) {
        current.setAttribute("content", fresh.getAttribute("content") || "");
      }
    });

    const freshCanonical = doc.querySelector('link[rel="canonical"]');
    const canonical = document.querySelector('link[rel="canonical"]');
    if (freshCanonical && canonical) {
      canonical.href = freshCanonical.href;
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    const faqSection = document.querySelector(".faq");
    if (!faqSection) {
      return;
    }

    const parser = new DOMParser();
    const faqBase = (function () {
      const fromAttr = faqSection.getAttribute("data-faq-base");
      if (fromAttr && fromAttr.trim() !== "") {
        return fromAttr.trim();
      }
      const active = faqSection.querySelector(".faq-tags__link.is-active");
      if (active) {
        return active.getAttribute("href") || "";
      }
      return window.location.pathname + window.location.search;
    })();

    function buildFaqUrl(tag) {
      try {
        const u = new URL(faqBase, window.location.origin);
        if (tag && tag !== "") {
          u.searchParams.set("tag", tag);
        } else {
          u.searchParams.delete("tag");
        }
        u.hash = "";
        return u.pathname + u.search;
      } catch (e) {
        return tag && tag !== ""
          ? window.location.pathname + "?tag=" + encodeURIComponent(tag)
          : faqBase || window.location.pathname;
      }
    }
    let currentTag =
      new URL(window.location.href).searchParams.get("tag") || "";
    currentTag = currentTag.toLowerCase();
    let questionNodes = [];
    let isLoading = false;

    function getAnswerForQuestion(button) {
      return button.nextElementSibling;
    }

    function collapseQuestion(button) {
      const answer = getAnswerForQuestion(button);
      button.setAttribute("aria-expanded", "false");
      if (!answer) {
        return;
      }
      answer.classList.remove("open");
      answer.style.maxHeight = "0px";
      answer.addEventListener("transitionend", function handle(e) {
        if (
          e.propertyName === "max-height" &&
          button.getAttribute("aria-expanded") === "false"
        ) {
          answer.style.removeProperty("max-height");
          answer.removeEventListener("transitionend", handle);
        }
      });
    }

    function expandQuestion(button) {
      const answer = getAnswerForQuestion(button);
      button.setAttribute("aria-expanded", "true");
      if (!answer) {
        return;
      }
      void answer.offsetHeight;
      answer.classList.add("open");
      answer.style.maxHeight = answer.scrollHeight + 20 + "px";
    }

    function handleQuestionClick(event) {
      const question = event.currentTarget;
      const isExpanded = question.getAttribute("aria-expanded") === "true";
      questionNodes.forEach(btn => {
        if (btn !== question && btn.getAttribute("aria-expanded") === "true") {
          collapseQuestion(btn);
        }
      });
      if (isExpanded) {
        collapseQuestion(question);
      } else {
        expandQuestion(question);
      }
    }

    function handleQuestionKey(event) {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        event.currentTarget.click();
      }
    }

    function setupQuestions() {
      questionNodes = Array.from(faqSection.querySelectorAll(".faq__question"));
      questionNodes.forEach(question => {
        question.addEventListener("click", handleQuestionClick);
        question.addEventListener("keydown", handleQuestionKey);
      });
    }

    function openFromHash() {
      const match = window.location.hash.match(/faq-q-(\d+)/);
      if (!match) {
        return;
      }
      const element = document.getElementById("faq-q-" + match[1]);
      if (!element) {
        return;
      }
      const question = element.querySelector(".faq__question");
      if (question && question.getAttribute("aria-expanded") !== "true") {
        question.click();
      }

      window.requestAnimationFrame(function () {
        if (window.headerControl) {
          window.headerControl.freeze();
        }
        element.scrollIntoView({ block: "center", behavior: "instant" });
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
      });
    }

    function attachInteractions() {
      setupQuestions();
      openFromHash();
    }

    attachInteractions();
    if (history && history.replaceState) {
      history.replaceState({ tag: currentTag }, "", window.location.href);
    }

    function replaceFaqContent(doc) {
      const newContent = doc.querySelector(".faq__content");
      const currentContent = faqSection.querySelector(".faq__content");
      if (!newContent || !currentContent) {
        return false;
      }
      currentContent.innerHTML = newContent.innerHTML;
      return true;
    }

    function loadFaq(alias, options) {
      const opts = options || {};
      const normalized = (alias || "").toLowerCase();
      if (!opts.force && normalized === currentTag) {
        return;
      }
      if (isLoading) {
        return;
      }

      const url = buildFaqUrl(normalized);
      isLoading = true;
      faqSection.classList.add("is-loading");

      fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(response => {
          if (!response.ok) {
            throw new Error("Network error");
          }
          return response.text();
        })
        .then(html => {
          const doc = parser.parseFromString(html, "text/html");
          if (!replaceFaqContent(doc)) {
            throw new Error("Failed to update FAQ content");
          }
          currentTag = normalized;
          if (!opts.skipPush && history && history.pushState) {
            history.pushState({ tag: normalized }, "", url);
          }
          syncHead(doc);
          attachInteractions();
        })
        .catch(() => {
          window.location.href = url;
        })
        .finally(() => {
          isLoading = false;
          faqSection.classList.remove("is-loading");
          openFromHash();
        });
    }

    faqSection.addEventListener("click", function (event) {
      const tagLink = event.target.closest(".faq-tags__link");
      if (tagLink) {
        event.preventDefault();
        const alias = extractAlias(tagLink.getAttribute("href"));
        loadFaq(alias);
        return;
      }

      const chip = event.target.closest(".faq__tag-chip");
      if (chip) {
        event.preventDefault();
        const alias = extractAlias(chip.getAttribute("href"));
        loadFaq(alias);
      }
    });

    window.addEventListener("popstate", function () {
      const alias = new URL(window.location.href).searchParams.get("tag") || "";
      loadFaq(alias, { skipPush: true, force: true });
    });

    window.addEventListener("hashchange", openFromHash);
  });
})();
