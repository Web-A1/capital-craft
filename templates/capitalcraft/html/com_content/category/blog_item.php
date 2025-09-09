<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$params = $this->item->params;

// Link to full article
$articleLink = Route::_(RouteHelper::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));

// Pick publish date or created as fallback
$dateValue = $this->item->publish_up ?: $this->item->created;
?>

<header class="blog-card__header">
  <h2 class="blog-card__title">
    <a href="<?php echo $articleLink; ?>" class="blog-card__title-link">
      <?php echo $this->escape($this->item->title); ?>
    </a>
  </h2>
</header>

<div class="blog-card__excerpt">
  <?php echo $this->item->introtext; ?>
  <?php // If introtext is empty, show a trimmed part of fulltext (rare)
  if (empty($this->item->introtext) && !empty($this->item->fulltext)) {
      echo HTMLHelper::_('string.truncate', strip_tags($this->item->fulltext), 260);
  }
  ?>
  <?php if ($params->get('show_readmore') && $this->item->readmore) : ?>
    <p class="blog-card__more"><a href="<?php echo $articleLink; ?>"><?php echo HTMLHelper::_('string.truncate', $params->get('alternative_readmore', JText::_('COM_CONTENT_READ_MORE')) ?: JText::_('COM_CONTENT_READ_MORE'), 100); ?></a></p>
  <?php endif; ?>
</div>

<div class="blog-card__meta">
  <time class="blog-card__date" datetime="<?php echo HTMLHelper::_('date', $dateValue, 'c'); ?>">
    <?php echo HTMLHelper::_('date', $dateValue, JText::_('DATE_FORMAT_LC3')); ?>
  </time>

  <?php if (!empty($this->item->tags->itemTags)) : ?>
    <ul class="blog-card__tags">
      <?php foreach ($this->item->tags->itemTags as $tag) : ?>
        <li class="blog-card__tag">
          <a href="<?php echo Route::_('index.php?option=com_tags&view=tag&id=' . (int) $tag->tag_id); ?>" class="blog-card__tag-link">#<?php echo $this->escape($tag->title); ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

