(function () {
  if (window.__capitalcraftBlogFilterInit) {
    return;
  }

  window.__capitalcraftBlogFilterInit = true;

  const scope =
    document.querySelector(".blog") || document.querySelector(".blog-tags");
  if (!scope) {
    return;
  }

  const parser = new DOMParser();
  const cache = new Map();
  const origin = window.location.origin;
  const statusEl = scope.querySelector("#blog-filter-status");
  const header = document.querySelector(".site-header");
  const supportsSmoothScroll =
    !!document.documentElement &&
    "scrollBehavior" in document.documentElement.style;
  const metaSelectors = [
    'meta[name="description"]',
    'meta[property="og:url"]',
    'meta[property="og:title"]',
    'meta[property="og:description"]',
    'meta[name="twitter:title"]',
    'meta[name="twitter:description"]'
  ];

  let currentController = null;
  let isLoading = false;
  let prefetchQueue = new Set();

  function ensureHeaderPinned() {
    if (!header) {
      return;
    }

    header.classList.remove("unpinned");
    header.classList.add("pinned");

    if (
      window.headerControl &&
      typeof window.headerControl.pin === "function"
    ) {
      window.headerControl.pin({ scrollY: window.pageYOffset });
    }
  }

  function scrollBlogToTop() {
    const target = scope.querySelector(".blog__header") || scope;
    if (!target) {
      return;
    }

    ensureHeaderPinned();

    const rect = target.getBoundingClientRect();
    const headerHeight =
      header && typeof header.offsetHeight === "number"
        ? header.offsetHeight
        : 0;
    const top = Math.max(window.pageYOffset + rect.top - headerHeight, 0);

    const scrollOptions = { top: top };
    if (supportsSmoothScroll) {
      scrollOptions.behavior = "smooth";
    }

    try {
      window.scrollTo(scrollOptions);
    } catch (err) {
      window.scrollTo(0, top);
    }

    window.setTimeout(ensureHeaderPinned, 200);
  }

  function setStatus(message) {
    if (statusEl) {
      statusEl.textContent = message || "";
    }
  }

  function prepareUrl(url, options) {
    const prepared = new URL(url, origin);
    if (!options || !options.preserveLimitstart) {
      prepared.searchParams.delete("limitstart");
    }
    prepared.hash = "";

    return prepared.toString();
  }

  function startLoading() {
    isLoading = true;
    scope.classList.add("is-loading");
    scope.setAttribute("aria-busy", "true");
    setStatus("Загружаем материалы…");
  }

  function finishLoading() {
    scope.classList.remove("is-loading");
    scope.removeAttribute("aria-busy");
    isLoading = false;
  }

  function pluralize(count) {
    const abs = Math.abs(count) % 100;
    const mod = abs % 10;

    if (abs > 10 && abs < 20) {
      return "материалов";
    }

    if (mod > 1 && mod < 5) {
      return "материала";
    }

    if (mod === 1) {
      return "материал";
    }

    return "материалов";
  }

  function syncHead(entry) {
    if (entry.title) {
      document.title = entry.title;
    }

    entry.metas.forEach(function (metaItem) {
      const node = document.querySelector(metaItem.selector);
      if (node) {
        node.setAttribute("content", metaItem.content || "");
      }
    });

    if (entry.canonical) {
      const canon = document.querySelector('link[rel="canonical"]');
      if (canon) {
        canon.setAttribute("href", entry.canonical);
      }
    }
  }

  function ensurePagination(entry) {
    let pagination = scope.querySelector(".blog-pagination");
    const label = entry.paginationLabel || "Пагинация блога";

    if (entry.hasPagination) {
      if (!pagination) {
        pagination = document.createElement("nav");
        pagination.className = "blog-pagination";
        pagination.setAttribute("aria-label", label);
        const container = scope.querySelector(".container") || scope;
        const anchor = scope.querySelector(".blog-list");
        if (container && anchor) {
          container.appendChild(pagination);
        } else {
          scope.appendChild(pagination);
        }
      }

      pagination.setAttribute("aria-label", label);
      pagination.innerHTML = entry.paginationInner || "";
    } else if (pagination) {
      pagination.remove();
    }
  }

  function updateStatus(entry) {
    if (!statusEl) {
      return;
    }

    const activeLink = scope.querySelector(
      ".blog__tags-nav .blog-tags__link.is-active"
    );
    const filterLabel = activeLink
      ? activeLink.textContent.trim()
      : entry.statusLabel || "Все статьи";

    if (entry.cardCount > 0) {
      setStatus(
        "Показано " +
          entry.cardCount +
          " " +
          pluralize(entry.cardCount) +
          ": " +
          filterLabel
      );
    } else {
      setStatus("Нет материалов для фильтра " + filterLabel);
    }
  }

  function applyContent(entry) {
    if (entry.listInner === null) {
      window.location.href = entry.url;
      return;
    }

    const nav = scope.querySelector(".blog__tags-nav");
    if (nav && entry.navInner !== null) {
      nav.innerHTML = entry.navInner;
    }

    const list = scope.querySelector(".blog-list");
    if (list && entry.listInner !== null) {
      list.innerHTML = entry.listInner;
    }

    ensurePagination(entry);
    syncHead(entry);
    updateStatus(entry);
    scrollBlogToTop();
  }

  function extractContent(doc, url) {
    const entry = {
      url: url,
      title: "",
      metas: [],
      canonical: null,
      navInner: null,
      listInner: null,
      paginationInner: null,
      hasPagination: false,
      statusLabel: "",
      cardCount: 0,
      paginationLabel: "Пагинация блога"
    };

    const titleNode = doc.querySelector("title");
    entry.title = titleNode ? titleNode.textContent : "";

    const navNode = doc.querySelector(".blog__tags-nav");
    entry.navInner = navNode ? navNode.innerHTML : null;

    const listNode = doc.querySelector(".blog-list");
    entry.listInner = listNode ? listNode.innerHTML : null;
    entry.cardCount = listNode
      ? listNode.querySelectorAll(".blog-card").length
      : 0;

    const paginationNode = doc.querySelector(".blog-pagination");
    entry.hasPagination = Boolean(paginationNode);
    entry.paginationInner = paginationNode ? paginationNode.innerHTML : null;
    if (paginationNode) {
      entry.paginationLabel =
        paginationNode.getAttribute("aria-label") || entry.paginationLabel;
    }

    const statusSource =
      doc.querySelector(".blog__tags-nav .blog-tags__link.is-active") ||
      doc.querySelector("#tag-title") ||
      doc.querySelector("#blog-title");
    entry.statusLabel = statusSource ? statusSource.textContent.trim() : "";

    entry.metas = metaSelectors
      .map(function (selector) {
        const node = doc.querySelector(selector);
        return node
          ? { selector: selector, content: node.getAttribute("content") || "" }
          : null;
      })
      .filter(function (item) {
        return item !== null;
      });

    const canonicalNode = doc.querySelector('link[rel="canonical"]');
    entry.canonical = canonicalNode ? canonicalNode.getAttribute("href") : null;

    return entry;
  }

  function updateHistory(url, mode) {
    if (!history || !history.pushState) {
      return;
    }

    const state = { url: url };

    if (mode === "replace") {
      history.replaceState(state, "", url);
    } else if (mode !== "skip") {
      history.pushState(state, "", url);
    }
  }

  function loadFromCache(url, options) {
    const entry = cache.get(url);
    if (!entry) {
      return false;
    }

    applyContent(entry);

    if (!options || !options.skipPush) {
      updateHistory(url, options && options.replace ? "replace" : "push");
    }

    return true;
  }

  function prefetch(url) {
    try {
      const finalUrl = prepareUrl(url, { preserveLimitstart: false });
      if (cache.has(finalUrl) || prefetchQueue.has(finalUrl)) {
        return;
      }
      prefetchQueue.add(finalUrl);
      fetch(finalUrl, { headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(r => (r.ok ? r.text() : Promise.reject()))
        .then(html => {
          const doc = parser.parseFromString(html, "text/html");
          const entry = extractContent(doc, finalUrl);
          cache.set(finalUrl, entry);
        })
        .catch(() => {})
        .finally(() => prefetchQueue.delete(finalUrl));
    } catch (e) {
      // noop
    }
  }

  function loadPage(url, options) {
    if (currentController) {
      currentController.abort();
      currentController = null;
      if (isLoading) {
        finishLoading();
      }
    }

    const finalUrl = prepareUrl(url, {
      preserveLimitstart: !!(options && options.preserveLimitstart)
    });

    if (!options || !options.force) {
      const served = loadFromCache(finalUrl, options);
      if (served) {
        return;
      }
    }

    startLoading();

    const controller = new AbortController();
    currentController = controller;

    fetch(finalUrl, {
      headers: { "X-Requested-With": "XMLHttpRequest" },
      signal: controller.signal
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Network error");
        }
        return response.text();
      })
      .then(function (html) {
        const doc = parser.parseFromString(html, "text/html");
        const entry = extractContent(doc, finalUrl);
        cache.set(finalUrl, entry);
        applyContent(entry);
        if (!options || !options.skipPush) {
          updateHistory(
            finalUrl,
            options && options.replace ? "replace" : "push"
          );
        }
      })
      .catch(function (error) {
        if (error.name === "AbortError") {
          return;
        }
        window.location.href = finalUrl;
      })
      .finally(function () {
        if (currentController === controller) {
          currentController = null;
          finishLoading();
        }
      });
  }

  function handleClick(event) {
    const navLink = event.target.closest(".blog__tags-nav .blog-tags__link");
    if (navLink && navLink.href) {
      event.preventDefault();
      loadPage(navLink.href, { preserveLimitstart: false });
      return;
    }

    const otherTagLink = event.target.closest(
      ".blog-tags__others .blog-tags__link"
    );
    if (otherTagLink && otherTagLink.href) {
      event.preventDefault();
      loadPage(otherTagLink.href, { preserveLimitstart: false });
      return;
    }

    const tagLink = event.target.closest(".blog-card__tag-link");
    if (tagLink && tagLink.href) {
      event.preventDefault();
      loadPage(tagLink.href, { preserveLimitstart: false });
      return;
    }

    const paginationLink = event.target.closest(".blog-pagination a");
    if (paginationLink && paginationLink.href) {
      event.preventDefault();
      loadPage(paginationLink.href, { preserveLimitstart: true });
      return;
    }

    const card = event.target.closest(".blog-card");
    if (card && !event.target.closest("a")) {
      const href = card.getAttribute("data-href");
      if (href) {
        window.location.href = href;
      }
    }
  }

  function handleHover(event) {
    const navLink = event.target.closest(
      ".blog__tags-nav .blog-tags__link, .blog-tags__others .blog-tags__link, .blog-card__tag-link"
    );
    if (navLink && navLink.href) {
      prefetch(navLink.href);
    }
  }

  function handlePopstate(event) {
    const targetUrl = (event.state && event.state.url) || window.location.href;
    loadPage(targetUrl, { skipPush: true, preserveLimitstart: true });
  }

  function primeCache() {
    const initialUrl = prepareUrl(window.location.href, {
      preserveLimitstart: true
    });
    const entry = extractContent(document, initialUrl);
    cache.set(initialUrl, entry);
    updateStatus(entry);
    updateHistory(initialUrl, "replace");

    const tagLinks = Array.from(
      document.querySelectorAll(".blog__tags-nav .blog-tags__link")
    ).filter(a => a && a.href);
    const activeIndex = tagLinks.findIndex(a =>
      a.classList.contains("is-active")
    );
    const candidates = [];
    if (activeIndex >= 0) {
      for (let i = 1; i <= 3; i++) {
        const next = tagLinks[activeIndex + i];
        if (next) candidates.push(next);
      }
    }
    candidates.forEach(a => prefetch(a.href));
  }

  primeCache();
  document.addEventListener("click", handleClick);
  document.addEventListener("mouseover", handleHover, { passive: true });
  window.addEventListener("popstate", handlePopstate);
})();
