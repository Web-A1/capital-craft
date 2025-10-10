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
$app->getDocument()->getWebAssetManager()->useScript("tpl.capitalcraft.blog.filter");
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
          <article class="blog-card blog-card--lead" data-href="<?php echo $cardLink; ?>">
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
          <article class="blog-card" data-href="<?php echo $cardLink; ?>">
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
