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
  document.addEventListener('click', function(e) {
    const tagLink = e.target.closest('.blog-card__tag-link');
    if (tagLink) return;
    const card = e.target.closest('.blog-card');
    if (!card) return;
    const href = card.dataset.href;
    if (href) window.location.href = href;
  });
</script>
