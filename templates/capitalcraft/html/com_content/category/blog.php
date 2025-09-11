<?php
defined("_JEXEC") or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */

// Prepare category description plugins like core does
$this->category->text = $this->category->description;
$app = JFactory::getApplication();
$app->triggerEvent("onContentPrepare", [
    $this->category->extension . ".categories",
    &$this->category,
    &$this->params,
    0,
]);
$this->category->description = $this->category->text;

$htag = $this->params->get("show_page_heading") ? "h2" : "h1";
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
    $db = Joomla\CMS\Factory::getDbo();
$q = $db
    ->getQuery(true)
    ->select("t.id, t.title")
    ->from($db->quoteName("#__tags", "t"))
    ->join(
        "INNER",
        $db->quoteName("#__contentitem_tag_map", "m") .
            " ON m.tag_id = t.id AND m.type_alias = " .
            $db->quote("com_content.article"),
    )
    ->where("t.published = 1")
    ->group("t.id")
    ->order("t.title ASC");
$db->setQuery($q);
$allTags = (array) $db->loadObjectList();
?>

    <?php if (!empty($allTags)): ?>
      <nav class="blog__tags-nav" aria-label="Навигация по тегам">
        <ul class="blog-tags__cloud blog-tags__cloud--nowrap">
          <?php foreach ($allTags as $tg): ?>
            <li class="blog-tags__tag">
              <a class="blog-tags__link" href="<?php echo JRoute::_(
                  "index.php?option=com_tags&view=tag&id=" . (int) $tg->id,
              ); ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, "UTF-8"); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
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
          <article class="blog-card blog-card--lead" data-href="<?php echo $cardLink; ?>" role="link" tabindex="0">
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
          <article class="blog-card" data-href="<?php echo $cardLink; ?>" role="link" tabindex="0">
            <?php
            $this->item = &$item;
            echo $this->loadTemplate("item");
            ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if (!empty($this->link_items)): ?>
      <div class="blog-links">
        <?php echo $this->loadTemplate("links"); ?>
      </div>
    <?php endif; ?>

    <?php if (
        ($this->params->def("show_pagination", 1) == 1 || $this->params->get("show_pagination") == 2) &&
        $this->pagination->pagesTotal > 1
    ): ?>
      <nav class="blog-pagination" aria-label="Пагинация блога">
        <?php if ($this->params->def("show_pagination_results", 1)): ?>
          <p class="blog-pagination__counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
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
  document.addEventListener('keydown', function(e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('blog-card')) {
      const href = e.target.dataset.href;
      if (href) { e.preventDefault(); window.location.href = href; }
    }
  });
</script>
