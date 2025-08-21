<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();

// SEO мета-теги для FAQ страницы
$doc->setTitle('Часто задаваемые вопросы - Capital Craft | Инвестиционные решения');
$doc->setDescription('Ответы на популярные вопросы о привлечении капитала, инвестициях и финансировании бизнеса. Экспертные консультации от Capital Craft.');

// Open Graph теги для социальных сетей (исправлено: property вместо name)
$doc->addCustomTag('<meta property="og:title" content="FAQ — Часто задаваемые вопросы | Capital Craft" />');
$doc->addCustomTag('<meta property="og:description" content="Ответы на популярные вопросы о привлечении капитала и инвестициях" />');
$doc->addCustomTag('<meta property="og:type" content="website" />');
$doc->addCustomTag('<meta property="og:url" content="' . JURI::current() . '" />');
$doc->addCustomTag('<meta property="og:image" content="' . JURI::root() . 'templates/capitalcraft/images/faq/faq_hand.webp" />');
$doc->addCustomTag('<meta property="og:site_name" content="Capital Craft" />');
$doc->addCustomTag('<meta property="og:locale" content="ru_RU" />');

// Canonical URL для предотвращения дублирования (исправлено: добавлен rel="canonical")
$canonical = JURI::current();
$doc->addHeadLink($canonical, 'canonical', 'rel');

// Hreflang теги для языковой версии (исправлено: добавлен rel="alternate")
$doc->addHeadLink($canonical, 'alternate', 'rel', ['hreflang' => 'ru-RU']);
$doc->addHeadLink($canonical, 'alternate', 'rel', ['hreflang' => 'x-default']);

// Мета-теги для поисковых роботов
$doc->setMetaData('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');
$doc->setMetaData('revisit-after', '7 days');

require __DIR__ . '/../../../data/faq_data.php';

// Структурированные данные JSON-LD для  FAQ
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => []
];

foreach ($faq_data as $item) {
    $faqSchema['mainEntity'][] = [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a']
        ]
    ];
}

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($faqSchema, JSON_UNESCAPED_UNICODE) . '</script>');

// Organization schema для Capital Craft
$orgSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Capital Craft',
    'description' => 'Бутиковое агентство инвестиционных решений, специализирующееся на привлечении финансирования для бизнеса',
    'url' => JURI::root(),
    'logo' => JURI::root() . 'templates/capitalcraft/images/logo_black.svg',
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
        'email' => 'info@capital-craft.ru'
    ],
    'sameAs' => [
        'https://t.me/capital_craft1',
        'https://dzen.ru/capital_craft1'
    ]
];

$doc->addCustomTag('<script type="application/ld+json">' . json_encode($orgSchema, JSON_UNESCAPED_UNICODE) . '</script>');
?>

<section class="faq frame section-with-divider">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <h1 class="faq__subtitle">часто задаваемые вопросы</h1>
                <h2 class="faq__title">Сильные решения начинаются с вопросов</h2>
            </div>
            <div class="faq__accordion">
                <?php foreach ($faq_data as $item): ?>
                    <div class="faq__item">
                        <button class="faq__question" aria-expanded="false">
                            <span class="faq__text">
                                <?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="faq__icon">+</span>
                        </button>
                        <div class="faq__answer">
                            <?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <figure class="faq__image">
            <img src="/templates/capitalcraft/images/faq/faq_hand.webp" alt="Часто задаваемые вопросы о привлечении капитала и инвестициях" loading="lazy">
        </figure>
    </div>
</section>