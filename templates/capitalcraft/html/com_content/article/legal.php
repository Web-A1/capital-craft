<?php defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once JPATH_SITE . "/templates/capitalcraft/helpers/SeoHelper.php";

$doc = Factory::getDocument();
$item = $this->item;
$params = $this->params;

$canonicalUrl = CapitalcraftSeoHelper::buildCanonical();
CapitalcraftSeoHelper::addCanonicalLink($canonicalUrl);

$translate = static function (string $key, string $fallback): string {
    $text = Text::_($key);

    return $text === $key ? $fallback : $text;
};

$pdfDefaultLabel = $translate("TPL_CAPITALCRAFT_LEGAL_DOWNLOAD_PDF", "Скачать PDF");
$noticeTitle = $translate("TPL_CAPITALCRAFT_LEGAL_NOTICE_TITLE", "Важно");
$sectionSubtitle = $translate("TPL_CAPITALCRAFT_LEGAL_SUBTITLE", "Юридическая информация");
$illustrationDefaultAlt = $translate(
    "TPL_CAPITALCRAFT_LEGAL_IMAGE_ALT",
    "Иллюстрация юридического раздела Capital Craft",
);

$language = !empty($item->language) && $item->language !== "*" ? $item->language : $doc->getLanguage();
$language = $language ?: "ru-RU";
$language = str_replace("_", "-", $language);

$legalType = "WebPage";
$articleAlias = strtolower((string) ($item->alias ?? ""));

if ($articleAlias !== "") {
    if (strpos($articleAlias, "privacy") !== false) {
        $legalType = "PrivacyPolicy";
    } elseif (strpos($articleAlias, "policy") !== false) {
        $legalType = "PrivacyPolicy";
    } elseif (strpos($articleAlias, "terms") !== false || strpos($articleAlias, "rules") !== false) {
        $legalType = "TermsOfService";
    }
}

$dateFields = [
    "modified" => $item->modified ?? "",
    "publish_up" => $item->publish_up ?? "",
    "created" => $item->created ?? "",
];

$updatedDateIso = "";
$updatedDateDisplay = "";

foreach ($dateFields as $dateValue) {
    if (!empty($dateValue) && $dateValue !== "0000-00-00 00:00:00") {
        try {
            $date = Factory::getDate($dateValue);
            $updatedDateIso = $date->format(DATE_ATOM, true);
            $updatedDateDisplay = HTMLHelper::_("date", $dateValue, Text::_("DATE_FORMAT_LC3"));
            break;
        } catch (\Throwable $exception) {
            // Ignore invalid date formats
        }
    }
}

$schemaData = [
    "@context" => "https://schema.org",
    "@type" => $legalType,
    "name" => (string) ($item->title ?? ""),
    "description" => !empty($item->metadesc) ? $item->metadesc : null,
    "url" => $canonicalUrl,
    "inLanguage" => $language,
    "datePublished" => null,
    "dateModified" => null,
    "publisher" => [
        "@type" => "Organization",
        "name" => "Capital Craft",
        "url" => Uri::root(),
        "logo" => [
            "@type" => "ImageObject",
            "url" => Uri::root() . "templates/capitalcraft/images/logo_black.svg",
        ],
    ],
];

if (!empty($item->publish_up) && $item->publish_up !== "0000-00-00 00:00:00") {
    try {
        $schemaData["datePublished"] = Factory::getDate($item->publish_up)->format(DATE_ATOM);
    } catch (\Throwable $exception) {
        $schemaData["datePublished"] = null;
    }
}

if ($updatedDateIso !== "") {
    $schemaData["dateModified"] = $updatedDateIso;
}

$schemaData = array_filter($schemaData, static fn($value) => $value !== null && $value !== "");

$doc->addCustomTag(
    '<script type="application/ld+json">' .
        json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        "</script>",
);

$articleHtml = trim((string) ($item->text ?? ""));

$pdfLinks = [];

if (!empty($item->urls)) {
    $urlsData = json_decode($item->urls, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($urlsData)) {
        $linkKeys = ["urla", "urlb", "urlc"];

        foreach ($linkKeys as $linkKey) {
            $url = trim((string) ($urlsData[$linkKey] ?? ""));

            if ($url === "") {
                continue;
            }

            $path = parse_url($url, PHP_URL_PATH);
            $extension = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));

            if ($extension !== "pdf") {
                continue;
            }

            $textKey = $linkKey . "text";
            $label = trim((string) ($urlsData[$textKey] ?? ""));
            $label = $label !== "" ? $label : $pdfDefaultLabel;

            $targetKey = $linkKey . "target";
            $target = trim((string) ($urlsData[$targetKey] ?? ""));

            $pdfLinks[] = [
                "url" => $url,
                "label" => $label,
                "target" => $target,
            ];
        }
    }
}

$legalNoticeHtml = "";

if (!empty($item->jcfields) && is_array($item->jcfields)) {
    foreach ($item->jcfields as $field) {
        if (!is_object($field)) {
            continue;
        }

        $alias = strtolower((string) ($field->alias ?? ($field->name ?? "")));
        $value = $field->value ?? "";

        if ($value === "") {
            continue;
        }

        if ($alias !== "" && (strpos($alias, "legal_notice") !== false || strpos($alias, "legal_alert") !== false)) {
            $legalNoticeHtml = HTMLHelper::_("content.prepare", $value, "", "com_fields.field");
            break;
        }
    }
}

if ($legalNoticeHtml === "" && !empty($item->attribs)) {
    $attribs = json_decode($item->attribs, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($attribs)) {
        $noticeValue = trim((string) ($attribs["legal_notice"] ?? ""));

        if ($noticeValue !== "") {
            $legalNoticeHtml = HTMLHelper::_("content.prepare", $noticeValue, "", "com_content.article");
        }
    }
}

$defaultIllustration = "/templates/capitalcraft/images/legal/legal.webp";
$illustrationSrc = "";
$illustrationAlt = "";
$useDefaultIllustration = false;

if (!empty($item->images)) {
    $imagesData = json_decode($item->images, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($imagesData)) {
        $illustrationSrc = trim((string) ($imagesData["image_fulltext"] ?? ""));

        if ($illustrationSrc === "") {
            $illustrationSrc = trim((string) ($imagesData["image_intro"] ?? ""));
        }

        $illustrationAlt = trim((string) ($imagesData["image_fulltext_alt"] ?? ""));

        if ($illustrationAlt === "") {
            $illustrationAlt = trim((string) ($imagesData["image_intro_alt"] ?? ""));
        }
    }
}

if ($illustrationSrc === "") {
    $illustrationSrc = $defaultIllustration;
    $useDefaultIllustration = true;
}

if ($illustrationAlt === "") {
    $illustrationAlt = $illustrationDefaultAlt;
}

$illustrationWidth = $useDefaultIllustration ? 351 : null;
$illustrationHeight = $useDefaultIllustration ? 624 : null;

$beforeDisplayTitle = $item->event->beforeDisplayTitle ?? "";
$afterDisplayTitle = $item->event->afterDisplayTitle ?? "";
$beforeDisplayContent = $item->event->beforeDisplayContent ?? "";
$afterDisplayContent = $item->event->afterDisplayContent ?? "";
?>

<section class="legal frame section-with-divider">
    <div class="legal__container container">
        <div class="legal__content">
            <header class="legal__intro">
                <div class="legal__intro-text">
                    <p class="legal__subtitle"><?php echo htmlspecialchars(
                        $sectionSubtitle,
                        ENT_QUOTES,
                        "UTF-8",
                    ); ?></p>
                    <?php echo $beforeDisplayTitle; ?>
                    <?php if ($params->get("show_title")): ?>
                        <h1 class="legal__title"><?php echo $this->escape($item->title); ?></h1>
                    <?php endif; ?>
                    <?php echo $afterDisplayTitle; ?>
                </div>
            </header>

            <?php if ($updatedDateDisplay !== ""): ?>
                <time class="legal__meta-date" datetime="<?php echo htmlspecialchars(
                    $updatedDateIso,
                    ENT_QUOTES,
                    "UTF-8",
                ); ?>">
                    <?php echo "Дата обновления: " . htmlspecialchars($updatedDateDisplay, ENT_QUOTES, "UTF-8"); ?>
                </time>
            <?php endif; ?>

            <?php if ($legalNoticeHtml !== ""): ?>
                <aside class="legal__notice" aria-label="<?php echo htmlspecialchars(
                    $noticeTitle,
                    ENT_QUOTES,
                    "UTF-8",
                ); ?>">
                    <div class="legal__notice-title"><?php echo htmlspecialchars(
                        $noticeTitle,
                        ENT_QUOTES,
                        "UTF-8",
                    ); ?></div>
                    <div class="legal__notice-content">
                        <?php echo $legalNoticeHtml; ?>
                    </div>
                </aside>
            <?php endif; ?>

            <?php echo $beforeDisplayContent; ?>

            <div class="legal__body">
                <?php echo $articleHtml; ?>
            </div>

            <?php if (!empty($pdfLinks)): ?>
                <div class="legal__downloads">
                    <?php foreach ($pdfLinks as $pdfLink): ?>
                        <?php
                        $targetAttr = "";
                        $relAttr = "";

                        if ($pdfLink["target"] !== "") {
                            $target = htmlspecialchars($pdfLink["target"], ENT_QUOTES, "UTF-8");
                            $targetAttr = ' target="' . $target . '"';

                            if ($pdfLink["target"] === "_blank") {
                                $relAttr = ' rel="noopener noreferrer"';
                            }
                        }
                        ?>
                        <a class="legal__download-link" href="<?php echo htmlspecialchars(
                            $pdfLink["url"],
                            ENT_QUOTES,
                            "UTF-8",
                        ); ?>"<?php echo $targetAttr . $relAttr; ?>>
                            <?php echo htmlspecialchars($pdfLink["label"], ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php echo $afterDisplayContent; ?>
        </div>

        <figure class="legal__image">
            <img
                src="<?php echo htmlspecialchars($illustrationSrc, ENT_QUOTES, "UTF-8"); ?>"
                alt="<?php echo htmlspecialchars($illustrationAlt, ENT_QUOTES, "UTF-8"); ?>"
                loading="lazy"
                decoding="async"
            >
        </figure>
    </div>
</section>
