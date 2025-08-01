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

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/embla-carousel-autoplay/embla-carousel-autoplay.umd.js"></script>
  <?php if ($isHome): ?>
    <script src="templates/capitalcraft/js/home/partners.js"></script>
  <?php endif; ?>
  <script src="templates/capitalcraft/js/burger.js"></script>
  <script src="templates/capitalcraft/js/modal.js"></script>
  <script src="templates/capitalcraft/js/phone-mask.js"></script>
  <script src="templates/capitalcraft/js/form-submit.js"></script>
  <script src="templates/capitalcraft/js/script.js"></script>

</body>

</html>