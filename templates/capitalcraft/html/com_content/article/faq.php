<?php defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once JPATH_SITE . '/templates/capitalcraft/helpers/FaqHelper.php';
require_once JPATH_SITE . '/templates/capitalcraft/helpers/SeoHelper.php';

$doc = Factory::getDocument();
$app = Factory::getApplication();

// Параметр фильтра по тегу (?tag=alias|id)
$input = $app->getInput();
$tagParamRaw = $input->getString("tag", "");
$tagParam = trim($tagParamRaw);

$faqData = CapitalcraftFaqHelper::getFaqPageData($tagParam);
$faqItems = $faqData['items'];
$faqAllTags = $faqData['allTags'];
$selectedAlias = $faqData['selectedAlias'];

$canonicalUrl = CapitalcraftSeoHelper::buildCanonical(['tag']);
CapitalcraftSeoHelper::addCanonicalLink($canonicalUrl);

// Фолбэк отключён: если в БД нет данных, не выводим вопросы

// Улучшенные структурированные данные JSON-LD для FAQ
$faqSchema = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "name" => "Часто задаваемые вопросы о привлечении капитала",
    "description" =>
        "Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса от экспертов Capital Craft",
    "url" => $canonicalUrl,
    "mainEntity" => [],
    "about" => [
        "@type" => "Organization",
        "name" => "Capital Craft",
        "description" => "Бутиковое агентство инвестиционных решений",
    ],
];

foreach ($faqItems as $index => $item) {
    $faqSchema["mainEntity"][] = [
        "@type" => "Question",
        "position" => $index + 1,
        "name" => (string) ($item["q"] ?? ""),
        "acceptedAnswer" => [
            "@type" => "Answer",
            "text" => (string) ($item["a"] ?? ""),
            "dateCreated" => date("c"),
            "upvoteCount" => 1,
        ],
    ];
}

$doc->addCustomTag(
    '<script type="application/ld+json">' .
        json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
        "</script>",
);

// BreadcrumbList schema
$breadcrumbSchema = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Главная",
            "item" => Uri::root(),
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "FAQ",
            "item" => $canonicalUrl,
        ],
    ],
];

$doc->addCustomTag(
    '<script type="application/ld+json">' .
        json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
        "</script>",
);

// Organization schema для Capital Craft
$orgSchema = [
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "name" => "Capital Craft",
    "alternateName" => "Capital-craft",
    "description" =>
        "Бутиковое агентство инвестиционных решений, специализирующееся на привлечении финансирования для бизнеса",
    "url" => Uri::root(),
    "logo" => [
        "@type" => "ImageObject",
        "url" => Uri::root() . "templates/capitalcraft/images/logo_black.svg",
        "width" => 200,
        "height" => 60,
    ],
    "image" => Uri::root() . "templates/capitalcraft/images/faq/faq_hand.webp",
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "Варшавское шоссе 33, стр 1",
        "addressLocality" => "Москва",
        "postalCode" => "117105",
        "addressCountry" => "RU",
    ],
    "contactPoint" => [
        "@type" => "ContactPoint",
        "telephone" => "+7 (499) 325-68-26",
        "contactType" => "customer service",
        "email" => "info@capital-craft.ru",
        "availableLanguage" => ["Russian", "English"],
    ],
    "sameAs" => ["https://t.me/capital_craft1", "https://dzen.ru/capital_craft1"],
    "foundingDate" => "2020",
    "areaServed" => "RU",
    "hasOfferCatalog" => [
        "@type" => "OfferCatalog",
        "name" => "Инвестиционные решения и привлечение капитала",
        "description" => "Услуги по привлечению капитала и инвестиционным решениям",
    ],
];

$doc->addCustomTag(
    '<script type="application/ld+json">' .
        json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
        "</script>",
);

// WebPage schema
$webPageSchema = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "name" => "Часто задаваемые вопросы - Capital Craft",
    "description" => "Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса",
    "url" => $canonicalUrl,
    "isPartOf" => [
        "@type" => "WebSite",
        "name" => "Capital Craft",
        "url" => Uri::root(),
    ],
    "about" => [
        "@type" => "Organization",
        "name" => "Capital Craft",
    ],
    "inLanguage" => "ru-RU",
    "breadcrumb" => [
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Главная",
            "item" => Uri::root(),
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => "FAQ",
                "item" => $canonicalUrl,
            ],
        ],
    ],
];

$doc->addCustomTag(
    '<script type="application/ld+json">' .
        json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
        "</script>",
);
?>

<section class="faq frame section-with-divider">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <h1 class="faq__subtitle">часто задаваемые вопросы</h1>
                <p class="faq__title">Сильные решения начинаются с вопросов</p>
            </div>
            <?php if (!empty($faqAllTags)): ?>
              <?php $activeTagAlias = $selectedAlias; ?>
              <nav class="faq__tags-nav" aria-label="Навигация по тегам FAQ">
                <ul class="faq-tags__cloud faq-tags__cloud--nowrap">
                  <li class="faq-tags__tag">
                    <a class="faq-tags__link<?php echo $activeTagAlias === ""
                        ? " is-active"
                        : ""; ?>" href="/faq">Все вопросы</a>
                  </li>
                  <?php foreach ($faqAllTags as $tg): ?>
                    <?php $alias = strtolower($tg->alias); ?>
                    <li class="faq-tags__tag">
                      <a class="faq-tags__link<?php echo $activeTagAlias === $alias
                          ? " is-active"
                          : ""; ?>" href="<?php echo Route::_(
                              "/faq?tag=" . rawurlencode($tg->alias),
                          ); ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, "UTF-8"); ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </nav>
            <?php endif; ?>
            <div class="faq__accordion" role="region" aria-label="Список часто задаваемых вопросов">
                <?php foreach ($faqItems as $index => $item): ?>
                    <?php
                    $aliases = [];
                    if (!empty($item["tags"])) {
                        foreach ($item["tags"] as $t) {
                            $aliases[] = strtolower($t["alias"]);
                        }
                    }
                    ?>
                    <div class="faq__item" id="faq-q-<?php echo (int) ($item["id"] ??
                        0); ?>" data-tags="<?php echo htmlspecialchars(
                            implode(" ", $aliases),
                            ENT_QUOTES,
                            "UTF-8",
                        ); ?>">
                        <button class="faq__question" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-<?php echo $index; ?>"
                                aria-label="Вопрос <?php echo $index + 1; ?>: <?php echo htmlspecialchars(
                                    $item["q"],
                                    ENT_QUOTES,
                                    "UTF-8",
                                ); ?>">
                            <span class="faq__text">
                                <?php echo htmlspecialchars($item["q"], ENT_QUOTES, "UTF-8"); ?>
                            </span>
                        </button>
                        <div class="faq__answer" 
                             id="faq-answer-<?php echo $index; ?>"
                             role="region" 
                             aria-label="Ответ на вопрос: <?php echo htmlspecialchars(
                                 $item["q"],
                                 ENT_QUOTES,
                                 "UTF-8",
                             ); ?>">
                            <?php echo htmlspecialchars($item["a"], ENT_QUOTES, "UTF-8"); ?>
                        </div>
                        <?php
                        $primaryTag = $item["primary_tag"] ?? null;
                    if (empty($primaryTag) && !empty($item["tags"])) {
                        $primaryTag = $item["tags"][0];
                    }
                    ?>
                        <?php if (!empty($primaryTag)): ?>
                            <a class="faq__tag-chip" href="<?php echo Route::_(
                                "/faq?tag=" . rawurlencode($primaryTag["alias"]),
                            ); ?>">#<?php echo htmlspecialchars($primaryTag["title"], ENT_QUOTES, "UTF-8"); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <figure class="faq__image">
            <img src="/templates/capitalcraft/images/faq/faq_hand.webp" 
                 alt="Часто задаваемые вопросы о привлечении капитала и инвестициях" 
                 loading="lazy"
                 width="351"
                 height="624"
                 decoding="async">
        </figure>
    </div>
</section>
