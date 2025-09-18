<?php
defined("_JEXEC") or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Tags\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Tags\Site\View\Tag\HtmlView $this */

if (empty($this->items)): ?>
  <div class="alert alert-info">
    <span class="icon-info-circle" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo Text::_("INFO"); ?></span>
    <?php echo Text::_("COM_TAGS_NO_ITEMS"); ?>
  </div>
<?php else: ?>
  <div class="blog-list">
    <?php foreach ($this->items as $i => $item): ?>
      <?php
      $images = json_decode($item->core_images ?: "{}");
      $intro = !empty($images->image_intro) ? $images->image_intro : null;
      $alt = !empty($images->image_intro_alt) ? $images->image_intro_alt : "";
      $link = Route::_(
          RouteHelper::getItemRoute(
              $item->content_item_id,
              $item->core_alias,
              $item->core_catid,
              $item->core_language,
              $item->type_alias,
              $item->router,
          ),
      );
      $date = $item->core_created_time ?? ($item->core_publish_up ?? "");
      $excerpt = HTMLHelper::_("string.truncate", strip_tags($item->core_body ?? ""), 350);
      ?>
      <article class="blog-card" data-href="<?php echo $link; ?>">
        <div class="blog-card__grid">
          <div class="blog-card__main">
            <header class="blog-card__header">
              <h2 class="blog-card__title">
                <a href="<?php echo $link; ?>" class="blog-card__title-link">
                  <?php echo $this->escape($item->core_title); ?>
                </a>
              </h2>
            </header>

            <div class="blog-card__excerpt">
              <?php echo $excerpt; ?>
            </div>

            <div class="blog-card__meta">
              <?php if ($date): ?>
                <time class="blog-card__date" datetime="<?php echo HTMLHelper::_("date", $date, "c"); ?>">
                  <?php echo HTMLHelper::_("date", $date, Text::_("DATE_FORMAT_LC3")); ?>
                </time>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($intro): ?>
            <figure class="blog-card__image">
              <a href="<?php echo $link; ?>">
                <img src="<?php echo htmlspecialchars(
                    $intro,
                    ENT_QUOTES,
                    "UTF-8",
                ); ?>" alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, "UTF-8"); ?>">
              </a>
            </figure>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif;
