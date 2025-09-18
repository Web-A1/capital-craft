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
            $tagRoute = Route::_(TagsRouteHelper::getTagRoute((int) $tg->id . ":" . ($tg->alias ?? "")));
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
          ); ?>" data-href="<?php echo $cardLink; ?>" role="link" tabindex="0">
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
          ); ?>" data-href="<?php echo $cardLink; ?>" role="link" tabindex="0">
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
    const blogSection = document.querySelector('.blog');
    if (!blogSection) {
      return;
    }

    const parser = new DOMParser();
    let isLoading = false;
    let currentTag = (new URL(window.location.href)).searchParams.get('tag') || '';
    currentTag = currentTag.toLowerCase();

    function syncHead(doc) {
      const titleEl = doc.querySelector('title');
      if (titleEl) {
        document.title = titleEl.textContent;
      }

      const selectors = [
        'meta[name="description"]',
        'meta[property="og:url"]',
        'meta[property="og:title"]',
        'meta[property="og:description"]',
        'meta[name="twitter:title"]',
        'meta[name="twitter:description"]',
      ];

      selectors.forEach((selector) => {
        const fresh = doc.querySelector(selector);
        const current = document.querySelector(selector);
        if (fresh && current) {
          current.setAttribute('content', fresh.getAttribute('content') || '');
        }
      });

      const freshCanonical = doc.querySelector('link[rel="canonical"]');
      const canonical = document.querySelector('link[rel="canonical"]');
      if (freshCanonical && canonical) {
        canonical.href = freshCanonical.href;
      }
    }

    function replaceContent(doc) {
      const newNav = doc.querySelector('.blog__tags-nav');
      const nav = blogSection.querySelector('.blog__tags-nav');
      if (newNav && nav) {
        nav.innerHTML = newNav.innerHTML;
      }

      const newList = doc.querySelector('.blog-list');
      const list = blogSection.querySelector('.blog-list');
      if (newList && list) {
        list.innerHTML = newList.innerHTML;
      }

      const newPagination = doc.querySelector('.blog-pagination');
      const pagination = blogSection.querySelector('.blog-pagination');
      if (pagination) {
        if (newPagination) {
          pagination.innerHTML = newPagination.innerHTML;
        } else {
          pagination.remove();
        }
      } else if (newPagination) {
        const container = blogSection.querySelector('.container');
        if (container) {
          container.appendChild(newPagination);
        }
      }
    }

    function loadBlog(alias, options) {
      const opts = options || {};
      const normalized = (alias || '').toLowerCase();
      if (!opts.force && normalized === currentTag) {
        return;
      }
      if (isLoading) {
        return;
      }

      const url = normalized ? `/blog?tag=${encodeURIComponent(normalized)}` : '/blog';
      isLoading = true;
      blogSection.classList.add('is-loading');

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Network error');
          }
          return response.text();
        })
        .then((html) => {
          const doc = parser.parseFromString(html, 'text/html');
          replaceContent(doc);
          currentTag = normalized;
          if (!opts.skipPush && history && history.pushState) {
            history.pushState({ tag: normalized }, '', url);
          }
          syncHead(doc);
        })
        .catch(() => {
          window.location.href = url;
        })
        .finally(() => {
          isLoading = false;
          blogSection.classList.remove('is-loading');
        });
    }

    document.addEventListener('click', function (event) {
      const pill = event.target.closest('.blog-tags__link');
      if (pill) {
        event.preventDefault();
        const alias = (pill.getAttribute('data-alias') || '').toLowerCase();
        loadBlog(alias);
        return;
      }

      const inCardTag = event.target.closest('.blog-card__tag-link');
      if (inCardTag) {
        event.preventDefault();
        const alias = (inCardTag.getAttribute('data-alias') || '').toLowerCase();
        loadBlog(alias);
        return;
      }

      const card = event.target.closest('.blog-card');
      if (!card) {
        return;
      }
      if (event.target.closest('.blog-card__tag-link')) {
        return;
      }
      const href = card.dataset.href;
      if (href) {
        window.location.href = href;
      }
    });

    document.addEventListener('keydown', function (event) {
      if ((event.key === 'Enter' || event.key === ' ') && event.target.classList && event.target.classList.contains('blog-card')) {
        const href = event.target.dataset.href;
        if (href) {
          event.preventDefault();
          window.location.href = href;
        }
      }
    });

    if (history && history.replaceState) {
      history.replaceState({ tag: currentTag }, '', window.location.href);
    }

    window.addEventListener('popstate', function () {
      const alias = (new URL(window.location.href)).searchParams.get('tag') || '';
      loadBlog(alias, { skipPush: true, force: true });
    });
  })();
</script>
