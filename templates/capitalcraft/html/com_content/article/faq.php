<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$db  = JFactory::getDbo();

// Канонический URL для JSON-LD схем (используется только в структурированных данных)
$canonicalUrl = 'https://capital-craft.ru/faq';

// Параметр фильтра по тегу (?tag=alias|id)
$input   = $app->input;
$tagParam = trim($input->getString('tag', ''));
$tagIds   = [];

if ($tagParam !== '') {
    if (ctype_digit($tagParam)) {
        $tagIds = [(int) $tagParam];
    } else {
        $qTag = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__tags'))
            ->where($db->quoteName('alias') . ' = ' . $db->quote($tagParam))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($qTag);
        $found = (int) $db->loadResult();
        if ($found) {
            $tagIds = [$found];
        }
    }
}

// Определяем категорию FAQ по alias 'faq' (fallback: по title 'FAQ')
$faqCatId = 0;
$qCat = $db->getQuery(true)
    ->select($db->quoteName('id'))
    ->from($db->quoteName('#__categories'))
    ->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
    ->where($db->quoteName('alias') . ' = ' . $db->quote('faq'))
    ->where($db->quoteName('published') . ' = 1');
$db->setQuery($qCat);
$faqCatId = (int) $db->loadResult();

if (!$faqCatId) {
    $qCat2 = $db->getQuery(true)
        ->select($db->quoteName('id'))
        ->from($db->quoteName('#__categories'))
        ->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
        ->where($db->quoteName('title') . ' = ' . $db->quote('FAQ'))
        ->where($db->quoteName('published') . ' = 1');
    $db->setQuery($qCat2);
    $faqCatId = (int) $db->loadResult();
}

// Загружаем FAQ из БД, при отсутствии категории — используем локальный массив как фолбэк
$faqItems = [];
$faqIds   = [];
if ($faqCatId) {
    $q = $db->getQuery(true)
        ->select('c.id, c.title, c.introtext, c.fulltext, c.publish_up')
        ->from($db->quoteName('#__content', 'c'))
        ->where('c.state = 1')
        ->where('c.catid = ' . (int) $faqCatId)
        ->order('c.publish_up DESC');

    if (!empty($tagIds)) {
        $q->join(
            'INNER',
            $db->quoteName('#__contentitem_tag_map', 'm') .
            ' ON ' . $db->quoteName('m.content_item_id') . ' = ' . $db->quoteName('c.id') .
            ' AND ' . $db->quoteName('m.type_alias') . ' = ' . $db->quote('com_content.article')
        );
        $q->where('m.tag_id IN (' . implode(',', array_map('intval', $tagIds)) . ')');
        $q->group($db->quoteName('c.id'));
    }

    $db->setQuery($q);
    $rows = (array) $db->loadObjectList();
    foreach ($rows as $row) {
        $answer = !empty($row->fulltext) ? $row->fulltext : ($row->introtext ?? '');
        $faqItems[] = [
            'id' => (int) $row->id,
            'q'  => (string) $row->title,
            // Храним как плейн‑текст для безопасного вывода (как было в массиве)
            'a'  => trim(strip_tags((string) $answer)),
            'tags' => [],
        ];
        $faqIds[] = (int) $row->id;
    }

    // Подтягиваем теги для найденных FAQ
    if (!empty($faqIds)) {
        $qTags = $db->getQuery(true)
            ->select(
                $db->quoteName('m.content_item_id', 'cid') . ', ' .
                $db->quoteName('t.id') . ', ' .
                $db->quoteName('t.title') . ', ' .
                $db->quoteName('t.alias')
            )
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->join('INNER', $db->quoteName('#__tags', 't') . ' ON t.id = m.tag_id')
            ->where('m.type_alias = ' . $db->quote('com_content.article'))
            ->where('t.published = 1')
            ->where('m.content_item_id IN (' . implode(',', array_map('intval', $faqIds)) . ')');
        $db->setQuery($qTags);
        $tagRows = (array) $db->loadObjectList();

        // Индексируем теги по ID материала
        $tagsById = [];
        foreach ($tagRows as $tr) {
            $cid = (int) $tr->cid;
            if (!isset($tagsById[$cid])) {
                $tagsById[$cid] = [];
            }
            $tagsById[$cid][] = [
                'id' => (int) $tr->id,
                'title' => (string) $tr->title,
                'alias' => (string) $tr->alias,
            ];
        }

        // Прикрепляем к элементам
        foreach ($faqItems as &$it) {
            if (isset($tagsById[$it['id']])) {
                $it['tags'] = $tagsById[$it['id']];
            }
        }
        unset($it);
    }
}

// Фолбэк отключён: если в БД нет данных, не выводим вопросы

// Улучшенные структурированные данные JSON-LD для FAQ
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'name' => 'Часто задаваемые вопросы о привлечении капитала',
    'description' => 'Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса от экспертов Capital Craft',
    'url' => $canonicalUrl,
    'mainEntity' => [],
    'about' => [
        '@type' => 'Organization',
        'name' => 'Capital Craft',
        'description' => 'Бутиковое агентство инвестиционных решений',
    ],
];

foreach ($faqItems as $index => $item) {
    $faqSchema['mainEntity'][] = [
        '@type' => 'Question',
        'position' => $index + 1,
        'name' => (string) ($item['q'] ?? ''),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => (string) ($item['a'] ?? ''),
            'dateCreated' => date('c'),
            'upvoteCount' => 1,
        ],
    ];
}

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');

// BreadcrumbList schema
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Главная',
            'item' => JURI::root(),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'FAQ',
            'item' => JURI::current(),
        ],
    ],
];

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');

// Organization schema для Capital Craft
$orgSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Capital Craft',
    'alternateName' => 'Capital-craft',
    'description' => 'Бутиковое агентство инвестиционных решений, специализирующееся на привлечении финансирования для бизнеса',
    'url' => JURI::root(),
    'logo' => [
        '@type' => 'ImageObject',
        'url' => JURI::root() . 'templates/capitalcraft/images/logo_black.svg',
        'width' => 200,
        'height' => 60,
    ],
    'image' => JURI::root() . 'templates/capitalcraft/images/faq/faq_hand.webp',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Варшавское шоссе 33, стр 1',
        'addressLocality' => 'Москва',
        'postalCode' => '117105',
        'addressCountry' => 'RU',
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+7 (499) 325-68-26',
        'contactType' => 'customer service',
        'email' => 'info@capital-craft.ru',
        'availableLanguage' => ['Russian', 'English'],
    ],
    'sameAs' => [
        'https://t.me/capital_craft1',
        'https://dzen.ru/capital_craft1',
    ],
    'foundingDate' => '2020',
    'areaServed' => 'RU',
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Инвестиционные решения и привлечение капитала',
        'description' => 'Услуги по привлечению капитала и инвестиционным решениям',
    ],
];

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');

// WebPage schema
$webPageSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Часто задаваемые вопросы - Capital Craft',
    'description' => 'Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса',
    'url' => JURI::current(),
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => 'Capital Craft',
        'url' => JURI::root(),
    ],
    'about' => [
        '@type' => 'Organization',
        'name' => 'Capital Craft',
    ],
    'inLanguage' => 'ru-RU',
    'breadcrumb' => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => JURI::root(),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'FAQ',
                'item' => JURI::current(),
            ],
        ],
    ],
];

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');
?>

<section class="faq frame section-with-divider">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <h1 class="faq__subtitle">часто задаваемые вопросы</h1>
                <p class="faq__title">Сильные решения начинаются с вопросов</p>
            </div>
            <div class="faq__accordion" role="region" aria-label="Список часто задаваемых вопросов">
                <?php foreach ($faqItems as $index => $item): ?>
                    <div class="faq__item">
                        <button class="faq__question" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-<?php echo $index; ?>"
                                aria-label="Вопрос <?php echo $index + 1; ?>: <?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="faq__text">
                                <?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if (!empty($item['tags'])): ?>
                                <?php $tg = $item['tags'][0]; ?>
                                <a class="faq__tag-chip" href="<?php echo JRoute::_(
                                    '/faq?tag=' . rawurlencode($tg['alias'])
                                ); ?>">#<?php echo htmlspecialchars($tg['title'], ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php endif; ?>
                            <span class="faq__icon" aria-hidden="true">+</span>
                        </button>
                        <div class="faq__answer" 
                             id="faq-answer-<?php echo $index; ?>"
                             role="region" 
                             aria-label="Ответ на вопрос: <?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
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
