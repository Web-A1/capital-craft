<?php
defined("_JEXEC") or die();

$maxItems = 9;
$faqItems = [];

// Используем тег с alias 'faq-home' чтобы отмечать вопросы для главной страницы
$homeTagAlias = "faq-home";

try {
    if (class_exists("Joomla\\CMS\\Factory")) {
        $db = Joomla\CMS\Factory::getDbo();
    } elseif (class_exists("JFactory")) {
        $db = JFactory::getDbo();
    } else {
        $db = null;
    }

    if ($db) {
        // Получаем ID тега, который отмечает вопросы для главной страницы
        $tagQuery = $db
            ->getQuery(true)
            ->select($db->quoteName("id"))
            ->from($db->quoteName("#__tags"))
            ->where($db->quoteName("alias") . " = " . $db->quote($homeTagAlias))
            ->where($db->quoteName("published") . " = 1");
        $db->setQuery($tagQuery);
        $tagId = (int) $db->loadResult();

        if ($tagId) {
            $query = $db
                ->getQuery(true)
                ->select("c.id, c.title, c.introtext, c.fulltext")
                ->from($db->quoteName("#__content", "c"))
                ->join("INNER", $db->quoteName("#__contentitem_tag_map", "m") . " ON m.content_item_id = c.id")
                ->where("c.state = 1")
                ->where("m.tag_id = " . (int) $tagId)
                ->where("m.type_alias = " . $db->quote("com_content.article"))
                ->order($db->quoteName("m.tag_date") . " DESC");

            $db->setQuery($query, 0, $maxItems);
            $rows = (array) $db->loadObjectList();

            foreach ($rows as $row) {
                $question = trim((string) $row->title);
                if ($question === "") {
                    continue;
                }

                $answerRaw = $row->fulltext !== "" ? $row->fulltext : $row->introtext;
                $faqItems[] = [
                    "q" => $question,
                    "a" => trim(strip_tags((string) $answerRaw)),
                ];
            }
        }
    }
} catch (Throwable $e) {
    // Игнорируем ошибки и используем фолбэк ниже
}

if (empty($faqItems)) {
    require __DIR__ . "/../../data/faq_data.php";
    $faqItems = array_slice($faq_data, 0, $maxItems);
}

if (empty($faqItems)) {
    return;
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
                                <li class="faq-home__item"><?= htmlspecialchars($item["q"], ENT_QUOTES, "UTF-8") ?></li>
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
            <a href="/faq" class="btn-main faq-home__btn faq-home__btn--desktop">
                <span>Вопросы - ответы</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                </svg>
            </a>
        </div>
        <a href="/faq" class="btn-main faq-home__btn faq-home__btn--mobile">
            <span>Вопросы - ответы</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
            </svg>
        </a>
    </div>
</section>
