<?php
defined('_JEXEC') or die;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
?>

<section class="frame section-with-divider blog-page" aria-labelledby="blog-search-title">
  <div class="container blog-page__inner">

    <?php if ($this->params->get('show_page_heading')) : ?>
      <h1 id="blog-search-title">
        <?php if ($this->escape($this->params->get('page_heading'))) : ?>
          <?php echo $this->escape($this->params->get('page_heading')); ?>
        <?php else : ?>
          <?php echo $this->escape($this->params->get('page_title')); ?>
        <?php endif; ?>
      </h1>
    <?php endif; ?>

    <div class="blog-page__grid">
      <aside class="blog-filters" aria-label="Фильтры блога">
        <?php echo $this->loadTemplate('form'); ?>
      </aside>

      <div class="blog-content">
        <div id="search-results" class="com-finder__results">
          <?php echo $this->loadTemplate('results'); ?>
        </div>
      </div>
    </div>

  </div>
</section>
