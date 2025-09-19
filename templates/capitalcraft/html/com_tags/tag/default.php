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
      <h1 class="blog__subtitle" id="blog-subtitle">
        экспертные статьи и новости рынка финансов
      </h1>
      <p class="blog__title" id="blog-title">
        Практика привлечения капитала и ключевые события рынка
      </p>
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

// Read from model state first (handles direct loads reliably)
$stateIdRaw = (string) ($this->state->get('tag.id', ''));
if ($stateIdRaw !== '') {
    $stateIdParts = explode(',', $stateIdRaw, 2);
    $stateFirstId = (int) ($stateIdParts[0] ?? 0);
    if ($stateFirstId > 0) {
        $currentTagId = $stateFirstId;
    }
}

// Fallback: read current tag from request; 'id' may be string or array
$rawIdParam = $app->input->get('id', null);
$rawId = '';
if ($rawIdParam !== null) {
    if (\is_array($rawIdParam)) {
        $rawId = (string) ($rawIdParam[0] ?? '');
    } else {
        $rawId = (string) $rawIdParam;
    }

    if ($rawId !== '') {
        $parts = explode(':', $rawId, 2);
        if ($currentTagId <= 0) {
            $currentTagId = (int) ($parts[0] ?? 0);
        }
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
// Force breadcrumbs to point to Blog only (match /blog view)
if ($blogRoute) {
    $pathway = $app->getPathway();
    try {
        if (method_exists($pathway, 'setPathway')) {
            $pathway->setPathway([(object) ['name' => 'Блог', 'link' => $blogRoute]]);
        } else {
            $pathway->addItem('Блог', $blogRoute);
        }
    } catch (\Throwable $e) {
        // no-op
    }
}
if ($blogRoute): ?>
            <li class="blog-tags__tag">
              <a
                class="blog-tags__link<?php echo $currentTagAlias === "" ? " is-active" : ""; ?>"
                href="<?php echo $blogRoute; ?>"
                <?php echo $currentTagAlias === "" ? 'aria-current="page"' : ""; ?>
              >Все статьи</a>
            </li>
          <?php endif;
?>
          <?php foreach ($availableTags as $tagOption): ?>
            <?php
  $tagAliasLower = strtolower($tagOption->alias ?? "");
              $isActive = $currentTagAlias === $tagAliasLower;
              // Redirect intent: link to blog with tag param to unify behavior
              $tagRouteBase = $blogRoute ?: Route::_('index.php');
              $sep = (strpos($tagRouteBase, '?') === false) ? '?' : '&';
              $tagRoute = $tagRouteBase . $sep . 'tag=' . rawurlencode($tagOption->alias ?? '');
              ?>
            <li class="blog-tags__tag">
              <a
                class="blog-tags__link<?php echo $isActive ? " is-active" : ""; ?>"
                href="<?php echo $tagRoute; ?>"
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
            <?php
              $tagRouteBase = $blogRoute ?: Route::_('index.php');
              $sep = (strpos($tagRouteBase, '?') === false) ? '?' : '&';
              $tagRoute = $tagRouteBase . $sep . 'tag=' . rawurlencode($tg->alias ?? '');
              ?>
            <li class="blog-tags__tag">
              <a class="blog-tags__link" href="<?php echo $tagRoute; ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, 'UTF-8'); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

  </div>
</section>

<script defer src="/templates/capitalcraft/js/pages/blog/filter.js"></script>
