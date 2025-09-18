<?php defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;

require_once JPATH_SITE . '/templates/capitalcraft/helpers/RelatedHelper.php';

require_once JPATH_SITE . '/templates/capitalcraft/helpers/SeoHelper.php';

// Определяем, является ли это FAQ страницей
$isFAQPage = false;

// Проверяем по категории: alias категории должен быть 'faq'
if (isset($this->item) && isset($this->item->catid)) {
    $db = Factory::getDbo();
    $qCatAlias = $db
        ->getQuery(true)
        ->select($db->quoteName('alias'))
        ->from($db->quoteName('#__categories'))
        ->where($db->quoteName('id') . ' = ' . (int) $this->item->catid)
        ->where($db->quoteName('published') . ' = 1');
    $db->setQuery($qCatAlias);
    $currentCatAlias = strtolower((string) $db->loadResult());
    if ($currentCatAlias === 'faq') {
        $isFAQPage = true;
    }
}

// Если это FAQ страница, используем наш кастомный шаблон
if ($isFAQPage) {
    // Подключаем локальный шаблон FAQ прямо из override-директории
    require __DIR__ . '/faq.php';
} else {

    // Для остальных страниц используем стандартную Joomla логику
    // Но с улучшенной SEO оптимизацией

    // SEO мета-теги
    $doc = Factory::getDocument();

    // Улучшенный title
    if (!empty($this->item->title)) {
        $doc->setTitle($this->item->title . ' - Capital Craft | Инвестиционные решения');
    }

    // Description берём только из админки (без автогенерации)
    if (!empty($this->item->metadesc)) {
        $doc->setDescription($this->item->metadesc);
    }

    // Canonical URL
    $canonical = CapitalcraftSeoHelper::buildCanonical();
    CapitalcraftSeoHelper::addCanonicalLink($canonical);

    // Open Graph теги
    $doc->addCustomTag(
        '<meta property="og:title" content="' . htmlspecialchars($this->item->title, ENT_QUOTES, 'UTF-8') . '" />',
    );
    $ogDescription = $doc->getMetaData('description');
    if (!empty($ogDescription)) {
        $doc->addCustomTag(
            '<meta property="og:description" content="' .
                htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') .
                '" />',
        );
    }
    $doc->addCustomTag('<meta property="og:type" content="article" />');
    $doc->addCustomTag('<meta property="og:url" content="' . $canonical . '" />');
    $doc->addCustomTag('<meta property="og:site_name" content="Capital Craft" />');
    $doc->addCustomTag('<meta property="og:locale" content="ru_RU" />');

    // OG image: берём изображение материала, иначе дефолт
    $ogImage = '';
    if (!empty($this->item->images)) {
        $imagesObj = json_decode($this->item->images);
        if (!empty($imagesObj->image_fulltext)) {
            $ogImage = $imagesObj->image_fulltext;
        } elseif (!empty($imagesObj->image_intro)) {
            $ogImage = $imagesObj->image_intro;
        }
    }
    if (!empty($ogImage)) {
        if (strpos($ogImage, 'http') !== 0) {
            $ogImage = Uri::root() . ltrim($ogImage, '/');
        }
    } else {
        $ogImage = Uri::root() . 'templates/capitalcraft/images/og/OG-image.webp';
    }
    $doc->addCustomTag(
        '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . '" />',
    );
    $doc->addCustomTag(
        '<meta name="twitter:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . '" />',
    );

    // Robots meta
    $doc->setMetaData('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');

    // Структурированные данные для статьи
    $schemaDescription = $doc->getMetaData('description');
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $this->item->title,
        'description' => $schemaDescription,
        'url' => $canonical,
        'datePublished' => $this->item->publish_up,
        'dateModified' => $this->item->modified,
        'author' => [
            '@type' => 'Organization',
            'name' => 'Capital Craft',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Capital Craft',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => Uri::root() . 'templates/capitalcraft/images/logo_black.svg',
            ],
        ],
    ];

    $doc->addCustomTag(
        '<script type="application/ld+json">' . json_encode($articleSchema, JSON_UNESCAPED_UNICODE) . '</script>',
    );

    // Стандартная Joomla разметка
    ?>
    <section class="frame section-with-divider article">
    <div class="container com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
        <?php if ($this->params->get('show_title')): ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->item->title); ?></h1>
        </div>
        <?php elseif ($this->params->get('show_page_heading')): ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        </div>
        <?php endif; ?>
        
        <?php // Мета‑строка под заголовком: дата слева — теги справа

    $dateValue = $this->item->publish_up ?: $this->item->created; ?>
        <div class="blog-card__meta">
          <?php if (!empty($dateValue)): ?>
            <time class="blog-card__date" datetime="<?php echo HTMLHelper::_('date', $dateValue, 'c'); ?>">
              <?php echo HTMLHelper::_('date', $dateValue, Text::_('DATE_FORMAT_LC3')); ?>
            </time>
          <?php endif; ?>

          <?php if (!empty($this->item->tags->itemTags)): ?>
            <?php
            $menu = Factory::getApplication()->getMenu();
              $blogItem = $menu->getItems('alias', 'blog', true);
              $blogItemId = $blogItem ? $blogItem->id : 0;
              ?>
            <ul class="blog-card__tags">
              <?php foreach ($this->item->tags->itemTags as $tag): ?>
                <li class="blog-card__tag">
                  <?php
                    $menu = Factory::getApplication()->getMenu();
                    $blogItem = $menu->getItems('alias', 'blog', true);
                    $blogRoute = $blogItem ? Route::_('index.php?Itemid=' . (int) $blogItem->id) : Route::_('index.php');
                    $sep = (strpos($blogRoute, '?') === false) ? '?' : '&';
                    $tagHref = $blogRoute . $sep . 'tag=' . rawurlencode($tag->alias ?? '');
                  ?>
                  <a href="<?php echo $tagHref; ?>" class="blog-card__tag-link" data-alias="<?php echo htmlspecialchars(
                      $tag->alias ?? '',
                      ENT_QUOTES,
                      'UTF-8',
                  ); ?>">#<?php echo $this->escape($tag->title); ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
        
        <?php
        // Right-side illustration (from article images)
        $imagesObj = !empty($this->item->images) ? json_decode($this->item->images) : null;
    $mainImg =
        $imagesObj && !empty($imagesObj->image_fulltext)
            ? $imagesObj->image_fulltext
            : ($imagesObj && !empty($imagesObj->image_intro)
                ? $imagesObj->image_intro
                : '');
    $mainAlt =
        $imagesObj && !empty($imagesObj->image_fulltext_alt)
            ? $imagesObj->image_fulltext_alt
            : ($imagesObj && !empty($imagesObj->image_intro_alt)
                ? $imagesObj->image_intro_alt
                : '');
    ?>

        <?php
    // Build related articles and FAQ via helper
    $tagIds = [];
    if (!empty($this->item->tags->itemTags)) {
        foreach ($this->item->tags->itemTags as $tg) {
            if (!empty($tg->tag_id)) {
                $tagIds[] = (int) $tg->tag_id;
            }
        }
    }

    $relatedData = CapitalcraftRelatedHelper::getRelatedForArticle($this->item, $tagIds);
    ?>

        <div class="article__grid">
          <div class="article__main">
            <div class="com-content-article__body">
              <?php echo $this->item->text; ?>
            </div>
          </div>

          <div class="article__side">
            <?php if (!empty($mainImg)): ?>
              <figure class="article__image">
                <img src="<?php echo htmlspecialchars(
                    $mainImg,
                    ENT_QUOTES,
                    'UTF-8',
                ); ?>" alt="<?php echo htmlspecialchars($mainAlt, ENT_QUOTES, 'UTF-8'); ?>">
              </figure>
            <?php endif; ?>

            <?php $hasRelated = !empty($relatedData['articles']) || !empty($relatedData['faq']); ?>

            <?php if ($hasRelated): ?>
              <div class="article__related-wrap">
                <div class="article__related-header">
                  <div class="article__related-title">Читайте также</div>
                  <?php if (!empty($relatedData['heading_tags'])): ?>
                    <div class="article__related-tags">
                      <?php foreach ($relatedData['heading_tags'] as $tagInfo): ?>
                        <?php
                        $safeTitle = htmlspecialchars((string) $tagInfo['title'], ENT_QUOTES, 'UTF-8');
                          $safeTitle = preg_replace('/\s+/', '&nbsp;', $safeTitle);
                          ?>
                        <span class="article__related-tag">#<?php echo $safeTitle; ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="article__related-scroll">
                  <aside class="article__related-block">
                    <ul class="article__related-list">
                      <?php if (!empty($relatedData['articles'])): ?>
                        <?php foreach ($relatedData['articles'] as $rel): ?>
                          <li class="article__related-item">
                            <a class="article__related-link" href="<?php echo htmlspecialchars(
                                $rel['link'],
                                ENT_QUOTES,
                                'UTF-8',
                            ); ?>">
                              <div class="article__related-link-title">
                                <?php echo htmlspecialchars($rel['title'], ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                              <?php if (!empty($rel['excerpt'])): ?>
                                <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                    $rel['excerpt'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?></div>
                              <?php endif; ?>
                            </a>
                            <?php if (!empty($rel['publish_up'])): ?>
                              <time class="article__related-date" datetime="<?php echo HTMLHelper::_(
                                  'date',
                                  $rel['publish_up'],
                                  'c'
                              ); ?>">
                                <?php echo HTMLHelper::_(
                                    'date',
                                    $rel['publish_up'],
                                    Text::_('DATE_FORMAT_LC3')
                                ); ?>
                              </time>
                            <?php endif; ?>
                          </li>
                        <?php endforeach; ?>
                      <?php endif; ?>

                      <?php if (!empty($relatedData['faq'])): ?>
                        <?php foreach ($relatedData['faq'] as $fq): ?>
                          <li class="article__related-item">
                            <a class="article__related-link" href="<?php echo htmlspecialchars(
                                $fq['link'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">
                              <div class="article__related-link-title">
                                <?php echo htmlspecialchars($fq['title'], ENT_QUOTES, 'UTF-8'); ?>
                              </div>
                              <?php if (!empty($fq['excerpt'])): ?>
                                <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                    $fq['excerpt'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?></div>
                              <?php endif; ?>
                            </a>
                            <?php if (!empty($fq['publish_up'])): ?>
                              <time class="article__related-date" datetime="<?php echo HTMLHelper::_(
                                  'date',
                                  $fq['publish_up'],
                                  'c'
                              ); ?>">
                                <?php echo HTMLHelper::_(
                                    'date',
                                    $fq['publish_up'],
                                    Text::_('DATE_FORMAT_LC3')
                                ); ?>
                              </time>
                            <?php endif; ?>
                          </li>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </ul>
                  </aside>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
    </div>
    </section>
    <?php
}
?>
