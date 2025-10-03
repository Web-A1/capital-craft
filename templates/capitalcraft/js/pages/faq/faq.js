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

    const header = document.querySelector(".site-header");

    function ensureHeaderPinned() {
      if (!header) {
        return;
      }

      header.classList.remove("unpinned");
      header.classList.add("pinned");
    }

    function createHeaderInteractionLock(options) {
      const settings = options || {};
      const releaseDelay =
        Number.isFinite(settings.releaseDelay) && settings.releaseDelay >= 0
          ? settings.releaseDelay
          : 150;

      ensureHeaderPinned();

      let controlInstance = window.headerControl || null;
      let releaseRequested = false;
      let releaseTimer = null;

      const freezeControl = control => {
        if (!control) {
          return;
        }

        control.freeze({ scrollY: window.pageYOffset });
        control.pin({ scrollY: window.pageYOffset });
      };

      const releaseControl = control => {
        if (!control) {
          return;
        }

        control.pin({ scrollY: window.pageYOffset });

        const finalizeRelease = () => {
          control.unfreeze({ scrollY: window.pageYOffset });
        };

        if (releaseDelay > 0) {
          if (releaseTimer) {
            window.clearTimeout(releaseTimer);
          }
          releaseTimer = window.setTimeout(finalizeRelease, releaseDelay);
        } else {
          finalizeRelease();
        }
      };

      const handleReady = event => {
        const detail = event && event.detail;
        const controlFromEvent =
          (detail && detail.control) || window.headerControl;

        if (!controlFromEvent) {
          return;
        }

        controlInstance = controlFromEvent;
        window.removeEventListener("cc:header-control-ready", handleReady);

        if (releaseRequested) {
          releaseControl(controlInstance);
        } else {
          freezeControl(controlInstance);
        }
      };

      if (controlInstance) {
        freezeControl(controlInstance);
      } else {
        window.addEventListener("cc:header-control-ready", handleReady);
      }

      return function releaseHeaderLock() {
        if (releaseRequested) {
          return;
        }

        releaseRequested = true;
        ensureHeaderPinned();

        if (controlInstance) {
          releaseControl(controlInstance);
        }
      };
    }

    const parser = new DOMParser();
    const statusEl = faqSection.querySelector("#faq-filter-status");
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

    function setStatus(message) {
      if (!statusEl) {
        return;
      }
      statusEl.textContent = message || "";
    }

    function pluralize(count) {
      const abs = Math.abs(count) % 100;
      const mod = abs % 10;

      if (abs > 10 && abs < 20) {
        return "вопросов";
      }

      if (mod > 1 && mod < 5) {
        return "вопроса";
      }

      if (mod === 1) {
        return "вопрос";
      }

      return "вопросов";
    }

    function getFilterLabel() {
      const activeLink = faqSection.querySelector(".faq-tags__link.is-active");
      if (activeLink) {
        return activeLink.textContent.trim();
      }
      const activeChip = faqSection.querySelector(".faq__tag-chip");
      if (activeChip) {
        return activeChip.textContent.trim();
      }
      return "Все вопросы";
    }

    function announceUpdate() {
      if (!statusEl) {
        return;
      }
      const itemsCount = faqSection.querySelectorAll(".faq__item").length;
      const filterLabel = getFilterLabel();

      if (itemsCount > 0) {
        setStatus(
          "Показано " +
            itemsCount +
            " " +
            pluralize(itemsCount) +
            ": " +
            filterLabel
        );
      } else {
        setStatus("Нет вопросов для фильтра " + filterLabel);
      }
    }

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
    let accordionElement = faqSection.querySelector(".faq__accordion");
    let collapseOthers = false;
    let resizeHandlerAttached = false;
    let fontsHandlerAttached = false;
    const ANSWER_HEIGHT_BUFFER = 16;

    function updateAccordionMeta() {
      accordionElement = faqSection.querySelector(".faq__accordion");

      if (!accordionElement) {
        collapseOthers = false;
        return;
      }

      const mode = (
        accordionElement.getAttribute("data-accordion-mode") || "multiple"
      )
        .toLowerCase()
        .trim();
      collapseOthers = mode === "single";
      accordionElement.setAttribute(
        "aria-multiselectable",
        collapseOthers ? "false" : "true"
      );
    }
    updateAccordionMeta();

    function getAnswerForQuestion(button) {
      const controlId = button.getAttribute("aria-controls");
      if (controlId) {
        return document.getElementById(controlId);
      }

      const item = button.closest(".faq__item");
      if (item) {
        return item.querySelector(".faq__answer");
      }

      return null;
    }

    function collapseQuestion(button) {
      const answer = getAnswerForQuestion(button);
      button.setAttribute("aria-expanded", "false");
      if (!answer) {
        return;
      }

      const currentHeight = answer.scrollHeight + ANSWER_HEIGHT_BUFFER;
      if (currentHeight > 0) {
        answer.style.maxHeight = currentHeight + "px";
      }

      answer.classList.remove("open");
      answer.setAttribute("aria-hidden", "true");

      void answer.offsetHeight;
      answer.style.maxHeight = "0px";
    }

    function expandQuestion(button) {
      const answer = getAnswerForQuestion(button);
      button.setAttribute("aria-expanded", "true");
      if (!answer) {
        return;
      }
      answer.classList.add("open");
      answer.setAttribute("aria-hidden", "false");

      const targetHeight = answer.scrollHeight + ANSWER_HEIGHT_BUFFER;
      void answer.offsetHeight;
      answer.style.maxHeight = targetHeight + "px";
    }

    function handleQuestionClick(event) {
      const question = event.currentTarget;
      const isExpanded = question.getAttribute("aria-expanded") === "true";

      if (collapseOthers) {
        questionNodes.forEach(btn => {
          if (
            btn !== question &&
            btn.getAttribute("aria-expanded") === "true"
          ) {
            collapseQuestion(btn);
          }
        });
      }
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

    function refreshOpenHeights() {
      questionNodes.forEach(button => {
        if (button.getAttribute("aria-expanded") === "true") {
          const answer = getAnswerForQuestion(button);
          if (answer) {
            const targetHeight = answer.scrollHeight + ANSWER_HEIGHT_BUFFER;
            answer.style.maxHeight = targetHeight + "px";
          }
        }
      });
    }

    function setupQuestions() {
      questionNodes = Array.from(faqSection.querySelectorAll(".faq__question"));
      questionNodes.forEach(question => {
        question.addEventListener("click", handleQuestionClick);
        question.addEventListener("keydown", handleQuestionKey);
      });
    }

    function syncAnswerAria() {
      const answers = faqSection.querySelectorAll(".faq__answer");
      answers.forEach(answer => {
        const item = answer.closest(".faq__item");
        const controller = item
          ? item.querySelector(
              '.faq__question[aria-controls="' + answer.id + '"]'
            )
          : null;
        const expanded =
          controller && controller.getAttribute("aria-expanded") === "true";
        answer.setAttribute("aria-hidden", expanded ? "false" : "true");
        answer.classList.toggle("open", expanded);
        if (expanded) {
          const targetHeight = answer.scrollHeight + ANSWER_HEIGHT_BUFFER;
          answer.style.maxHeight = targetHeight + "px";
        } else {
          answer.style.maxHeight = "0px";
        }
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
      const releaseHeaderLock = createHeaderInteractionLock({
        releaseDelay: 180
      });
      let autoReleaseId = null;

      const question = element.querySelector(".faq__question");
      if (question) {
        const isExpanded = question.getAttribute("aria-expanded") === "true";

        if (!isExpanded) {
          if (collapseOthers) {
            questionNodes.forEach(btn => {
              if (
                btn !== question &&
                btn.getAttribute("aria-expanded") === "true"
              ) {
                collapseQuestion(btn);
              }
            });
          }

          expandQuestion(question);
        }
      }

      ensureHeaderPinned();

      const scheduleFrame =
        typeof window.requestAnimationFrame === "function"
          ? window.requestAnimationFrame.bind(window)
          : callback => window.setTimeout(callback, 0);

      autoReleaseId = window.setTimeout(releaseHeaderLock, 1200);

      scheduleFrame(function () {
        ensureHeaderPinned();

        element.scrollIntoView({ block: "center", behavior: "instant" });

        if (
          typeof window.matchMedia === "function" &&
          window.matchMedia("(max-width: 767px)").matches &&
          header
        ) {
          window.scrollBy(0, -header.offsetHeight);
        }

        ensureHeaderPinned();

        scheduleFrame(function () {
          ensureHeaderPinned();
          if (autoReleaseId !== null) {
            window.clearTimeout(autoReleaseId);
            autoReleaseId = null;
          }
          releaseHeaderLock();
        });
      });
    }

    function attachInteractions() {
      faqSection.classList.add("faq--interactive", "faq--interactive-initial");
      updateAccordionMeta();
      setupQuestions();
      syncAnswerAria();
      openFromHash();

      const finalizeInteractive = function () {
        faqSection.classList.remove("faq--interactive-initial");
      };

      if (typeof window.requestAnimationFrame === "function") {
        window.requestAnimationFrame(finalizeInteractive);
      } else {
        window.setTimeout(finalizeInteractive, 0);
      }

      if (!resizeHandlerAttached) {
        const resizeHandler = function () {
          refreshOpenHeights();
        };
        window.addEventListener("resize", resizeHandler);
        window.addEventListener("orientationchange", resizeHandler);
        resizeHandlerAttached = true;
      }

      if (!fontsHandlerAttached && document.fonts) {
        if (typeof document.fonts.addEventListener === "function") {
          document.fonts.addEventListener("loadingdone", refreshOpenHeights);
        }
        if (
          typeof document.fonts.ready === "object" &&
          typeof document.fonts.ready.then === "function"
        ) {
          document.fonts.ready.then(refreshOpenHeights).catch(() => {});
        }
        fontsHandlerAttached = true;
      }
    }

    attachInteractions();
    announceUpdate();
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
      faqSection.setAttribute("aria-busy", "true");
      setStatus("Загружаем вопросы…");

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
          announceUpdate();
        })
        .catch(() => {
          window.location.href = url;
        })
        .finally(() => {
          isLoading = false;
          faqSection.classList.remove("is-loading");
          faqSection.removeAttribute("aria-busy");
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
