<?php
defined("_JEXEC") or die();

$maxItems = 9;
$faqItems = [];
$faqCatId = 0;

try {
    if (class_exists("Joomla\\CMS\\Factory")) {
        $db = Joomla\CMS\Factory::getDbo();
    } elseif (class_exists("JFactory")) {
        $db = JFactory::getDbo();
    } else {
        $db = null;
    }

    if ($db) {
        // Определяем категорию FAQ (alias `faq`, иначе по названию `FAQ`)
        $catQuery = $db
            ->getQuery(true)
            ->select($db->quoteName("id"))
            ->from($db->quoteName("#__categories"))
            ->where($db->quoteName("extension") . " = " . $db->quote("com_content"))
            ->where($db->quoteName("alias") . " = " . $db->quote("faq"))
            ->where($db->quoteName("published") . " = 1");
        $db->setQuery($catQuery);
        $faqCatId = (int) $db->loadResult();

        if (!$faqCatId) {
            $catQueryByTitle = $db
                ->getQuery(true)
                ->select($db->quoteName("id"))
                ->from($db->quoteName("#__categories"))
                ->where($db->quoteName("extension") . " = " . $db->quote("com_content"))
                ->where($db->quoteName("title") . " = " . $db->quote("FAQ"))
                ->where($db->quoteName("published") . " = 1");
            $db->setQuery($catQueryByTitle);
            $faqCatId = (int) $db->loadResult();
        }

        if ($faqCatId) {
            $featuredQuery = $db
                ->getQuery(true)
                ->select("c.id, c.title, c.introtext, c.fulltext")
                ->from($db->quoteName("#__content", "c"))
                ->join("LEFT", $db->quoteName("#__content_frontpage", "fp") . " ON fp.content_id = c.id")
                ->where("c.state = 1")
                ->where("c.catid = " . (int) $faqCatId)
                ->where("c.featured = 1")
                ->order("COALESCE(fp.ordering, 9999) ASC")
                ->order("c.publish_up DESC");

            $db->setQuery($featuredQuery, 0, $maxItems);
            $rows = (array) $db->loadObjectList();

            foreach ($rows as $row) {
                $question = trim((string) $row->title);
                if ($question === "") {
                    continue;
                }

                $answerRaw = $row->fulltext !== "" ? $row->fulltext : $row->introtext;
                $faqItems[] = [
                    "id" => (int) $row->id,
                    "q" => $question,
                    "a" => trim(strip_tags((string) $answerRaw)),
                ];
            }
        }
    }
} catch (Throwable $e) {
    // Игнорируем ошибки и просто не выводим блок
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
                                <?php
                                $questionText = htmlspecialchars($item["q"], ENT_QUOTES, "UTF-8");
                                $questionId = isset($item["id"]) ? (int) $item["id"] : 0;
                                $questionHref = $questionId > 0 ? "/faq#faq-q-{$questionId}" : "/faq";
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
