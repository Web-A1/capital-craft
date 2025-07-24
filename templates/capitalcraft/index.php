<?php
defined('_JEXEC') or die;

$enabled = false; // ← здесь включаешь/отключаешь проверку

if ($enabled) {
  $allowedIp = '82.208.83.161';
  $clientIp = $_SERVER['REMOTE_ADDR'];

  if ($clientIp !== $allowedIp) {
    echo '<h1>Сайт в разработке</h1><p>Доступ только для администратора.</p>';
    exit;
  }
}
?>

<link rel="stylesheet" href="templates/capitalcraft/css/style.css">

<!DOCTYPE html>
<html lang="ru">

<head>
  <jdoc:include type="head" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="templates/capitalcraft/css/style.css">
</head>

<body>
  <div class="page-wrapper">
    <div class="magazine-frame">
      <?php include __DIR__ . '/header.php'; ?>

      <main>
        <jdoc:include type="component" />
      </main>

      <?php include __DIR__ . '/footer.php'; ?>

    </div>
  </div>

  <script src="templates/capitalcraft/js/script.js"></script>
</body>


</html>