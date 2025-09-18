<?php
defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;

require_once __DIR__ . "/../../../helpers/TagFilterHelper.php";

/** @var \Joomla\Component\Tags\Site\View\Tag\HtmlView $this */
$htag = $this->params->get("show_page_heading") ? "h2" : "h1";
?>

<section class="frame section-with-divider blog-tags" aria-labelledby="tag-title">
  <div class="container">

    <header class="blog__header">
      <div class="blog__subtitle" id="tag-subtitle">Статьи по тегу</div>
      <<?php echo $htag; ?> class="blog__title" id="tag-title">
        #<?php echo HTMLHelper::_("content.prepare", $this->tags_title, "", "com_tags.tag"); ?>
      </<?php echo $htag; ?>>
    </header>

    <?php
    $app = Factory::getApplication();
$menu = $app->getMenu();
$blogMenu = $menu->getItems("alias", "blog", true);
$blogCategoryId = 0;

if ($blogMenu && isset($blogMenu->query["id"])) {
    $blogCategoryId = (int) $blogMenu->query["id"];
}

$includeSub = false;
$maxLevels = 0;

if ($blogMenu && method_exists($blogMenu, "getParams")) {
    $menuParams = $blogMenu->getParams();
    if ($menuParams) {
        $includeSub = (bool) $menuParams->get("show_subcategory_content", "0");
        $maxLevels = $includeSub ? (int) $menuParams->get("show_subcategory_content", "1") : 0;
    }
}

if ($blogCategoryId <= 0 && $blogMenu) {
    $blogCategoryId = isset($blogMenu->query["catid"]) ? (int) $blogMenu->query["catid"] : 0;
}

$availableTags = [];

if ($blogCategoryId > 0) {
    $availableTags = CapitalcraftTagFilterHelper::getBlogTags($blogCategoryId, [
        "includeSubcategories" => $includeSub,
        "maxSubLevels" => $maxLevels,
    ]);
} else {
    $availableTags = CapitalcraftTagFilterHelper::getAllTags();
}

$currentTagId = 0;
$currentTagAlias = "";

if (!empty($this->item) && \is_array($this->item)) {
    $firstTag = $this->item[0] ?? null;
    if ($firstTag) {
        $currentTagId = (int) ($firstTag->id ?? 0);
        $currentTagAlias = strtolower($firstTag->alias ?? "");
    }
}

if (!$currentTagId) {
    $rawId = $app->input->getString("id", "");
    if ($rawId !== "") {
        $parts = explode(":", $rawId, 2);
        $currentTagId = (int) ($parts[0] ?? 0);
        if (!empty($parts[1])) {
            $currentTagAlias = strtolower($parts[1]);
        }
    }
}

if ($currentTagId && $currentTagAlias === "") {
    $db = Factory::getDbo();
    $user = Factory::getUser();
    $levels = array_map("intval", $user->getAuthorisedViewLevels());
    if (empty($levels)) {
        $levels = [0];
    }
    $languageTag = $app->getLanguage()->getTag() ?: "*";
    $languageCondition =
        $languageTag === "*"
            ? "1=1"
            : $db->quoteName("t.language") . " IN (" . $db->quote("*") . "," . $db->quote($languageTag) . ")";
    $tagQuery = $db
        ->getQuery(true)
        ->select($db->quoteName("alias"))
        ->from($db->quoteName("#__tags", "t"))
        ->where($db->quoteName("t.id") . " = " . (int) $currentTagId)
        ->where($db->quoteName("t.published") . " = 1")
        ->where("t.access IN (" . implode(",", $levels) . ")")
        ->where($languageCondition)
        ->setLimit(1);
    $db->setQuery($tagQuery);
    $aliasFromDb = (string) $db->loadResult();
    if ($aliasFromDb !== "") {
        $currentTagAlias = strtolower($aliasFromDb);
    }
}
?>

    <?php if (!empty($availableTags)): ?>
      <nav class="blog__tags-nav" aria-label="Навигация по тегам">
        <ul class="blog-tags__cloud blog-tags__cloud--nowrap">
          <?php
      $blogRoute = null;
        if ($blogCategoryId > 0) {
            $blogRoute = Route::_(ContentRouteHelper::getCategoryRoute($blogCategoryId));
        } elseif ($blogMenu) {
            $blogRoute = Route::_("index.php?Itemid=" . (int) $blogMenu->id);
        }
if ($blogRoute): ?>
            <li class="blog-tags__tag">
              <a
                class="blog-tags__link<?php echo $currentTagAlias === "" ? " is-active" : ""; ?>"
                href="<?php echo $blogRoute; ?>"
                data-tag-alias=""
                <?php echo $currentTagAlias === "" ? 'aria-current="page"' : ""; ?>
              >Все статьи</a>
            </li>
          <?php endif;
?>
          <?php foreach ($availableTags as $tagOption): ?>
            <?php
  $tagAliasLower = strtolower($tagOption->alias ?? "");
              $isActive = $currentTagAlias === $tagAliasLower;
              $tagRoute = Route::_(TagsRouteHelper::getTagRoute((int) $tagOption->id . ":" . ($tagOption->alias ?? "")));
              ?>
            <li class="blog-tags__tag">
              <a
                class="blog-tags__link<?php echo $isActive ? " is-active" : ""; ?>"
                href="<?php echo $tagRoute; ?>"
                data-tag-alias="<?php echo htmlspecialchars($tagAliasLower, ENT_QUOTES, "UTF-8"); ?>"
                <?php echo $isActive ? 'aria-current="page"' : ""; ?>
              >#<?php echo htmlspecialchars($tagOption->title, ENT_QUOTES, "UTF-8"); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <div class="visually-hidden" aria-live="polite" aria-atomic="true" id="blog-filter-status"></div>
    <?php endif; ?>

    <?php echo $this->loadTemplate("items"); ?>

    <?php if (
        ($this->params->def("show_pagination", 1) == 1 || $this->params->get("show_pagination") == 2) &&
        $this->pagination->pagesTotal > 1
    ): ?>
      <nav class="blog-pagination" aria-label="Пагинация по тегу">
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
    <?php endif; ?>

    <?php
    $otherTags = [];
if ($currentTagId) {
    $otherTags = CapitalcraftTagFilterHelper::getAllTags(["excludeTagId" => $currentTagId]);
} else {
    $otherTags = CapitalcraftTagFilterHelper::getAllTags();
}
?>

    <?php if (!empty($otherTags)): ?>
      <section class="blog-tags__others">
        <div class="blog__subtitle">Другие теги</div>
        <ul class="blog-tags__cloud">
          <?php foreach ($otherTags as $tg): ?>
            <?php $tagRoute = Route::_(TagsRouteHelper::getTagRoute((int) $tg->id . ':' . ($tg->alias ?? ''))); ?>
            <li class="blog-tags__tag">
              <a class="blog-tags__link" href="<?php echo $tagRoute; ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, 'UTF-8'); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
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
    }

    primeCache();
    document.addEventListener('click', handleClick);
    window.addEventListener('popstate', handlePopstate);
  })();
</script>
