<?php defined("_JEXEC") or die();

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

// Определяем, является ли это FAQ страницей
$isFAQPage = false;

// Проверяем по нескольким критериям
if (isset($this->item)) {
    // По заголовку
    if (
        stripos($this->item->title, "FAQ") !== false ||
        stripos($this->item->title, "часто задаваемые вопросы") !== false ||
        stripos($this->item->title, "вопросы") !== false
    ) {
        $isFAQPage = true;
    }

    // По alias
    if (isset($this->item->alias) && stripos($this->item->alias, "faq") !== false) {
        $isFAQPage = true;
    }

    // По категории
    if (isset($this->item->catid) && $this->item->catid == 1) {
        // Предполагаем, что FAQ имеет catid = 1
        $isFAQPage = true;
    }
}

// Если это FAQ страница, используем наш кастомный шаблон
if ($isFAQPage) {
    // Загружаем наш FAQ шаблон
    $this->loadTemplate("faq");
} else {

    // Для остальных страниц используем стандартную Joomla логику
    // Но с улучшенной SEO оптимизацией

    // SEO мета-теги
    $doc = JFactory::getDocument();

    // Улучшенный title
    if (!empty($this->item->title)) {
        $doc->setTitle($this->item->title . " - Capital Craft | Инвестиционные решения");
    }

    // Description берём только из админки (без автогенерации)
    if (!empty($this->item->metadesc)) {
        $doc->setDescription($this->item->metadesc);
    }

    // Canonical URL
    $canonical = JURI::current();
    $doc->addHeadLink($canonical, "canonical", "rel");

    // Open Graph теги
    $doc->addCustomTag(
        '<meta property="og:title" content="' . htmlspecialchars($this->item->title, ENT_QUOTES, "UTF-8") . '" />',
    );
    $ogDescription = $doc->getMetaData("description");
    if (!empty($ogDescription)) {
        $doc->addCustomTag(
            '<meta property="og:description" content="' .
                htmlspecialchars($ogDescription, ENT_QUOTES, "UTF-8") .
                '" />',
        );
    }
    $doc->addCustomTag('<meta property="og:type" content="article" />');
    $doc->addCustomTag('<meta property="og:url" content="' . $canonical . '" />');
    $doc->addCustomTag('<meta property="og:site_name" content="Capital Craft" />');
    $doc->addCustomTag('<meta property="og:locale" content="ru_RU" />');

    // OG image: берём изображение материала, иначе дефолт
    $ogImage = "";
    if (!empty($this->item->images)) {
        $imagesObj = json_decode($this->item->images);
        if (!empty($imagesObj->image_fulltext)) {
            $ogImage = $imagesObj->image_fulltext;
        } elseif (!empty($imagesObj->image_intro)) {
            $ogImage = $imagesObj->image_intro;
        }
    }
    if (!empty($ogImage)) {
        if (strpos($ogImage, "http") !== 0) {
            $ogImage = JURI::root() . ltrim($ogImage, "/");
        }
    } else {
        $ogImage = JURI::root() . "templates/capitalcraft/images/og/OG-image.webp";
    }
    $doc->addCustomTag(
        '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, "UTF-8") . '" />',
    );
    $doc->addCustomTag(
        '<meta name="twitter:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, "UTF-8") . '" />',
    );

    // Robots meta
    $doc->setMetaData("robots", "index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1");

    // Структурированные данные для статьи
    $schemaDescription = $doc->getMetaData("description");
    $articleSchema = [
        "@context" => "https://schema.org",
        "@type" => "Article",
        "headline" => $this->item->title,
        "description" => $schemaDescription,
        "url" => $canonical,
        "datePublished" => $this->item->publish_up,
        "dateModified" => $this->item->modified,
        "author" => [
            "@type" => "Organization",
            "name" => "Capital Craft",
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "Capital Craft",
            "logo" => [
                "@type" => "ImageObject",
                "url" => JURI::root() . "templates/capitalcraft/images/logo_black.svg",
            ],
        ],
    ];

    $doc->addCustomTag(
        '<script type="application/ld+json">' . json_encode($articleSchema, JSON_UNESCAPED_UNICODE) . "</script>",
    );

    // Стандартная Joomla разметка
    ?>
    <section class="frame section-with-divider article">
    <div class="container com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
        <?php if ($this->params->get("show_title")): ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->item->title); ?></h1>
        </div>
        <?php elseif ($this->params->get("show_page_heading")): ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->params->get("page_heading")); ?></h1>
        </div>
        <?php endif; ?>
        
        <?php // Мета‑строка под заголовком: дата слева — теги справа

    $dateValue = $this->item->publish_up ?: $this->item->created; ?>
        <div class="blog-card__meta">
          <?php if (!empty($dateValue)): ?>
            <time class="blog-card__date" datetime="<?php echo JHtml::_("date", $dateValue, "c"); ?>">
              <?php echo JHtml::_("date", $dateValue, JText::_("DATE_FORMAT_LC3")); ?>
            </time>
          <?php endif; ?>

          <?php if (!empty($this->item->tags->itemTags)): ?>
            <?php
            $menu = JFactory::getApplication()->getMenu();
            $blogItem = $menu->getItems('alias', 'blog', true);
            $blogItemId = $blogItem ? $blogItem->id : 0;
            ?>
            <ul class="blog-card__tags">
              <?php foreach ($this->item->tags->itemTags as $tag): ?>
                <li class="blog-card__tag">
                  <a href="<?php echo JRoute::_('index.php?Itemid=' . $blogItemId . '&tag=' . urlencode($tag->alias)); ?>" class="blog-card__tag-link" data-alias="<?php echo htmlspecialchars($tag->alias ?? '', ENT_QUOTES, 'UTF-8'); ?>">#<?php echo $this->escape($tag->title); ?></a>
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
                : "");
    $mainAlt =
        $imagesObj && !empty($imagesObj->image_fulltext_alt)
            ? $imagesObj->image_fulltext_alt
            : ($imagesObj && !empty($imagesObj->image_intro_alt)
                ? $imagesObj->image_intro_alt
                : "");
    ?>

        <?php
    // Build related articles by the same tag(s)
    $relatedItems = [];
    $relatedTagTitle = "";
    if (!empty($this->item->tags->itemTags)) {
        // Collect tag IDs and remember the first tag title for the heading
        $tagIds = [];
        $firstTagAlias = "";
        foreach ($this->item->tags->itemTags as $tg) {
            if (!empty($tg->tag_id)) {
                $tagIds[] = (int) $tg->tag_id;
            }
            if ($relatedTagTitle === "" && !empty($tg->title)) {
                $relatedTagTitle = $tg->title;
            }
            if ($firstTagAlias === "" && !empty($tg->alias)) {
                $firstTagAlias = $tg->alias;
            }
        }

        if (!empty($tagIds)) {
            $db = JFactory::getDbo();
            $query = $db
                ->getQuery(true)
                ->select("c.id, c.title, c.alias, c.catid, c.publish_up, c.language")
                ->from($db->quoteName("#__content", "c"))
                ->join(
                    "INNER",
                    $db->quoteName("#__contentitem_tag_map", "m") .
                        " ON " .
                        $db->quoteName("m.content_item_id") .
                        " = " .
                        $db->quoteName("c.id") .
                        " AND " .
                        $db->quoteName("m.type_alias") .
                        " = " .
                        $db->quote("com_content.article"),
                )
                ->where("c.state = 1")
                ->where("c.id != " . (int) $this->item->id)
                ->where("m.tag_id IN (" . implode(",", array_map("intval", $tagIds)) . ")")
                ->group($db->quoteName("c.id"))
                ->order($db->escape("c.publish_up DESC"));

            $db->setQuery($query, 0, 4); // fetch up to 4 small previews
            $relatedItems = (array) $db->loadObjectList();
        }
    }
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
                    "UTF-8",
                ); ?>" alt="<?php echo htmlspecialchars($mainAlt, ENT_QUOTES, "UTF-8"); ?>">
              </figure>
            <?php endif; ?>

            <?php
            // Sidebar: FAQ with the same tag(s)
            $faqRelated = [];
    if (!empty($tagIds)) {
        // find FAQ category id
        $dbFaq = JFactory::getDbo();
        $qCat = $dbFaq
            ->getQuery(true)
            ->select($dbFaq->quoteName("id"))
            ->from($dbFaq->quoteName("#__categories"))
            ->where($dbFaq->quoteName("extension") . " = " . $dbFaq->quote("com_content"))
            ->where($dbFaq->quoteName("alias") . " = " . $dbFaq->quote("faq"))
            ->where($dbFaq->quoteName("published") . " = 1");
        $dbFaq->setQuery($qCat);
        $faqCatId = (int) $dbFaq->loadResult();
        if ($faqCatId) {
            $qf = $dbFaq
                ->getQuery(true)
                ->select("c.id, c.title, c.introtext, c.fulltext, c.publish_up")
                ->from($dbFaq->quoteName("#__content", "c"))
                ->join(
                    "INNER",
                    $dbFaq->quoteName("#__contentitem_tag_map", "m") .
                        " ON m.content_item_id = c.id AND m.type_alias = " .
                        $dbFaq->quote("com_content.article"),
                )
                ->where("c.state = 1")
                ->where("c.catid = " . (int) $faqCatId)
                ->where("m.tag_id IN (" . implode(",", array_map("intval", $tagIds)) . ")")
                ->group("c.id")
                ->order("c.publish_up DESC");
            $dbFaq->setQuery($qf, 0, 5);
            $faqRelated = (array) $dbFaq->loadObjectList();
        }
    }

    if (!empty($relatedItems) || !empty($faqRelated)): ?>
              <div class="article__related-wrap">
                <div class="article__related-scroll">
                <?php if (!empty($relatedItems)): ?>
                  <aside class="article__related-block">
                    <div class="article__related-header">
                      <div class="article__related-title">
                        Другие статьи<?php echo $relatedTagTitle
                    ? " #" . htmlspecialchars($relatedTagTitle, ENT_QUOTES, "UTF-8")
                    : ""; ?>
                      </div>
                    </div>
                    <?php // Extend query to get intro/full text for previews
            // Extend query to get intro/full text for previews
            // Re-run query only if items lack excerpt fields
            if (!empty($relatedItems)) {
                $ids = array_map(function ($o) {
                    return (int) $o->id;
                }, $relatedItems);
                $db2 = JFactory::getDbo();
                $q2 = $db2
                    ->getQuery(true)
                    ->select(
                        $db2->quoteName("id") .
                            ", " .
                            $db2->quoteName("introtext") .
                            ", " .
                            $db2->quoteName("fulltext"),
                    )
                    ->from($db2->quoteName("#__content"))
                    ->where($db2->quoteName("id") . " IN (" . implode(",", $ids) . ")");
                $db2->setQuery($q2);
                $textsMap = [];
                foreach ((array) $db2->loadObjectList() as $row) {
                    $textsMap[$row->id] = !empty($row->introtext) ? $row->introtext : $row->fulltext;
                }
            } ?>
                    <ul class="article__related-list">
                      <?php foreach ($relatedItems as $rel): ?>
                        <?php
                $raw = isset($textsMap[$rel->id]) ? $textsMap[$rel->id] : "";
                          $excerpt = "";
                          if (!empty($raw)) {
                              $clean = strip_tags($raw);
                              // Увеличиваем лимит символов, чтобы отобразить до трёх строк
                              $excerpt = JHtml::_("string.truncate", $clean, 240, true, false);
                          }
                          ?>
                        <li class="article__related-item">
                          <a class="article__related-link" href="<?php echo Route::_(
                              RouteHelper::getArticleRoute(
                                  $rel->id . ":" . $rel->alias,
                                  $rel->catid,
                                  $rel->language ?? 0
                              ),
                          ); ?>">
                            <div class="article__related-link-title">
                              <?php echo htmlspecialchars($rel->title, ENT_QUOTES, "UTF-8"); ?>
                            </div>
                            <?php if (!empty($excerpt)): ?>
                              <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                  $excerpt,
                                  ENT_QUOTES,
                                  "UTF-8",
                              ); ?></div>
                            <?php endif; ?>
                          </a>
                          <?php if (!empty($rel->publish_up)): ?>
                            <time class="article__related-date" datetime="<?php echo JHtml::_(
                                "date",
                                $rel->publish_up,
                                "c",
                            ); ?>">
                              <?php echo JHtml::_("date", $rel->publish_up, JText::_("DATE_FORMAT_LC3")); ?>
                            </time>
                          <?php endif; ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </aside>
                <?php endif; ?>

                <?php if (!empty($faqRelated)): ?>
                  <aside class="article__related-block">
                    <div class="article__related-header">
                      <div class="article__related-title">
                        Вопросы<?php echo $relatedTagTitle
                            ? " #" . htmlspecialchars($relatedTagTitle, ENT_QUOTES, "UTF-8")
                            : ""; ?>
                      </div>
                    </div>
                    <ul class="article__related-list">
                      <?php foreach ($faqRelated as $fq): ?>
                        <?php
                        $raw = !empty($fq->introtext) ? $fq->introtext : $fq->fulltext ?? "";
                          $clean = trim(strip_tags($raw));
                          $excerpt = $clean ? JHtml::_("string.truncate", $clean, 200, true, false) : "";
                          $faqLink =
                              "/faq" .
                              ($firstTagAlias ? "?tag=" . rawurlencode($firstTagAlias) : "") .
                              "#faq-q-" .
                              (int) $fq->id;
                          ?>
                        <li class="article__related-item">
                          <a class="article__related-link" href="<?php echo $faqLink; ?>">
                            <div class="article__related-link-title">
                              <?php echo htmlspecialchars($fq->title, ENT_QUOTES, "UTF-8"); ?>
                            </div>
                            <?php if (!empty($excerpt)): ?>
                              <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                  $excerpt,
                                  ENT_QUOTES,
                                  "UTF-8",
                              ); ?></div>
                            <?php endif; ?>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </aside>
                <?php endif; ?>
                </div>
              </div>
            <?php endif;
    ?>
          </div>
        </div>
    </div>
    </section>
    <?php
}
?>
