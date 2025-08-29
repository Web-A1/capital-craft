<?php
defined("_JEXEC") or die();

// Получаем объект меню и проверяем: активный пункт = главный?
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active = $menu->getActive();
$isHome = $active == $menu->getDefault();
$isFaq = $active && $active->alias === "faq";

$document = JFactory::getDocument();
$document->addStyleSheet($this->baseurl . "/templates/capitalcraft/css/base.css");

if ($isHome) {
    $document->addStyleSheet($this->baseurl . "/templates/capitalcraft/css/home.css");
}

if ($isFaq) {
    $document->addStyleSheet($this->baseurl . "/templates/capitalcraft/css/faq.css");
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>

  <jdoc:include type="head" />

  <?php if ($isHome): ?>
    
    <!-- Open Graph теги для социальных сетей -->
    <?php $ogTitle = htmlspecialchars($document->getTitle(), ENT_QUOTES, "UTF-8"); ?>
    <?php $ogDesc = htmlspecialchars((string) $document->getMetaData("description"), ENT_QUOTES, "UTF-8"); ?>
    <meta property="og:title" content="<?= $ogTitle ?>">
    <?php if (!empty($ogDesc)): ?>
      <meta property="og:description" content="<?= $ogDesc ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= JURI::root() ?>">
    <meta property="og:site_name" content="Capital Craft">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:image" content="<?= JURI::root() ?>templates/capitalcraft/images/og/OG-image.webp">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Capital Craft — превью">
    
    <!-- Twitter Card разметка -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $ogTitle ?>">
    <?php if (!empty($ogDesc)): ?>
      <meta name="twitter:description" content="<?= $ogDesc ?>">
    <?php endif; ?>
    <meta name="twitter:image" content="<?= JURI::root() ?>templates/capitalcraft/images/og/OG-image.webp">
    <meta name="twitter:image:alt" content="Capital Craft — превью">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= JURI::root() ?>">
    
    <!-- Hreflang для языковой версии (только для главной) -->
    <link rel="alternate" hreflang="ru-RU" href="https://capital-craft.ru/">
    <link rel="alternate" hreflang="x-default" href="https://capital-craft.ru/">
    
    <!-- Мета-теги для поисковых роботов -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Дополнительные SEO мета-теги для главной страницы -->
    <meta name="keywords" content="инвестиционные решения, привлечение финансирования, капитал, бизнес, лизинг, банки, инвестиции, финансирование, рост бизнеса, стратегия развития">
    <meta name="author" content="Capital Craft">
    <meta name="publisher" content="Capital Craft">
    <meta name="copyright" content="© 2025 Capital Craft. Все права защищены.">
    
    <!-- Структурированные данные JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Capital Craft",
      "alternateName": "Capital-craft",
      "description": "Бутиковое агентство инвестиционных решений",
      "url": "https://capital-craft.ru/",
      "logo": "https://capital-craft.ru/favicon.svg",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+7 (499) 325-68-26",
        "contactType": "customer service",
        "areaServed": "RU",
        "availableLanguage": "Russian"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Варшавское шоссе 33, стр 1",
        "addressLocality": "Москва",
        "postalCode": "117105",
        "addressCountry": "RU"
      },
      "sameAs": [
        "https://dzen.ru/capital_craft1",
        "https://t.me/capital_craft1"
      ],
      "serviceType": "Инвестиционные решения",
      "areaServed": "Россия"
    }
    </script>
  <?php else: ?>
    <!-- Базовые SEO мета-теги для остальных страниц -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="revisit-after" content="7 days">
    
    <!-- Open Graph теги для остальных страниц -->
    <meta property="og:site_name" content="Capital Craft">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:type" content="website">
    
    <!-- Hreflang на уровне остальных страниц не задаём глобально -->
    
    <!-- Специальные мета-теги для FAQ страницы -->
    <?php if ($isFaq): ?>
      <!-- Title и Description берутся из пункта меню/админки -->
      
      <!-- Keywords, Author, Publisher -->
      <meta name="keywords" content="инвестиции, финансирование, капитал, бизнес, лизинг, банки, привлечение средств">
      <meta name="author" content="Capital Craft">
      <meta name="publisher" content="Capital Craft">
      <meta name="copyright" content="© 2025 Capital Craft. Все права защищены.">
      
      <!-- Canonical URL для FAQ (динамически) -->
      <link rel="canonical" href="<?= JURI::current() ?>">
      
      <!-- Open Graph теги для FAQ -->
      <?php $faqTitle = htmlspecialchars($document->getTitle(), ENT_QUOTES, "UTF-8"); ?>
      <?php $faqDesc = htmlspecialchars((string) $document->getMetaData("description"), ENT_QUOTES, "UTF-8"); ?>
      <meta property="og:title" content="<?= $faqTitle ?>">
      <?php if (!empty($faqDesc)): ?>
        <meta property="og:description" content="<?= $faqDesc ?>">
      <?php endif; ?>
      <meta property="og:type" content="website">
      <meta property="og:url" content="<?= JURI::current() ?>">
      <meta property="og:site_name" content="Capital Craft">
      <meta property="og:locale" content="ru_RU">
      <meta property="og:image" content="<?= JURI::root() ?>templates/capitalcraft/images/og/OG-image.webp">
      <meta property="og:image:width" content="1200">
      <meta property="og:image:height" content="630">
      
      <!-- Twitter Card для FAQ -->
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="<?= $faqTitle ?>">
      <?php if (!empty($faqDesc)): ?>
        <meta name="twitter:description" content="<?= $faqDesc ?>">
      <?php endif; ?>
      <meta name="twitter:image" content="<?= JURI::root() ?>templates/capitalcraft/images/og/OG-image.webp">
      
      <!-- Hreflang для FAQ -->
      <link rel="alternate" hreflang="ru-RU" href="https://capital-craft.ru/faq">
      <link rel="alternate" hreflang="x-default" href="https://capital-craft.ru/faq">
    <?php endif; ?>
  <?php endif; ?>

  <!-- Шрифты -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;700&family=Onest:wght@400;700&family=Syne:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Критические стили -->
  <link rel="stylesheet" href="templates/capitalcraft/css/critical.css">

  <!-- Верификация Яндекс.Вебмастера -->
  <meta name="yandex-verification" content="277972e517ae7eff" />
  <!-- Адаптивная SVG-фавиконка (переключается через prefers-color-scheme внутри SVG) -->
  <link rel="icon" href="<?php echo $this->baseurl; ?>/favicon.svg?v=2" type="image/svg+xml" sizes="any">

  <!-- Apple и mask icon -->
  <link rel="apple-touch-icon" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/apple-touch-icon.png">
  <link rel="mask-icon" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/favicon_black.svg" color="#000">

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="modulepreload" href="templates/capitalcraft/js/global/bundle.js">
</head>

<body>
  <div class="page-wrapper">

    <?php include __DIR__ . "/partials/_header.php"; ?>

    <?php if ($this->countModules("breadcrumbs")): ?>
      <div class="breadcrumbs-wrapper">
        <div class="frame">
          <jdoc:include type="modules" name="breadcrumbs" style="none" />
        </div>
      </div>
    <?php endif; ?>

    <?php if ($isHome): ?>
      <?php include __DIR__ . "/pages/home/hero.php"; ?>
      <?php include __DIR__ . "/pages/home/partners.php"; ?>
      <?php include __DIR__ . "/pages/home/philosophy.php"; ?>
      <?php include __DIR__ . "/pages/home/team.php"; ?>
      <?php include __DIR__ . "/pages/home/faq-home.php"; ?>
      <?php include __DIR__ . "/pages/home/products.php"; ?>
      <?php include __DIR__ . "/pages/home/show_case.php"; ?>
      <?php include __DIR__ . "/pages/home/reviews.php"; ?>

    <?php else: ?>
      <main>
        <jdoc:include type="component" />
      </main>
    <?php endif; ?>

    <?php include __DIR__ . "/partials/_footer.php"; ?>
    <?php include __DIR__ . "/partials/_modal.php"; ?>
    <div class="container">
      <button class="scroll-top" aria-label="Наверх">
        <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 7.49969C0 7.12772 0.273914 6.81983 0.628906 6.77118L0.727539 6.76434L13.5117 6.76434L8.89355 2.11884C8.60893 1.83253 8.60823 1.36733 8.8916 1.07977C9.14925 0.818358 9.55271 0.793725 9.83789 1.00653L9.91992 1.07684L15.7861 6.97821C15.8531 7.04562 15.9029 7.12416 15.9385 7.20673C15.9438 7.21911 15.9475 7.23208 15.9521 7.24481C15.9641 7.27737 15.9752 7.30979 15.9824 7.34344C15.9846 7.35343 15.9856 7.36359 15.9873 7.37372C15.9936 7.41069 15.9973 7.44764 15.998 7.48505C15.9981 7.48989 16 7.49482 16 7.49969C16 7.50823 15.9974 7.51662 15.9971 7.52508C15.9961 7.5539 15.9946 7.58247 15.9902 7.61102C15.9874 7.62956 15.9836 7.64765 15.9795 7.66571C15.974 7.68944 15.9678 7.71283 15.96 7.73602C15.9532 7.75611 15.9449 7.77535 15.9365 7.79462C15.9286 7.81277 15.9206 7.83074 15.9111 7.84833C15.8993 7.87037 15.886 7.8912 15.8721 7.9118C15.8666 7.91985 15.8632 7.92932 15.8574 7.93719L15.834 7.96454C15.8248 7.97586 15.8155 7.987 15.8057 7.99774L15.7871 8.0202L9.91992 13.9225C9.63542 14.2088 9.17507 14.2078 8.8916 13.9206C8.63393 13.6592 8.61139 13.2508 8.82324 12.9636L8.89355 12.8815L13.5107 8.23407H0.727539C0.325904 8.23407 6.17158e-05 7.90543 0 7.49969Z" />
        </svg>
      </button>
    </div>
  </div>

  <?php if ($isHome): ?>
    <script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/embla-carousel-autoplay/embla-carousel-autoplay.umd.js"></script>

    <script src="templates/capitalcraft/js/pages/home/partners.js"></script>

    <!-- Swiper styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Swiper script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="templates/capitalcraft/js/pages/home/show_case_swiper.js"></script>
    <script src="templates/capitalcraft/js/pages/home/faq_swiper.js"></script>
    <script src="templates/capitalcraft/js/pages/home/reviews_swiper.js"></script>
  <?php endif; ?>

  <?php if ($isFaq): ?>
    <script src="templates/capitalcraft/js/pages/faq/faq.js"></script>
  <?php endif; ?>

  <script type="module" src="templates/capitalcraft/js/global/bundle.js"></script>

</body>

</html>
