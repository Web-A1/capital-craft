<?php defined('_JEXEC') or die;

// Определяем, является ли это FAQ страницей
$isFAQPage = false;

// Проверяем по нескольким критериям
if (isset($this->item)) {
    // По заголовку
    if (stripos($this->item->title, 'FAQ') !== false || 
        stripos($this->item->title, 'часто задаваемые вопросы') !== false ||
        stripos($this->item->title, 'вопросы') !== false) {
        $isFAQPage = true;
    }
    
    // По alias
    if (isset($this->item->alias) && stripos($this->item->alias, 'faq') !== false) {
        $isFAQPage = true;
    }
    
    // По категории
    if (isset($this->item->catid) && $this->item->catid == 1) { // Предполагаем, что FAQ имеет catid = 1
        $isFAQPage = true;
    }
}

// Если это FAQ страница, используем наш кастомный шаблон
if ($isFAQPage) {
    // Загружаем наш FAQ шаблон
    $this->loadTemplate('faq');
} else {
    // Для остальных страниц используем стандартную Joomla логику
    // Но с улучшенной SEO оптимизацией
    
    // SEO мета-теги
    $doc = JFactory::getDocument();
    
    // Улучшенный title
    if (!empty($this->item->title)) {
        $doc->setTitle($this->item->title . ' - Capital Craft | Инвестиционные решения');
    }
    
    // Улучшенный description
    if (!empty($this->item->introtext)) {
        $description = strip_tags($this->item->introtext);
        $description = substr($description, 0, 160); // Ограничиваем длину
        $doc->setDescription($description);
    }
    
    // Canonical URL
    $canonical = JURI::current();
    $doc->addHeadLink($canonical, 'canonical', 'rel');
    
    // Open Graph теги
    $doc->addCustomTag('<meta property="og:title" content="' . htmlspecialchars($this->item->title, ENT_QUOTES, 'UTF-8') . '" />');
    $doc->addCustomTag('<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '" />');
    $doc->addCustomTag('<meta property="og:type" content="article" />');
    $doc->addCustomTag('<meta property="og:url" content="' . $canonical . '" />');
    $doc->addCustomTag('<meta property="og:site_name" content="Capital Craft" />');
    $doc->addCustomTag('<meta property="og:locale" content="ru_RU" />');
    
    // Robots meta
    $doc->setMetaData('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');
    
    // Структурированные данные для статьи
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $this->item->title,
        'description' => $description,
        'url' => $canonical,
        'datePublished' => $this->item->publish_up,
        'dateModified' => $this->item->modified,
        'author' => [
            '@type' => 'Organization',
            'name' => 'Capital Craft'
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Capital Craft',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => JURI::root() . 'templates/capitalcraft/images/logo_black.svg'
            ]
        ]
    ];
    
    $doc->addCustomTag('<script type="application/ld+json">' . json_encode($articleSchema, JSON_UNESCAPED_UNICODE) . '</script>');
    
    // Стандартная Joomla разметка
    ?>
    <div class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
        <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        </div>
        <?php endif; ?>
        
        <?php if ($this->params->get('show_title')) : ?>
        <div class="page-header">
            <h2><?php echo $this->escape($this->item->title); ?></h2>
        </div>
        <?php endif; ?>
        
        <div class="com-content-article__body">
            <?php echo $this->item->text; ?>
        </div>
    </div>
    <?php
}
?>
