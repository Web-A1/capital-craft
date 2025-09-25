<?php
defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */

$translate = static function (string $key, string $fallback): string {
    $text = Text::_($key);

    return $text === $key ? $fallback : $text;
};

$sectionSubtitle = $translate("TPL_CAPITALCRAFT_LEGAL_SUBTITLE", "Юридическая информация");
$illustrationDefaultAlt = $translate(
    "TPL_CAPITALCRAFT_LEGAL_IMAGE_ALT",
    "Иллюстрация юридического раздела Capital Craft",
);

$category = $this->category;

// Prepare category description with content plugins
if (is_object($category)) {
    $category->text = $category->description ?? "";
    $app = Factory::getApplication();
    $app->triggerEvent("onContentPrepare", [
        ($category->extension ?? "com_content") . ".categories",
        &$category,
        &$this->params,
        0,
    ]);
    $category->description = $category->text;
}

$defaultHeading = $translate("TPL_CAPITALCRAFT_LEGAL_HEADING", "Правовые основы сайта Capital Craft");
$pageHeading = $defaultHeading;

$defaultIllustration = "/templates/capitalcraft/images/legal/legal.webp";
$illustrationSrc = $defaultIllustration;
$illustrationAlt = $illustrationDefaultAlt;

if (is_object($category) && method_exists($category, "getParams")) {
    $categoryParams = $category->getParams();

    if ($categoryParams) {
        $imageSrc = trim((string) $categoryParams->get("image", ""));
        $imageAlt = trim((string) $categoryParams->get("image_alt", ""));

        if ($imageSrc !== "") {
            $illustrationSrc = $imageSrc;
        }

        if ($imageAlt !== "") {
            $illustrationAlt = $imageAlt;
        }
    }
}

$getItemDate = static function ($item): array {
    $dateFields = [$item->modified ?? "", $item->publish_up ?? "", $item->created ?? ""];

    foreach ($dateFields as $value) {
        if ($value === "" || $value === "0000-00-00 00:00:00") {
            continue;
        }

        try {
            $date = Factory::getDate($value);
            $iso = $date->format(DATE_ATOM, true);
            $display = HTMLHelper::_("date", $value, Text::_("DATE_FORMAT_LC3"));

            return [$iso, $display];
        } catch (\Throwable $exception) {
            continue;
        }
    }

    return ["", ""];
};

$collectItems = static function (array $groups): array {
    $items = [];

    foreach ($groups as $group) {
        foreach ($group as $item) {
            if (!is_object($item)) {
                continue;
            }

            $items[] = $item;
        }
    }

    return $items;
};

$allItems = $collectItems([$this->lead_items ?? [], $this->intro_items ?? [], $this->link_items ?? []]);
?>

<section class="legal legal--category frame section-with-divider">
    <div class="legal__container container">
        <header class="legal__intro">
            <div class="legal__intro-text">
                <p class="legal__subtitle"><?php echo htmlspecialchars($sectionSubtitle, ENT_QUOTES, "UTF-8"); ?></p>
                <?php if ($pageHeading !== ""): ?>
                    <h1 class="legal__title"><?php echo htmlspecialchars($pageHeading, ENT_QUOTES, "UTF-8"); ?></h1>
                <?php endif; ?>
                <?php if (!empty($category->description) && $this->params->get("show_description")): ?>
                    <div class="legal__description">
                        <?php echo $category->description; ?>
                    </div>
                <?php endif; ?>
            </div>

            <figure class="legal__illustration">
                <img
                    src="<?php echo htmlspecialchars($illustrationSrc, ENT_QUOTES, "UTF-8"); ?>"
                    alt="<?php echo htmlspecialchars($illustrationAlt, ENT_QUOTES, "UTF-8"); ?>"
                    loading="lazy"
                    decoding="async"
                >
            </figure>
        </header>

        <div class="legal__layout">
            <div class="legal__main">
                <?php if (empty($allItems)): ?>
                    <?php if ($this->params->get("show_no_articles", 1)): ?>
                        <div class="alert alert-info"><?php echo Text::_("COM_CONTENT_NO_ARTICLES"); ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="legal__list" aria-label="Список юридических документов">
                        <?php foreach ($allItems as $item): ?>
                            <?php
                            $link = Route::_(
                                ContentRouteHelper::getArticleRoute($item->slug, $item->catid, $item->language),
                            );
                            [$dateIso, $dateDisplay] = $getItemDate($item);
                            $introtext = trim((string) ($item->introtext ?? ""));
                            if ($introtext !== "") {
                                $introtext = preg_replace("/\s+/u", " ", strip_tags($introtext));
                                $introtext = HTMLHelper::_("string.truncate", $introtext, 220, true, false);
                            }
                            ?>
                            <article class="legal-card">
                                <a class="legal-card__body" href="<?php echo htmlspecialchars(
                                    $link,
                                    ENT_QUOTES,
                                    "UTF-8",
                                ); ?>">
                                    <div class="legal-card__header">
                                        <h2 class="legal-card__title"><?php echo htmlspecialchars(
                                            $item->title ?? "",
                                            ENT_QUOTES,
                                            "UTF-8",
                                        ); ?></h2>
                                    </div>

                                    <?php if ($introtext !== ""): ?>
                                        <p class="legal-card__intro"><?php echo htmlspecialchars(
                                            $introtext,
                                            ENT_QUOTES,
                                            "UTF-8",
                                        ); ?></p>
                                    <?php endif; ?>

                                    <?php if ($dateDisplay !== ""): ?>
                                        <div class="legal-card__meta">
                                            <time 
                                                class="legal-card__meta-date" 
                                                datetime="<?php echo htmlspecialchars(
                                                    $dateIso,
                                                    ENT_QUOTES,
                                                    "UTF-8",
                                                ); ?>"
                                                aria-label="Дата обновления: <?php echo htmlspecialchars(
                                                    $dateDisplay,
                                                    ENT_QUOTES,
                                                    "UTF-8",
                                                ); ?>"
                                                title="Обновлено <?php echo htmlspecialchars(
                                                    $dateDisplay,
                                                    ENT_QUOTES,
                                                    "UTF-8",
                                                ); ?>"
                                            >
                                                <?php echo htmlspecialchars($dateDisplay, ENT_QUOTES, "UTF-8"); ?>
                                            </time>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
