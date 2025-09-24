<?php defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
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

$updatedLabel = $translate("TPL_CAPITALCRAFT_LEGAL_UPDATED", "Обновлено");
$tocTitle = $translate("TPL_CAPITALCRAFT_LEGAL_TOC_TITLE", "Содержание");
$tocAriaLabel = $translate("TPL_CAPITALCRAFT_LEGAL_TOC_ARIA", "Оглавление документа");
$pdfDefaultLabel = $translate("TPL_CAPITALCRAFT_LEGAL_DOWNLOAD_PDF", "Скачать PDF");
$noticeTitle = $translate("TPL_CAPITALCRAFT_LEGAL_NOTICE_TITLE", "Важно");

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

$articleHtml = (string) ($item->text ?? "");
$articleHtml = trim($articleHtml);
$tocItems = [];

if ($articleHtml !== "" && class_exists("DOMDocument")) {
    $libxmlPreviousState = libxml_use_internal_errors(true);
    $dom = new DOMDocument("1.0", "UTF-8");
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $articleHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if ($loaded) {
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//h2 | //h3");
        $usedIds = [];

        if ($nodes !== false) {
            /** @var DOMElement $node */
            foreach ($nodes as $index => $node) {
                if (!($node instanceof DOMElement)) {
                    continue;
                }

                $level = (int) substr($node->tagName, 1);
                $textContent = trim(preg_replace("/\s+/u", " ", $node->textContent));

                if ($textContent === "") {
                    continue;
                }

                $id = $node->getAttribute("id");

                if ($id === "") {
                    $baseId = OutputFilter::stringURLSafe($textContent);
                    $baseId = $baseId !== "" ? $baseId : "section-" . ($index + 1);
                    $uniqueId = $baseId;
                    $counter = 2;

                    while (isset($usedIds[$uniqueId])) {
                        $uniqueId = $baseId . "-" . $counter;
                        $counter++;
                    }

                    $id = $uniqueId;
                    $node->setAttribute("id", $id);
                }

                $usedIds[$id] = true;

                $tocItems[] = [
                    "id" => $id,
                    "title" => $textContent,
                    "level" => $level,
                ];
            }
        }

        if (!empty($tocItems)) {
            $processedHtml = $dom->saveHTML();
            $articleHtml = preg_replace("/^<\?xml.*?\?>/u", "", $processedHtml ?? "");
        }
    }

    libxml_clear_errors();
    libxml_use_internal_errors($libxmlPreviousState);
}

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

$beforeDisplayTitle = $item->event->beforeDisplayTitle ?? "";
$afterDisplayTitle = $item->event->afterDisplayTitle ?? "";
$beforeDisplayContent = $item->event->beforeDisplayContent ?? "";
$afterDisplayContent = $item->event->afterDisplayContent ?? "";
?>

<section class="legal frame section-with-divider">
    <div class="legal__container container">
        <?php if ($params->get("show_title")): ?>
            <?php echo $beforeDisplayTitle; ?>
            <header class="legal__header">
                <h1 class="legal__title"><?php echo $this->escape($item->title); ?></h1>
                <?php if ($updatedDateDisplay !== ""): ?>
                    <div class="legal__meta">
                        <span class="legal__meta-label"><?php echo htmlspecialchars(
                            $updatedLabel,
                            ENT_QUOTES,
                            "UTF-8",
                        ); ?></span>
                        <time class="legal__meta-date" datetime="<?php echo htmlspecialchars(
                            $updatedDateIso,
                            ENT_QUOTES,
                            "UTF-8",
                        ); ?>">
                            <?php echo htmlspecialchars($updatedDateDisplay, ENT_QUOTES, "UTF-8"); ?>
                        </time>
                    </div>
                <?php endif; ?>
            </header>
            <?php echo $afterDisplayTitle; ?>
        <?php else: ?>
            <?php echo $beforeDisplayTitle; ?>
            <?php echo $afterDisplayTitle; ?>
            <?php if ($updatedDateDisplay !== ""): ?>
                <div class="legal__meta legal__meta--standalone">
                    <span class="legal__meta-label"><?php echo htmlspecialchars(
                        $updatedLabel,
                        ENT_QUOTES,
                        "UTF-8",
                    ); ?></span>
                    <time class="legal__meta-date" datetime="<?php echo htmlspecialchars(
                        $updatedDateIso,
                        ENT_QUOTES,
                        "UTF-8",
                    ); ?>">
                        <?php echo htmlspecialchars($updatedDateDisplay, ENT_QUOTES, "UTF-8"); ?>
                    </time>
                </div>
            <?php endif; ?>
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

        <?php if (!empty($tocItems)): ?>
            <nav class="legal__toc" aria-label="<?php echo htmlspecialchars($tocAriaLabel, ENT_QUOTES, "UTF-8"); ?>">
                <div class="legal__toc-title" id="legal-toc-title"><?php echo htmlspecialchars(
                    $tocTitle,
                    ENT_QUOTES,
                    "UTF-8",
                ); ?></div>
                <ol class="legal__toc-list">
                    <?php foreach ($tocItems as $tocItem): ?>
                        <li class="legal__toc-item legal__toc-item--level-<?php echo (int) $tocItem["level"]; ?>">
                            <a class="legal__toc-link" href="#<?php echo htmlspecialchars(
                                $tocItem["id"],
                                ENT_QUOTES,
                                "UTF-8",
                            ); ?>">
                                <?php echo htmlspecialchars($tocItem["title"], ENT_QUOTES, "UTF-8"); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

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
</section>
