<?php
defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;

require_once __DIR__ . "/../../../helpers/TagFilterHelper.php";

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */

// Prepare category description plugins like core does
$this->category->text = $this->category->description;
$app = Factory::getApplication();
$app->triggerEvent("onContentPrepare", [
    $this->category->extension . ".categories",
    &$this->category,
    &$this->params,
    0,
]);
$this->category->description = $this->category->text;

$htag = $this->params->get("show_page_heading") ? "h2" : "h1";

$app = Factory::getApplication();
$db = Factory::getDbo();
$input = $app->input;
$tagParamRaw = trim($input->getString("tag", ""));
$normalizedTagAlias = "";
$selectedTagId = 0;

if ($tagParamRaw !== "") {
    $db = Factory::getDbo();
    $user = Factory::getUser();
    $viewLevels = array_map("intval", $user->getAuthorisedViewLevels());
    if (empty($viewLevels)) {
        $viewLevels = [0];
    }
    $levelsCondition = "t.access IN (" . implode(",", $viewLevels) . ")";
    $languageTag = Factory::getApplication()->getLanguage()->getTag() ?: "*";
    $languageCondition =
        $languageTag === "*"
            ? "1=1"
            : $db->quoteName("t.language") . " IN (" . $db->quote("*") . "," . $db->quote($languageTag) . ")";

    $tagQuery = $db
        ->getQuery(true)
        ->select($db->quoteName(["id", "alias"]))
        ->from($db->quoteName("#__tags", "t"))
        ->where($db->quoteName("t.published") . " = 1")
        ->where($levelsCondition)
        ->where($languageCondition)
        ->setLimit(1);

    if (ctype_digit($tagParamRaw)) {
        $tagQuery->where($db->quoteName("t.id") . " = " . (int) $tagParamRaw);
    } else {
        $tagQuery->where($db->quoteName("t.alias") . " = " . $db->quote($tagParamRaw));
    }

    $db->setQuery($tagQuery);
    $tagRow = $db->loadObject();

    if ($tagRow && !empty($tagRow->alias)) {
        $selectedTagId = (int) $tagRow->id;
        $normalizedTagAlias = strtolower((string) $tagRow->alias);
    }
} else {
    $normalizedTagAlias = "";
}

$rawLeadItems = $this->lead_items ?? [];
$rawIntroItems = $this->intro_items ?? [];

$matchesTag = function ($item) use ($normalizedTagAlias) {
    if ($normalizedTagAlias === "") {
        return true;
    }

    if (empty($item->tags->itemTags)) {
        return false;
    }

    foreach ($item->tags->itemTags as $tag) {
        $alias = strtolower($tag->alias ?? "");
        if ($alias === $normalizedTagAlias) {
            return true;
        }
    }

    return false;
};

if ($normalizedTagAlias !== "") {
    $filteredLead = array_values(array_filter($rawLeadItems, $matchesTag));
    $filteredIntro = array_values(array_filter($rawIntroItems, $matchesTag));

    $this->lead_items = $filteredLead;
    $this->intro_items = $filteredIntro;
    $this->link_items = [];
} else {
    $this->lead_items = $rawLeadItems;
    $this->intro_items = $rawIntroItems;
}
?>

<section class="frame section-with-divider blog" aria-labelledby="blog-title">
  <div class="container">

    <header class="blog__header">
      <h1 class="blog__subtitle" id="blog-subtitle">
        экспертные статьи и новости рынка финансов
      </h1>

      <p class="blog__title" id="blog-title">
        Практика привлечения капитала и ключевые события рынка
      </p>

      <?php if ($this->params->get("show_cat_tags", 0) && !empty($this->category->tags->itemTags)): ?>
        <?php $this->category->tagLayout = new FileLayout("joomla.content.tags"); ?>
        <div class="blog__category-tags">
          <?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
        </div>
      <?php endif; ?>

      <?php if ($this->params->get("show_description", 0) && $this->category->description): ?>
        <div class="blog__description">
          <?php echo HTMLHelper::_("content.prepare", $this->category->description, "", "com_content.category"); ?>
        </div>
      <?php endif; ?>
    </header>

    <?php
    // Build navigation of all available tags
    $includeSub = (bool) $this->params->get("show_subcategory_content", "0");
$maxLevels = $includeSub ? (int) $this->params->get("show_subcategory_content", "1") : 0;
$allTags = CapitalcraftTagFilterHelper::getBlogTags((int) $this->category->id, [
    "includeSubcategories" => $includeSub,
    "maxSubLevels" => $maxLevels,
]);
?>

    <?php if (!empty($allTags)): ?>
      <nav class="blog__tags-nav" aria-label="Навигация по тегам">
        <ul class="blog-tags__cloud blog-tags__cloud--nowrap">
          <?php $blogRoute = Route::_(ContentRouteHelper::getCategoryRoute($this->category->id)); ?>
          <li class="blog-tags__tag">
            <a
              class="blog-tags__link<?php echo $normalizedTagAlias === "" ? " is-active" : ""; ?>"
              href="<?php echo $blogRoute; ?>"
              data-tag-alias=""
              <?php echo $normalizedTagAlias === "" ? 'aria-current="page"' : ""; ?>
            >Все статьи</a>
          </li>
          <?php foreach ($allTags as $tg): ?>
            <?php
        $tagAliasLower = strtolower($tg->alias ?? "");
              $isActiveTag = $normalizedTagAlias === $tagAliasLower;
            // Build link to blog with tag parameter
            $tagRouteBase = $blogRoute;
            $sep = (strpos($tagRouteBase, '?') === false) ? '?' : '&';
            $tagRoute = $tagRouteBase . $sep . 'tag=' . rawurlencode($tg->alias ?? '');
              ?>
            <li class="blog-tags__tag">
              <a
                class="blog-tags__link<?php echo $isActiveTag ? " is-active" : ""; ?>"
                href="<?php echo $tagRoute; ?>"
                data-tag-alias="<?php echo htmlspecialchars($tagAliasLower, ENT_QUOTES, "UTF-8"); ?>"
                <?php echo $isActiveTag ? 'aria-current="page"' : ""; ?>
              >#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, "UTF-8"); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <div class="visually-hidden" aria-live="polite" aria-atomic="true" id="blog-filter-status"></div>
    <?php endif; ?>

    <?php if (empty($this->lead_items) && empty($this->intro_items) && empty($this->link_items)): ?>
      <?php if ($this->params->get("show_no_articles", 1)): ?>
        <div class="alert alert-info"><?php echo Text::_("COM_CONTENT_NO_ARTICLES"); ?></div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="blog-list">
      <?php if (!empty($this->lead_items)): ?>
        <?php foreach ($this->lead_items as &$item): ?>
          <?php $cardLink = Route::_(
              ContentRouteHelper::getArticleRoute($item->slug, $item->catid, $item->language),
          ); ?>
          <?php
          $aliases = [];
            if (!empty($item->tags->itemTags)) {
                foreach ($item->tags->itemTags as $tg) {
                    if (!empty($tg->alias)) {
                        $aliases[] = strtolower($tg->alias);
                    }
                }
            }
            ?>
          <article class="blog-card blog-card--lead" data-tags="<?php echo htmlspecialchars(
              implode(" ", $aliases),
              ENT_QUOTES,
              "UTF-8",
          ); ?>" data-href="<?php echo $cardLink; ?>">
            <?php
          $this->item = &$item;
            echo $this->loadTemplate("item");
            ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($this->intro_items)): ?>
        <?php foreach ($this->intro_items as &$item): ?>
          <?php $cardLink = Route::_(
              ContentRouteHelper::getArticleRoute($item->slug, $item->catid, $item->language),
          ); ?>
          <?php
          $aliases = [];
            if (!empty($item->tags->itemTags)) {
                foreach ($item->tags->itemTags as $tg) {
                    if (!empty($tg->alias)) {
                        $aliases[] = strtolower($tg->alias);
                    }
                }
            }
            ?>
          <article class="blog-card" data-tags="<?php echo htmlspecialchars(
              implode(" ", $aliases),
              ENT_QUOTES,
              "UTF-8",
          ); ?>" data-href="<?php echo $cardLink; ?>">
            <?php
          $this->item = &$item;
            echo $this->loadTemplate("item");
            ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php
/* link_items преднамеренно не рендерим, чтобы не дублировать записи на след. страницах */
?>

    <?php if (
        ($this->params->def("show_pagination", 1) == 1 || $this->params->get("show_pagination") == 2) &&
        $this->pagination->pagesTotal > 1
    ): ?>
      <nav class="blog-pagination" aria-label="Пагинация блога">
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
    <?php endif; ?>

  </div>
</section>

<script>
  (function () {
    if (window.__capitalcraftBlogFilterInit) {
      return;
    }

    window.__capitalcraftBlogFilterInit = true;

    const scope = document.querySelector('.blog') || document.querySelector('.blog-tags');
    if (!scope) {
      return;
    }

    const parser = new DOMParser();
    const cache = new Map();
    const origin = window.location.origin;
    const statusEl = scope.querySelector('#blog-filter-status');
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

    function setStatus(message) {
      if (statusEl) {
        statusEl.textContent = message || '';
      }
    }

    function prepareUrl(url, options) {
      const prepared = new URL(url, origin);
      if (!options || !options.preserveLimitstart) {
        prepared.searchParams.delete('limitstart');
      }
      prepared.hash = '';

      return prepared.toString();
    }

    function startLoading() {
      isLoading = true;
      scope.classList.add('is-loading');
      scope.setAttribute('aria-busy', 'true');
      setStatus('Загружаем материалы…');
    }

    function finishLoading() {
      scope.classList.remove('is-loading');
      scope.removeAttribute('aria-busy');
      isLoading = false;
    }

    function pluralize(count) {
      const abs = Math.abs(count) % 100;
      const mod = abs % 10;

      if (abs > 10 && abs < 20) {
        return 'материалов';
      }

      if (mod > 1 && mod < 5) {
        return 'материала';
      }

      if (mod === 1) {
        return 'материал';
      }

      return 'материалов';
    }

    function syncHead(entry) {
      if (entry.title) {
        document.title = entry.title;
      }

      entry.metas.forEach(function (metaItem) {
        const node = document.querySelector(metaItem.selector);
        if (node) {
          node.setAttribute('content', metaItem.content || '');
        }
      });

      if (entry.canonical) {
        const canon = document.querySelector('link[rel="canonical"]');
        if (canon) {
          canon.setAttribute('href', entry.canonical);
        }
      }
    }

    function ensurePagination(entry) {
      let pagination = scope.querySelector('.blog-pagination');
      const label = entry.paginationLabel || 'Пагинация блога';

      if (entry.hasPagination) {
        if (!pagination) {
          pagination = document.createElement('nav');
          pagination.className = 'blog-pagination';
          pagination.setAttribute('aria-label', label);
          const container = scope.querySelector('.container') || scope;
          const anchor = scope.querySelector('.blog-list');
          if (container && anchor) {
            container.appendChild(pagination);
          } else {
            scope.appendChild(pagination);
          }
        }

        pagination.setAttribute('aria-label', label);
        pagination.innerHTML = entry.paginationInner || '';
      } else if (pagination) {
        pagination.remove();
      }
    }

    function updateStatus(entry) {
      if (!statusEl) {
        return;
      }

      const activeLink = scope.querySelector('.blog__tags-nav .blog-tags__link.is-active');
      const filterLabel = activeLink ? activeLink.textContent.trim() : (entry.statusLabel || 'Все статьи');

      if (entry.cardCount > 0) {
        setStatus('Показано ' + entry.cardCount + ' ' + pluralize(entry.cardCount) + ': ' + filterLabel);
      } else {
        setStatus('Нет материалов для фильтра ' + filterLabel);
      }
    }

    function applyContent(entry) {
      if (entry.listInner === null) {
        window.location.href = entry.url;
        return;
      }

      const nav = scope.querySelector('.blog__tags-nav');
      if (nav && entry.navInner !== null) {
        nav.innerHTML = entry.navInner;
      }

      const list = scope.querySelector('.blog-list');
      if (list && entry.listInner !== null) {
        list.innerHTML = entry.listInner;
      }

      ensurePagination(entry);
      syncHead(entry);
      updateStatus(entry);
    }

    function extractContent(doc, url) {
      const entry = {
        url: url,
        title: '',
        metas: [],
        canonical: null,
        navInner: null,
        listInner: null,
        paginationInner: null,
        hasPagination: false,
        statusLabel: '',
        cardCount: 0,
        paginationLabel: 'Пагинация блога'
      };

      const titleNode = doc.querySelector('title');
      entry.title = titleNode ? titleNode.textContent : '';

      const navNode = doc.querySelector('.blog__tags-nav');
      entry.navInner = navNode ? navNode.innerHTML : null;

      const listNode = doc.querySelector('.blog-list');
      entry.listInner = listNode ? listNode.innerHTML : null;
      entry.cardCount = listNode ? listNode.querySelectorAll('.blog-card').length : 0;

      const paginationNode = doc.querySelector('.blog-pagination');
      entry.hasPagination = Boolean(paginationNode);
      entry.paginationInner = paginationNode ? paginationNode.innerHTML : null;
      if (paginationNode) {
        entry.paginationLabel = paginationNode.getAttribute('aria-label') || entry.paginationLabel;
      }

      const statusSource =
        doc.querySelector('.blog__tags-nav .blog-tags__link.is-active') ||
        doc.querySelector('#tag-title') ||
        doc.querySelector('#blog-title');
      entry.statusLabel = statusSource ? statusSource.textContent.trim() : '';

      entry.metas = metaSelectors
        .map(function (selector) {
          const node = doc.querySelector(selector);
          return node ? { selector: selector, content: node.getAttribute('content') || '' } : null;
        })
        .filter(function (item) {
          return item !== null;
        });

      const canonicalNode = doc.querySelector('link[rel="canonical"]');
      entry.canonical = canonicalNode ? canonicalNode.getAttribute('href') : null;

      return entry;
    }

    function updateHistory(url, mode) {
      if (!history || !history.pushState) {
        return;
      }

      const state = { url: url };

      if (mode === 'replace') {
        history.replaceState(state, '', url);
      } else if (mode !== 'skip') {
        history.pushState(state, '', url);
      }
    }

    function loadFromCache(url, options) {
      const entry = cache.get(url);
      if (!entry) {
        return false;
      }

      applyContent(entry);

      if (!options || !options.skipPush) {
        updateHistory(url, options && options.replace ? 'replace' : 'push');
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
        fetch(finalUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(r => (r.ok ? r.text() : Promise.reject()))
          .then(html => {
            const doc = parser.parseFromString(html, 'text/html');
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

      const finalUrl = prepareUrl(url, { preserveLimitstart: !!(options && options.preserveLimitstart) });

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
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: controller.signal
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Network error');
          }

          return response.text();
        })
        .then(function (html) {
          const doc = parser.parseFromString(html, 'text/html');
          const entry = extractContent(doc, finalUrl);
          cache.set(finalUrl, entry);
          applyContent(entry);

          if (!options || !options.skipPush) {
            updateHistory(finalUrl, options && options.replace ? 'replace' : 'push');
          }
        })
        .catch(function (error) {
          if (error.name === 'AbortError') {
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
      const navLink = event.target.closest('.blog__tags-nav .blog-tags__link');
      if (navLink && navLink.href) {
        event.preventDefault();
        loadPage(navLink.href, { preserveLimitstart: false });
        return;
      }

      const otherTagLink = event.target.closest('.blog-tags__others .blog-tags__link');
      if (otherTagLink && otherTagLink.href) {
        event.preventDefault();
        loadPage(otherTagLink.href, { preserveLimitstart: false });
        return;
      }

      const tagLink = event.target.closest('.blog-card__tag-link');
      if (tagLink && tagLink.href) {
        event.preventDefault();
        loadPage(tagLink.href, { preserveLimitstart: false });
        return;
      }

      const paginationLink = event.target.closest('.blog-pagination a');
      if (paginationLink && paginationLink.href) {
        event.preventDefault();
        loadPage(paginationLink.href, { preserveLimitstart: true });
        return;
      }

      const card = event.target.closest('.blog-card');
      if (card && !event.target.closest('a')) {
        const href = card.getAttribute('data-href');
        if (href) {
          window.location.href = href;
        }
      }
    }

    function handleHover(event) {
      const navLink = event.target.closest('.blog__tags-nav .blog-tags__link, .blog-tags__others .blog-tags__link, .blog-card__tag-link');
      if (navLink && navLink.href) {
        prefetch(navLink.href);
      }
    }

    function handlePopstate(event) {
      const targetUrl = (event.state && event.state.url) || window.location.href;
      loadPage(targetUrl, { skipPush: true, preserveLimitstart: true });
    }

    function primeCache() {
      const initialUrl = prepareUrl(window.location.href, { preserveLimitstart: true });
      const entry = extractContent(document, initialUrl);
      cache.set(initialUrl, entry);
      updateStatus(entry);
      updateHistory(initialUrl, 'replace');

      // Prefetch next 3 tag links for instant switching
      const tagLinks = Array.from(document.querySelectorAll('.blog__tags-nav .blog-tags__link'))
        .filter(a => a && a.href);
      const activeIndex = tagLinks.findIndex(a => a.classList.contains('is-active'));
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
    document.addEventListener('click', handleClick);
    document.addEventListener('mouseover', handleHover, { passive: true });
    window.addEventListener('popstate', handlePopstate);
  })();
</script>
