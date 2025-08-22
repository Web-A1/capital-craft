<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();

// SEO мета-теги для FAQ страницы
$doc->setTitle('Часто задаваемые вопросы - Capital Craft | Инвестиционные решения');
$doc->setDescription('Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса. Экспертные консультации от Capital Craft.');

// Open Graph теги для социальных сетей
$doc->addCustomTag('<meta property="og:title" content="FAQ — Часто задаваемые вопросы | Capital Craft" />');
$doc->addCustomTag('<meta property="og:description" content="Ответы на популярные вопросы о привлечении капитала и инвестициях" />');
$doc->addCustomTag('<meta property="og:type" content="website" />');

// Генерируем canonical URL более надежным способом
$canonicalUrl = 'https://capital-craft.ru/faq';
$doc->addCustomTag('<meta property="og:url" content="' . $canonicalUrl . '" />');

// Open Graph URL
$doc->addCustomTag('<meta property="og:url" content="' . $canonicalUrl . '" />');
$doc->addCustomTag('<meta property="og:image" content="' . JURI::root() . 'templates/capitalcraft/images/faq/faq_hand.webp" />');
$doc->addCustomTag('<meta property="og:site_name" content="Capital Craft" />');
$doc->addCustomTag('<meta property="og:locale" content="ru_RU" />');

// Canonical URL для предотвращения дублирования
$doc->addHeadLink($canonicalUrl, 'canonical', 'rel');

// Hreflang теги для языковой версии
$doc->addHeadLink($canonicalUrl, 'alternate', 'rel', ['hreflang' => 'ru-RU']);
$doc->addHeadLink($canonicalUrl, 'alternate', 'rel', ['hreflang' => 'x-default']);

// Мета-теги для поисковых роботов
$doc->setMetaData('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');
$doc->setMetaData('revisit-after', '7 days');

// Мета-теги для производительности и мобильной оптимизации
$doc->addCustomTag('<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">');
$doc->addCustomTag('<meta http-equiv="X-UA-Compatible" content="IE=edge">');
$doc->addCustomTag('<meta name="theme-color" content="#8d222c">');
$doc->addCustomTag('<meta name="msapplication-TileColor" content="#8d222c">');

require __DIR__ . '/../../../data/faq_data.php';

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
        'description' => 'Бутиковое агентство инвестиционных решений'
    ]
];

foreach ($faq_data as $index => $item) {
    $faqSchema['mainEntity'][] = [
        '@type' => 'Question',
        'position' => $index + 1,
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a'],
            'dateCreated' => date('c'),
            'upvoteCount' => 1
        ]
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
            'item' => JURI::root()
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'FAQ',
            'item' => JURI::current()
        ]
    ]
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
        'height' => 60
    ],
    'image' => JURI::root() . 'templates/capitalcraft/images/faq/faq_hand.webp',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Варшавское шоссе 33, стр 1',
        'addressLocality' => 'Москва',
        'postalCode' => '117105',
        'addressCountry' => 'RU'
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+7 (499) 325-68-26',
        'contactType' => 'customer service',
        'email' => 'info@capital-craft.ru',
        'availableLanguage' => ['Russian', 'English']
    ],
    'sameAs' => [
        'https://t.me/capital_craft1',
        'https://dzen.ru/capital_craft1'
    ],
    'foundingDate' => '2020',
    'areaServed' => 'RU',
    'serviceType' => 'Инвестиционные решения и привлечение капитала'
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
        'url' => JURI::root()
    ],
    'about' => [
        '@type' => 'Organization',
        'name' => 'Capital Craft'
    ],
    'inLanguage' => 'ru-RU',
    'breadcrumb' => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => JURI::root()
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'FAQ',
                'item' => JURI::current()
            ]
        ]
    ]
];

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');
?>

<section class="faq frame section-with-divider">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <h1 class="faq__subtitle">часто задаваемые вопросы</h1>
                <h2 class="faq__title">Сильные решения начинаются с вопросов</h2>
            </div>
            <div class="faq__accordion" role="region" aria-label="Список часто задаваемых вопросов">
                <?php foreach ($faq_data as $index => $item): ?>
                    <div class="faq__item">
                        <button class="faq__question" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-<?php echo $index; ?>"
                                aria-label="Вопрос <?php echo $index + 1; ?>: <?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="faq__text">
                                <?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
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