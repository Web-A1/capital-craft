<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */

// Prepare category description plugins like core does
$this->category->text = $this->category->description;
$app = JFactory::getApplication();
$app->triggerEvent('onContentPrepare', [$this->category->extension . '.categories', &$this->category, &$this->params, 0]);
$this->category->description = $this->category->text;

$htag = $this->params->get('show_page_heading') ? 'h2' : 'h1';
?>

<section class="frame section-with-divider blog-index" aria-labelledby="blog-title">
  <div class="container">

    <header class="blog-index__header">
      <?php if ($this->params->get('show_page_heading')) : ?>
        <p class="blog-index__subtitle" id="blog-subtitle">
          <?php echo $this->escape($this->params->get('page_heading')); ?>
        </p>
      <?php endif; ?>

      <?php if ($this->params->get('show_category_title', 1)) : ?>
        <<?php echo $htag; ?> class="blog-index__title" id="blog-title">
          <?php echo $this->escape($this->category->title); ?>
        </<?php echo $htag; ?>>
      <?php endif; ?>

      <?php if ($this->params->get('show_cat_tags', 0) && !empty($this->category->tags->itemTags)) : ?>
        <?php $this->category->tagLayout = new FileLayout('joomla.content.tags'); ?>
        <div class="blog-index__category-tags">
          <?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
        </div>
      <?php endif; ?>

      <?php if ($this->params->get('show_description', 0) && $this->category->description) : ?>
        <div class="blog-index__description">
          <?php echo HTMLHelper::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
        </div>
      <?php endif; ?>
    </header>

    <?php if (empty($this->lead_items) && empty($this->intro_items) && empty($this->link_items)) : ?>
      <?php if ($this->params->get('show_no_articles', 1)) : ?>
        <div class="alert alert-info"><?php echo Text::_('COM_CONTENT_NO_ARTICLES'); ?></div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="blog-list">
      <?php if (!empty($this->lead_items)) : ?>
        <?php foreach ($this->lead_items as &$item) : ?>
          <article class="blog-card blog-card--lead">
            <?php $this->item = &$item; echo $this->loadTemplate('item'); ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($this->intro_items)) : ?>
        <?php foreach ($this->intro_items as &$item) : ?>
          <article class="blog-card">
            <?php $this->item = &$item; echo $this->loadTemplate('item'); ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if (!empty($this->link_items)) : ?>
      <div class="blog-links">
        <?php echo $this->loadTemplate('links'); ?>
      </div>
    <?php endif; ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
      <nav class="blog-pagination" aria-label="Пагинация блога">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
          <p class="blog-pagination__counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
    <?php endif; ?>

  </div>
</section>
