<?php defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once JPATH_SITE . "/templates/capitalcraft/helpers/RelatedHelper.php";

require_once JPATH_SITE . "/templates/capitalcraft/helpers/SeoHelper.php";

// Определяем, является ли это FAQ страницей
$isFAQPage = false;

// Сначала используем alias категории из объекта статьи; при его отсутствии — фолбэк на запрос
if (isset($this->item)) {
    $catAlias = "";

    if (!empty($this->item->category_alias)) {
        $catAlias = strtolower((string) $this->item->category_alias);
    } elseif (isset($this->item->category) && !empty($this->item->category->alias)) {
        $catAlias = strtolower((string) $this->item->category->alias);
    }

    if ($catAlias === "faq") {
        $isFAQPage = true;
    } elseif (isset($this->item->catid)) {
        // Фолбэк: если alias не доступен в объекте, делаем единичный запрос к БД
        $db = Factory::getDbo();
        $qCatAlias = $db
            ->getQuery(true)
            ->select($db->quoteName("alias"))
            ->from($db->quoteName("#__categories"))
            ->where($db->quoteName("id") . " = " . (int) $this->item->catid)
            ->where($db->quoteName("published") . " = 1");
        $db->setQuery($qCatAlias);
        $currentCatAlias = strtolower((string) $db->loadResult());
        if ($currentCatAlias === "faq") {
            $isFAQPage = true;
        }
    }
}

// Если это FAQ страница, используем наш кастомный шаблон
if ($isFAQPage) {
    // Подключаем локальный шаблон FAQ прямо из override-директории
    require __DIR__ . "/faq.php";
} else {

    // Для остальных страниц используем стандартную Joomla логику
    // Но с улучшенной SEO оптимизацией

    // SEO мета-теги
    $doc = Factory::getDocument();
    $defaultBreadcrumbId = CapitalcraftSeoHelper::getDefaultBreadcrumbId();
    // echo '<!-- bcId: ' . $defaultBreadcrumbId . ' -->';

    $bodyText = "";
    if (!empty($this->item->text)) {
        $tmp = strip_tags($this->item->text);
        $tmp = html_entity_decode($tmp, ENT_QUOTES | ENT_HTML5, "UTF-8");
        $tmp = preg_replace("/\s+/u", " ", $tmp);
        $bodyText = trim($tmp);
    }

    $articleImages = !empty($this->item->images) ? json_decode($this->item->images) : null;

    // Улучшенный title
    if (!empty($this->item->title)) {
        $doc->setTitle(CapitalcraftSeoHelper::buildArticleTitle($this->item->title));
    }

    // Description берём только из админки (без автогенерации)
    if (!empty($this->item->metadesc)) {
        $doc->setDescription($this->item->metadesc);
    }

    $currentDescription = $doc->getMetaData("description");
    if (empty($currentDescription) && $bodyText !== "") {
        $generatedDescription = CapitalcraftSeoHelper::buildMetaDescriptionFromText($bodyText);
        if ($generatedDescription !== "") {
            $doc->setDescription($generatedDescription);
        }
    }

    // Canonical URL
    $canonical = CapitalcraftSeoHelper::buildCanonical();
    CapitalcraftSeoHelper::addCanonicalLink($canonical);

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
    if (!empty($publishedIso)) {
        $doc->addCustomTag('<meta property="article:published_time" content="' . $publishedIso . '" />');
    }
    if (!empty($modifiedIso)) {
        $doc->addCustomTag('<meta property="article:modified_time" content="' . $modifiedIso . '" />');
    }

    // OG image: берём изображение материала, иначе дефолт
    $ogImage = "";
    $ogImageAlt = "";
    $ogImageWidth = null;
    $ogImageHeight = null;
    if ($articleImages) {
        if (!empty($articleImages->image_fulltext)) {
            $ogImage = $articleImages->image_fulltext;
            $ogImageAlt = $articleImages->image_fulltext_alt ?? "";
        } elseif (!empty($articleImages->image_intro)) {
            $ogImage = $articleImages->image_intro;
            $ogImageAlt = $articleImages->image_intro_alt ?? "";
        }
    }
    if (!empty($ogImage)) {
        if (strpos($ogImage, "http") !== 0) {
            $ogImage = Uri::root() . ltrim($ogImage, "/");
        }
        // Удаляем hash-добавку Joomla (#joomlaImage://...), сохраняя query-параметры (width/height)
        $ogImageParts = explode("#", $ogImage, 2);
        $ogImage = trim($ogImageParts[0]);

        if (!empty($ogImageParts[1]) && strpos($ogImageParts[1], "?") !== false) {
            [, $fragmentQuery] = explode("?", $ogImageParts[1], 2);
            $fragmentQuery = trim($fragmentQuery);
            if ($fragmentQuery !== "") {
                $ogImage .= (strpos($ogImage, "?") === false ? "?" : "&") . $fragmentQuery;
                parse_str($fragmentQuery, $imgQueryParams);
                if (isset($imgQueryParams["width"])) {
                    $ogImageWidth = (int) $imgQueryParams["width"];
                }
                if (isset($imgQueryParams["height"])) {
                    $ogImageHeight = (int) $imgQueryParams["height"];
                }
            }
        }
    } else {
        $ogImage = Uri::root() . "templates/capitalcraft/images/og/OG-image.webp";
        $ogImageAlt = "Capital Craft";
        $ogImageWidth = 1200;
        $ogImageHeight = 630;
    }
    if ($ogImageAlt === "") {
        $ogImageAlt = $this->item->title ?: "Capital Craft";
    }
    $doc->addCustomTag(
        '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, "UTF-8") . '" />',
    );
    $doc->addCustomTag(
        '<meta property="og:image:alt" content="' . htmlspecialchars($ogImageAlt, ENT_QUOTES, "UTF-8") . '" />',
    );
    if ($ogImageWidth && $ogImageWidth > 0) {
        $doc->addCustomTag('<meta property="og:image:width" content="' . (int) $ogImageWidth . '" />');
    }
    if ($ogImageHeight && $ogImageHeight > 0) {
        $doc->addCustomTag('<meta property="og:image:height" content="' . (int) $ogImageHeight . '" />');
    }
    $doc->addCustomTag(
        '<meta name="twitter:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, "UTF-8") . '" />',
    );
    $doc->addCustomTag(
        '<meta name="twitter:image:alt" content="' . htmlspecialchars($ogImageAlt, ENT_QUOTES, "UTF-8") . '" />',
    );
    $doc->addCustomTag('<meta name="twitter:card" content="summary_large_image" />');
    $doc->addCustomTag(
        '<meta name="twitter:title" content="' . htmlspecialchars($this->item->title, ENT_QUOTES, "UTF-8") . '" />',
    );
    if (!empty($ogDescription)) {
        $doc->addCustomTag(
            '<meta name="twitter:description" content="' .
                htmlspecialchars($ogDescription, ENT_QUOTES, "UTF-8") .
                '" />',
        );
    }
    $doc->addCustomTag(
        '<meta name="twitter:url" content="' . htmlspecialchars($canonical, ENT_QUOTES, "UTF-8") . '" />',
    );

    // Robots meta
    $doc->setMetaData("robots", "index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1");

    // Структурированные данные для статьи (расширенная схема)
    $schemaDescription = $doc->getMetaData("description");
    $schemaLang = $this->item->language ?: $doc->getLanguage();
    $schemaLang = $schemaLang ?: "ru-RU";
    if ($schemaLang === "*") {
        $schemaLang = "ru-RU";
    }
    $keywords = [];
    $tagIds = [];
    if (!empty($this->item->tags->itemTags)) {
        foreach ($this->item->tags->itemTags as $tg) {
            if (!empty($tg->title)) {
                $keywords[] = (string) $tg->title;
            }
            if (!empty($tg->tag_id)) {
                $tagIds[] = (int) $tg->tag_id;
            }
        }
    }

    $relatedData = CapitalcraftRelatedHelper::getRelatedForArticle($this->item, $tagIds);

    $faqEntities = [];
    if (!empty($relatedData["faq"])) {
        foreach ($relatedData["faq"] as $faqItem) {
            $questionText = trim((string) ($faqItem["title"] ?? ""));
            $answerRaw = trim((string) ($faqItem["excerpt"] ?? ""));
            $answerText = trim(strip_tags($answerRaw));

            if ($questionText !== "" && $answerText !== "") {
                $faqEntities[] = [
                    "@type" => "Question",
                    "name" => $questionText,
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $answerText,
                    ],
                ];
            }
        }
    }

    $articleSection = "";
    if (!empty($this->item->category_title)) {
        $articleSection = (string) $this->item->category_title;
    }

    $wordCount = $bodyText !== "" ? str_word_count($bodyText, 0, "А-Яа-яЁё") : 0;
    // Лимитируем articleBody, чтобы не раздувать JSON-LD (до ~10k символов)
    $articleBodyLimited = $bodyText !== "" ? mb_substr($bodyText, 0, 10000, "UTF-8") : "";

    $published = $this->item->publish_up ?: $this->item->created;
    $publishedIso = $published ? HTMLHelper::_("date", $published, DATE_ATOM) : null;
    $modifiedIso = $this->item->modified ? HTMLHelper::_("date", $this->item->modified, DATE_ATOM) : null;

    // Формируем список хлебных крошек для JSON-LD
    $breadcrumbItems = [];
    $breadcrumbPosition = 1;
    $app = Factory::getApplication();
    $menu = $app->getMenu();
    $defaultMenu = $menu ? $menu->getDefault($app->getLanguage()->getTag()) : null;

    if ($defaultMenu) {
        $homeName = $defaultMenu->title ?: Text::_("JGLOBAL_HOME");
        $breadcrumbItems[] = [
            "@type" => "ListItem",
            "position" => $breadcrumbPosition++,
            "name" => $homeName,
            "item" => Uri::root(),
        ];
    }

    $pathway = $app->getPathway();
    $pathwayItems = $pathway ? $pathway->getPathway() : [];

    foreach ($pathwayItems as $crumb) {
        $crumbName = $crumb->title ?? ($crumb->name ?? "");
        if ($crumbName === "") {
            continue;
        }

        $crumbLink = $crumb->link ?? "";
        if ($crumbLink !== "") {
            $crumbLink = Route::_($crumbLink);
            if ($crumbLink && strpos($crumbLink, "http") !== 0) {
                $crumbLink = Uri::root() . ltrim($crumbLink, "/");
            }
        }

        $breadcrumbItems[] = [
            "@type" => "ListItem",
            "position" => $breadcrumbPosition++,
            "name" => $crumbName,
            "item" => $crumbLink ?: null,
        ];
    }

    $lastBreadcrumb = $breadcrumbItems ? end($breadcrumbItems) : null;
    if (!$lastBreadcrumb || ($lastBreadcrumb["name"] ?? "") !== $this->item->title) {
        $breadcrumbItems[] = [
            "@type" => "ListItem",
            "position" => $breadcrumbPosition,
            "name" => $this->item->title,
            "item" => $canonical,
        ];
    }

    $breadcrumbItems = array_values(
        array_filter($breadcrumbItems, function ($item) {
            return !empty($item["name"]);
        }),
    );

    $breadcrumbId = $defaultBreadcrumbId ?: Uri::root() . "#/schema/BreadcrumbList/article-" . (int) $this->item->id;

    $siteRoot = rtrim(Uri::root(), "/");
    $siteUrl = $siteRoot . "/";
    $organizationId = $siteRoot . "#/schema/Organization/base";
    $websiteId = $siteRoot . "#/schema/WebSite/base";
    $logoId = $siteRoot . "#/schema/ImageObject/logo";
    $logoUrl = Uri::root() . "templates/capitalcraft/images/logo_square.svg";
    $logoWidth = 512;
    $logoHeight = 512;
    $webPageId = $canonical . "#webpage";
    $articleId = $canonical . "#article";

    $organizationSchema = [
        "@type" => "Organization",
        "@id" => $organizationId,
        "name" => "Capital Craft",
        "url" => $siteUrl,
        "logo" => [
            "@type" => "ImageObject",
            "@id" => $logoId,
            "url" => $logoUrl,
            "contentUrl" => $logoUrl,
            "width" => $logoWidth,
            "height" => $logoHeight,
        ],
        "image" => ["@id" => $logoId],
        "sameAs" => ["https://t.me/capital_craft_official", "https://dzen.ru/capital_craft_official"],
    ];

    $websiteSchema = [
        "@type" => "WebSite",
        "@id" => $websiteId,
        "url" => $siteUrl,
        "name" => "CAPITAL CRAFT",
        "publisher" => ["@id" => $organizationId],
    ];

    $webPageSchema = [
        "@type" => "WebPage",
        "@id" => $webPageId,
        "url" => $canonical,
        "name" => $doc->getTitle(),
        "description" => $schemaDescription,
        "inLanguage" => $schemaLang,
        "isPartOf" => ["@id" => $websiteId],
        "about" => ["@id" => $organizationId],
        "breadcrumb" => !empty($breadcrumbItems) ? ["@id" => $breadcrumbId] : null,
    ];

    $webPageSchema = array_filter($webPageSchema, static fn($value) => $value !== null && $value !== "");

    $breadcrumbSchema = null;
    if (!empty($breadcrumbItems)) {
        $breadcrumbSchema = [
            "@type" => "BreadcrumbList",
            "@id" => $breadcrumbId,
            "itemListElement" => $breadcrumbItems,
        ];
    }

    $articleImageId = $articleId . "-image";
    $articleImageObject = [
        "@type" => "ImageObject",
        "@id" => $articleImageId,
        "url" => $ogImage,
        "contentUrl" => $ogImage,
        "caption" => $ogImageAlt,
        "width" => $ogImageWidth && $ogImageWidth > 0 ? $ogImageWidth : null,
        "height" => $ogImageHeight && $ogImageHeight > 0 ? $ogImageHeight : null,
    ];

    $articleImageObject = array_filter($articleImageObject, static fn($value) => !in_array($value, [null, ""], true));

    $articleSchema = [
        "@type" => ["BlogPosting", "Article"],
        "@id" => $articleId,
        "headline" => $this->item->title,
        "description" => $schemaDescription,
        "url" => $canonical,
        "mainEntityOfPage" => ["@id" => $webPageId],
        "isPartOf" => ["@id" => $websiteId],
        "image" => $articleImageObject,
        "inLanguage" => $schemaLang,
        "keywords" => !empty($keywords) ? implode(", ", $keywords) : null,
        "articleSection" => $articleSection ?: null,
        "wordCount" => $wordCount ?: null,
        "isAccessibleForFree" => true,
        "datePublished" => $publishedIso,
        "dateModified" => $modifiedIso,
        "articleBody" => $articleBodyLimited !== "" ? $articleBodyLimited : null,
        "author" => [
            "@type" => "Organization",
            "@id" => $organizationId,
            "name" => "Capital Craft",
        ],
        "publisher" => [
            "@type" => "Organization",
            "@id" => $organizationId,
            "name" => "Capital Craft",
            "logo" => [
                "@id" => $logoId,
                "url" => $logoUrl,
                "contentUrl" => $logoUrl,
                "width" => $logoWidth,
                "height" => $logoHeight,
            ],
        ],
    ];

    $articleSchema = array_filter($articleSchema, static fn($value) => !in_array($value, [null, ""], true));

    $graph = [$organizationSchema, $websiteSchema, $webPageSchema];

    if ($breadcrumbSchema) {
        $graph[] = $breadcrumbSchema;
    }

    if (!empty($faqEntities)) {
        $graph[] = [
            "@type" => "FAQPage",
            "@id" => $canonical . "#faq",
            "inLanguage" => $schemaLang,
            "mainEntity" => $faqEntities,
        ];
    }

    $graph[] = $articleSchema;

    $doc->addCustomTag(
        '<script type="application/ld+json">' .
            json_encode(
                [
                    "@context" => "https://schema.org",
                    "@graph" => $graph,
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ) .
            "</script>",
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
            <time class="blog-card__date" datetime="<?php echo HTMLHelper::_("date", $dateValue, "c"); ?>">
              <?php echo HTMLHelper::_("date", $dateValue, Text::_("DATE_FORMAT_LC3")); ?>
            </time>
          <?php endif; ?>

          <?php if (!empty($this->item->tags->itemTags)): ?>
            <?php
            // Получаем blogRoute один раз вне цикла тегов
            $menu = Factory::getApplication()->getMenu();
            $blogItem = $menu->getItems("alias", "blog", true);
            $blogRouteBase = $blogItem ? Route::_("index.php?Itemid=" . (int) $blogItem->id) : Route::_("index.php");
            $blogRouteSep = strpos($blogRouteBase, "?") === false ? "?" : "&";
            ?>
            <ul class="blog-card__tags">
              <?php foreach ($this->item->tags->itemTags as $tag): ?>
                <li class="blog-card__tag">
                  <?php $tagHref = $blogRouteBase . $blogRouteSep . "tag=" . rawurlencode($tag->alias ?? ""); ?>
                  <a href="<?php echo $tagHref; ?>" class="blog-card__tag-link">#<?php echo $this->escape(
    $tag->title,
); ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
        
        <?php
        // Right-side illustration (from article images)
        $mainImg =
            $articleImages && !empty($articleImages->image_fulltext)
                ? $articleImages->image_fulltext
                : ($articleImages && !empty($articleImages->image_intro)
                    ? $articleImages->image_intro
                    : "");
        $mainAlt =
            $articleImages && !empty($articleImages->image_fulltext_alt)
                ? $articleImages->image_fulltext_alt
                : ($articleImages && !empty($articleImages->image_intro_alt)
                    ? $articleImages->image_intro_alt
                    : "");
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
                <img
                  src="<?php echo htmlspecialchars($mainImg, ENT_QUOTES, "UTF-8"); ?>"
                  alt="<?php echo htmlspecialchars($mainAlt, ENT_QUOTES, "UTF-8"); ?>"
                  loading="lazy"
                  decoding="async"
                >
              </figure>
            <?php endif; ?>

            <?php $hasRelated = !empty($relatedData["articles"]) || !empty($relatedData["faq"]); ?>

            <?php if ($hasRelated): ?>
              <?php $relatedHeadingId = "article-related-" . (int) $this->item->id; ?>
              <aside class="article__related-wrap" aria-labelledby="<?php echo $relatedHeadingId; ?>">
                <div class="article__related-header">
                  <h2 class="article__related-title" id="<?php echo $relatedHeadingId; ?>">Читайте также</h2>
                  <?php if (!empty($relatedData["heading_tags"])): ?>
                    <div class="article__related-tags">
                      <?php foreach ($relatedData["heading_tags"] as $tagInfo): ?>
                        <?php
                        $safeTitle = htmlspecialchars((string) $tagInfo["title"], ENT_QUOTES, "UTF-8");
                        $safeTitle = preg_replace("/\s+/", "&nbsp;", $safeTitle);
                        ?>
                        <span class="article__related-tag">#<?php echo $safeTitle; ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="article__related-scroll">
                  <div class="article__related-block">
                    <ul class="article__related-list">
                      <?php if (!empty($relatedData["articles"])): ?>
                        <?php foreach ($relatedData["articles"] as $rel): ?>
                          <li class="article__related-item">
                            <a class="article__related-link" href="<?php echo htmlspecialchars(
                                $rel["link"],
                                ENT_QUOTES,
                                "UTF-8",
                            ); ?>">
                              <div class="article__related-link-title">
                                <?php echo htmlspecialchars($rel["title"], ENT_QUOTES, "UTF-8"); ?>
                              </div>
                              <?php if (!empty($rel["excerpt"])): ?>
                                <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                    $rel["excerpt"],
                                    ENT_QUOTES,
                                    "UTF-8",
                                ); ?></div>
                              <?php endif; ?>
                            </a>
                            <?php if (!empty($rel["publish_up"])): ?>
                              <time class="article__related-date" datetime="<?php echo HTMLHelper::_(
                                  "date",
                                  $rel["publish_up"],
                                  "c",
                              ); ?>">
                                <?php echo HTMLHelper::_("date", $rel["publish_up"], Text::_("DATE_FORMAT_LC3")); ?>
                              </time>
                            <?php endif; ?>
                          </li>
                        <?php endforeach; ?>
                      <?php endif; ?>

                      <?php if (!empty($relatedData["faq"])): ?>
                        <?php foreach ($relatedData["faq"] as $fq): ?>
                          <li class="article__related-item">
                            <a class="article__related-link" href="<?php echo htmlspecialchars(
                                $fq["link"],
                                ENT_QUOTES,
                                "UTF-8",
                            ); ?>">
                              <div class="article__related-link-title">
                                <?php echo htmlspecialchars($fq["title"], ENT_QUOTES, "UTF-8"); ?>
                              </div>
                              <?php if (!empty($fq["excerpt"])): ?>
                                <div class="article__related-excerpt"><?php echo htmlspecialchars(
                                    $fq["excerpt"],
                                    ENT_QUOTES,
                                    "UTF-8",
                                ); ?></div>
                              <?php endif; ?>
                            </a>
                            <?php if (!empty($fq["publish_up"])): ?>
                              <time class="article__related-date" datetime="<?php echo HTMLHelper::_(
                                  "date",
                                  $fq["publish_up"],
                                  "c",
                              ); ?>">
                                <?php echo HTMLHelper::_("date", $fq["publish_up"], Text::_("DATE_FORMAT_LC3")); ?>
                              </time>
                            <?php endif; ?>
                          </li>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>
              </aside>
            <?php endif; ?>
          </div>
        </div>
    </div>
    </section>
    <?php
}
?>
