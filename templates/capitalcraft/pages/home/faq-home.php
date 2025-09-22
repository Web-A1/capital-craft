<?php
defined("_JEXEC") or die();

require_once JPATH_SITE . "/templates/capitalcraft/helpers/FaqHelper.php";

$maxItems = 9;
$faqItems = CapitalcraftFaqHelper::getFeaturedFaq($maxItems);
$faqPageLink = CapitalcraftFaqHelper::getFaqRoute();

if (empty($faqItems)) {
    return;
}

if (count($faqItems) > 1) {
    foreach ($faqItems as $index => &$item) {
        if (!is_array($item)) {
            continue;
        }

        $item["_original_index"] = $index;
    }
    unset($item);

    usort($faqItems, static function (array $a, array $b): int {
        $questionA = $a["q"] ?? "";
        $questionB = $b["q"] ?? "";

        $lengthA = mb_strlen(is_string($questionA) ? $questionA : (string) $questionA, "UTF-8");
        $lengthB = mb_strlen(is_string($questionB) ? $questionB : (string) $questionB, "UTF-8");

        $lengthComparison = $lengthB <=> $lengthA;

        if ($lengthComparison !== 0) {
            return $lengthComparison;
        }

        return ($a["_original_index"] ?? 0) <=> ($b["_original_index"] ?? 0);
    });

    foreach ($faqItems as &$item) {
        if (is_array($item)) {
            unset($item["_original_index"]);
        }
    }
    unset($item);
}

$faqGroups = array_chunk($faqItems, 3);
?>

<section class="faq-home frame section-with-divider" id="faq">
    <div class="container faq-home__inner">
        <div class="faq-home__swiper swiper">
            <div class="swiper-wrapper">
                <?php foreach ($faqGroups as $group): ?>
                    <div class="faq-home__slide swiper-slide">
                        <ul>
                            <?php foreach ($group as $item): ?>
                                <?php
                                $questionText = htmlspecialchars($item["q"], ENT_QUOTES, "UTF-8");
                                $questionId = isset($item["id"]) ? (int) $item["id"] : 0;
                                $questionHref =
                                    $questionId > 0 ? CapitalcraftFaqHelper::buildFaqLink($questionId) : $faqPageLink;
                                ?>
                                <li class="faq-home__item">
                                    <a href="<?= htmlspecialchars(
                                        $questionHref,
                                        ENT_QUOTES,
                                        "UTF-8",
                                    ) ?>"><?= $questionText ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="faq-home__pagination swiper-pagination"></div>
        </div>

        <div class="faq-home__content">
            <div class="faq-home__title-block">
                <h2 class="faq-home__subtitle">Часто задаваемые вопросы</h2>
                <p class="faq-home__title">Сильные решения начинаются с вопросов</p>
            </div>
            <a href="<?= htmlspecialchars(
                $faqPageLink,
                ENT_QUOTES,
                "UTF-8",
            ) ?>" class="btn-main faq-home__btn faq-home__btn--desktop">
                <span>Вопросы - ответы</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                </svg>
            </a>
        </div>
        <a href="<?= htmlspecialchars(
            $faqPageLink,
            ENT_QUOTES,
            "UTF-8",
        ) ?>" class="btn-main faq-home__btn faq-home__btn--mobile">
            <span>Вопросы - ответы</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
            </svg>
        </a>
    </div>
</section>
