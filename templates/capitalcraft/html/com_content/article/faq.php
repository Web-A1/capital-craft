<?php defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

require_once JPATH_SITE . "/templates/capitalcraft/helpers/FaqHelper.php";
require_once JPATH_SITE . "/templates/capitalcraft/helpers/SeoHelper.php";

$doc = Factory::getDocument();
$app = Factory::getApplication();

// Параметр фильтра по тегу (?tag=alias|id)
$input = $app->getInput();
$tagParamRaw = $input->getString("tag", "");
$tagParam = trim($tagParamRaw);

// Редирект со старых алиасов тегов на новые (SEO‑безопасно)
$aliasRedirects = [
    "real-setate" => "real-estate",
];

$currentAliasLower = strtolower($tagParam);
if ($currentAliasLower !== "" && isset($aliasRedirects[$currentAliasLower])) {
    $newAlias = $aliasRedirects[$currentAliasLower];
    $isAjax = strtolower($app->input->server->getString("HTTP_X_REQUESTED_WITH", "")) === "xmlhttprequest";

    if (!$isAjax) {
        // 301 на корректный URL с заменой параметра tag
        $uri = Uri::getInstance();
        $uri->setVar("tag", $newAlias);
        $uri->setFragment("");
        $app->redirect($uri->toString(), 301);
        return;
    }

    // Для AJAX-запросов просто используем новый алиас без редиректа
    $tagParam = $newAlias;
}

$faqData = CapitalcraftFaqHelper::getFaqPageData($tagParam);
$faqItems = $faqData["items"];
$faqAllTags = $faqData["allTags"];
$selectedAlias = $faqData["selectedAlias"];
$faqBaseRoute = CapitalcraftFaqHelper::getFaqRoute();

$canonicalUrl = CapitalcraftSeoHelper::buildCanonical(["tag"]);
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
    $question = [
        "@type" => "Question",
        "position" => $index + 1,
        "name" => (string) ($item["q"] ?? ""),
    ];

    $answer = [
        "@type" => "Answer",
        "text" => (string) ($item["answer_text"] ?? ""),
        "upvoteCount" => 0,
    ];

    $publishUp = (string) ($item["publish_up"] ?? "");

    if ($publishUp !== "") {
        try {
            $answer["dateCreated"] = Factory::getDate($publishUp)->format(DATE_ATOM);
        } catch (\Exception $e) {
            // Игнорируем некорректную дату, чтобы не ломать схему
        }
    }

    if (!empty($item["id"])) {
        $answer["url"] = CapitalcraftFaqHelper::buildFaqLink((int) $item["id"]);
    }

    $question["acceptedAnswer"] = $answer;

    $faqSchema["mainEntity"][] = $question;
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
?>

<section class="faq frame section-with-divider" data-faq-base="<?= htmlspecialchars(
    $faqBaseRoute,
    ENT_QUOTES,
    "UTF-8",
) ?>">
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
                        : ""; ?>" href="<?= htmlspecialchars($faqBaseRoute, ENT_QUOTES, "UTF-8") ?>">Все вопросы</a>
                  </li>
                  <?php foreach ($faqAllTags as $tg): ?>
                    <?php $alias = strtolower($tg->alias); ?>
                    <li class="faq-tags__tag">
                      <?php $tagHref = CapitalcraftFaqHelper::getFaqRoute(["tag" => $tg->alias]); ?>
                      <a class="faq-tags__link<?php echo $activeTagAlias === $alias
                          ? " is-active"
                          : ""; ?>" href="<?= htmlspecialchars(
                              $tagHref,
                              ENT_QUOTES,
                              "UTF-8",
                          ) ?>">#<?php echo htmlspecialchars($tg->title, ENT_QUOTES, "UTF-8"); ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </nav>
            <?php endif; ?>
            <div class="visually-hidden" id="faq-filter-status" role="status" aria-live="polite" aria-atomic="true"></div>
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
                    <div class="faq__item" id="faq-q-<?php echo (int) ($item["id"] ?? 0); ?>">
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
                            <?php echo $item["answer_html"] ?? ""; ?>
                        </div>
                        <?php
                        $primaryTag = $item["primary_tag"] ?? null;
                    if (empty($primaryTag) && !empty($item["tags"])) {
                        $primaryTag = $item["tags"][0];
                    }
                    ?>
                        <?php if (!empty($primaryTag)): ?>
                            <?php $primaryTagLink = CapitalcraftFaqHelper::getFaqRoute([
                            "tag" => $primaryTag["alias"],
                        ]); ?>
                            <a class="faq__tag-chip" href="<?= htmlspecialchars(
                                $primaryTagLink,
                                ENT_QUOTES,
                                "UTF-8",
                            ) ?>">#<?php echo htmlspecialchars($primaryTag["title"], ENT_QUOTES, "UTF-8"); ?></a>
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
