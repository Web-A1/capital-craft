<?php
defined('_JEXEC') or die;

// Получаем объект меню и проверяем: активный пункт = главный?
$app   = JFactory::getApplication();
$menu  = $app->getMenu();
$isHome = $menu->getActive() == $menu->getDefault();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <jdoc:include type="head" />

  <!-- Фавиконки -->
  <link rel="icon" type="image/x-icon" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/favicon.ico">
  <link rel="icon" type="image/svg+xml" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/favicon.svg">
  <link rel="apple-touch-icon" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/apple-touch-icon.png">
  <link rel="mask-icon" href="<?php echo $this->baseurl; ?>/templates/capitalcraft/images/favicon/favicon_black.svg" color="#000">


  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Шрифты -->
  <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;700&display=swap" rel="stylesheet">

  <!-- Основные стили -->
  <link rel="stylesheet" href="templates/capitalcraft/css/style.css">

</head>

<body>
  <div class="page-wrapper">
    <div class="magazine-frame">

      <?php include __DIR__ . '/header.php'; ?>

      <?php if ($isHome): ?>
        <?php include __DIR__ . '/pages/home/hero.php'; ?>
        <?php include __DIR__ . '/pages/home/partners.php'; ?>
        <?php include __DIR__ . '/pages/home/philosophy.php'; ?>
        <?php include __DIR__ . '/pages/home/team.php'; ?>
        <?php include __DIR__ . '/pages/home/faq.php'; ?>
        <?php include __DIR__ . '/pages/home/products.php'; ?>
        <?php include __DIR__ . '/pages/home/show_case.php'; ?>
        <?php include __DIR__ . '/pages/home/reviews.php'; ?>


        <!-- добавь другие секции по необходимости -->
      <?php else: ?>
        <main>
          <jdoc:include type="component" />
        </main>
      <?php endif; ?>

      <?php include __DIR__ . '/footer.php'; ?>
      <?php include __DIR__ . '/modal.php'; ?>
      <button class="scroll-top" aria-label="Наверх">
        <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 7.49969C0 7.12772 0.273914 6.81983 0.628906 6.77118L0.727539 6.76434L13.5117 6.76434L8.89355 2.11884C8.60893 1.83253 8.60823 1.36733 8.8916 1.07977C9.14925 0.818358 9.55271 0.793725 9.83789 1.00653L9.91992 1.07684L15.7861 6.97821C15.8531 7.04562 15.9029 7.12416 15.9385 7.20673C15.9438 7.21911 15.9475 7.23208 15.9521 7.24481C15.9641 7.27737 15.9752 7.30979 15.9824 7.34344C15.9846 7.35343 15.9856 7.36359 15.9873 7.37372C15.9936 7.41069 15.9973 7.44764 15.998 7.48505C15.9981 7.48989 16 7.49482 16 7.49969C16 7.50823 15.9974 7.51662 15.9971 7.52508C15.9961 7.5539 15.9946 7.58247 15.9902 7.61102C15.9874 7.62956 15.9836 7.64765 15.9795 7.66571C15.974 7.68944 15.9678 7.71283 15.96 7.73602C15.9532 7.75611 15.9449 7.77535 15.9365 7.79462C15.9286 7.81277 15.9206 7.83074 15.9111 7.84833C15.8993 7.87037 15.886 7.8912 15.8721 7.9118C15.8666 7.91985 15.8632 7.92932 15.8574 7.93719L15.834 7.96454C15.8248 7.97586 15.8155 7.987 15.8057 7.99774L15.7871 8.0202L9.91992 13.9225C9.63542 14.2088 9.17507 14.2078 8.8916 13.9206C8.63393 13.6592 8.61139 13.2508 8.82324 12.9636L8.89355 12.8815L13.5107 8.23407H0.727539C0.325904 8.23407 6.17158e-05 7.90543 0 7.49969Z" />
        </svg>
      </button>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/embla-carousel-autoplay/embla-carousel-autoplay.umd.js"></script>
  <?php if ($isHome): ?>
    <script src="templates/capitalcraft/js/home/partners.js"></script>
    <script src="templates/capitalcraft/js/home/show_case_swiper.js"></script>
  <?php endif; ?>

  <script src="templates/capitalcraft/js/burger.js"></script>
  <script src="templates/capitalcraft/js/modal.js"></script>
  <script src="templates/capitalcraft/js/phone-mask.js"></script>
  <script src="templates/capitalcraft/js/form-submit.js"></script>
  <script src="templates/capitalcraft/js/scroll-top.js"></script>
  <script src="templates/capitalcraft/js/script.js"></script>

</body>

</html>