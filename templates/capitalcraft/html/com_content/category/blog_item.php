<?php
defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$params = $this->item->params;

// Link to full article
$articleLink = Route::_(RouteHelper::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));

// Pick publish date or created as fallback
$dateValue = $this->item->publish_up ?: $this->item->created;

// Prepare intro image
$imagesObj = !empty($this->item->images) ? json_decode($this->item->images) : null;
$introImg =
    $imagesObj && !empty($imagesObj->image_intro)
        ? $imagesObj->image_intro
        : ($imagesObj && !empty($imagesObj->image_fulltext)
            ? $imagesObj->image_fulltext
            : null);
$introImgAlt =
    $imagesObj && !empty($imagesObj->image_intro_alt)
        ? $imagesObj->image_intro_alt
        : ($imagesObj && !empty($imagesObj->image_fulltext_alt)
            ? $imagesObj->image_fulltext_alt
            : "");

// Build excerpt (200 chars, plain text)
$sourceText = trim(strip_tags($this->item->introtext ?: $this->item->fulltext ?? ""));
$excerpt = HTMLHelper::_("string.truncate", $sourceText, 350);

$menu = Factory::getApplication()->getMenu();
$blogItem = $menu->getItems("alias", "blog", true);
$blogItemId = $blogItem ? $blogItem->id : 0;
?>

<div class="blog-card__grid">
  <div class="blog-card__main">
    <header class="blog-card__header">
      <h2 class="blog-card__title">
        <a href="<?php echo $articleLink; ?>" class="blog-card__title-link">
          <?php echo $this->escape($this->item->title); ?>
        </a>
      </h2>
    </header>

    <div class="blog-card__excerpt">
      <?php echo $excerpt; ?>
      <?php if ($params->get("show_readmore") && $this->item->readmore): ?>
        <p class="blog-card__more"><a href="<?php echo $articleLink; ?>"><?php echo HTMLHelper::_(
    "string.truncate",
    $params->get("alternative_readmore", JText::_("COM_CONTENT_READ_MORE")) ?: JText::_("COM_CONTENT_READ_MORE"),
    100,
); ?></a></p>
      <?php endif; ?>
    </div>

    <div class="blog-card__meta">
      <time class="blog-card__date" datetime="<?php echo HTMLHelper::_("date", $dateValue, "c"); ?>">
        <?php echo HTMLHelper::_("date", $dateValue, JText::_("DATE_FORMAT_LC3")); ?>
      </time>

      <?php if (!empty($this->item->tags->itemTags)): ?>
        <ul class="blog-card__tags">
          <?php foreach ($this->item->tags->itemTags as $tag): ?>
            <li class="blog-card__tag">
              <a href="<?php echo Route::_(
                  "index.php?Itemid=" . $blogItemId . "&tag=" . urlencode($tag->alias),
              ); ?>" class="blog-card__tag-link" data-alias="<?php echo htmlspecialchars(
    $tag->alias ?? "",
    ENT_QUOTES,
    "UTF-8",
); ?>">#<?php echo $this->escape($tag->title); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($introImg): ?>
    <figure class="blog-card__image">
      <a href="<?php echo $articleLink; ?>">
        <img src="<?php echo htmlspecialchars($introImg, ENT_QUOTES, "UTF-8"); ?>" alt="<?php echo htmlspecialchars(
    $introImgAlt,
    ENT_QUOTES,
    "UTF-8",
); ?>">
      </a>
    </figure>
  <?php endif; ?>
</div>
