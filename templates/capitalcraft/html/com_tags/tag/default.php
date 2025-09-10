<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Tags\Site\View\Tag\HtmlView $this */
$htag = $this->params->get('show_page_heading') ? 'h2' : 'h1';
?>

<section class="frame section-with-divider blog-tags" aria-labelledby="tag-title">
  <div class="container">

    <header class="blog__header">
      <?php if ($this->params->get('show_page_heading')) : ?>
        <h1 class="blog__subtitle" id="tag-subtitle">
          <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
      <?php endif; ?>

      <?php if ($this->params->get('show_tag_title', 1)) : ?>
        <<?php echo $htag; ?> class="blog__title" id="tag-title">
          <?php echo HTMLHelper::_('content.prepare', $this->tags_title, '', 'com_tags.tag'); ?>
        </<?php echo $htag; ?>>
      <?php endif; ?>
    </header>

    <?php echo $this->loadTemplate('items'); ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
      <nav class="blog-pagination" aria-label="Пагинация по тегу">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
          <p class="blog-pagination__counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
        <div class="blog-pagination__links"><?php echo $this->pagination->getPagesLinks(); ?></div>
      </nav>
    <?php endif; ?>

  </div>
</section>

