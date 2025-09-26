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

if ($illustrationWidth === null || $illustrationHeight === null) {
    $imagePath = (string) (parse_url($illustrationSrc, PHP_URL_PATH) ?? "");

    if ($imagePath !== "") {
        $filesystemPath = JPATH_ROOT . "/" . ltrim($imagePath, "/");

        if (is_file($filesystemPath)) {
            $size = @getimagesize($filesystemPath);

            if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
                $illustrationWidth = (int) $size[0];
                $illustrationHeight = (int) $size[1];
            }
        }
    }
}

$illustrationSizeAttributes = "";

if ($illustrationWidth !== null) {
    $illustrationSizeAttributes .= ' width="' . (int) $illustrationWidth . '"';
}

if ($illustrationHeight !== null) {
    $illustrationSizeAttributes .= ' height="' . (int) $illustrationHeight . '"';
}

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


            <?php echo $beforeDisplayContent; ?>

            <div class="legal__body">
                <?php echo $articleHtml; ?>
            </div>


            <?php echo $afterDisplayContent; ?>
        </div>

        <figure class="legal__image">
            <img
                src="<?php echo htmlspecialchars($illustrationSrc, ENT_QUOTES, "UTF-8"); ?>"
                alt="<?php echo htmlspecialchars($illustrationAlt, ENT_QUOTES, "UTF-8"); ?>"
                <?php echo $illustrationSizeAttributes; ?>
                loading="lazy"
                decoding="async"
            >
        </figure>
    </div>
</section>
