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

  <script src="templates/capitalcraft/js/script.js"></script>
</body>

</html>