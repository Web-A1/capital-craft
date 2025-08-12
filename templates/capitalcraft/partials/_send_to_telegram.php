<?php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// 1) Метод
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2) Данные
$phone   = trim($_POST['phone'] ?? '');
$name    = trim($_POST['name'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($phone === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указан номер телефона'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Санитизация
$clean = static function (string $s): string {
    $s = strip_tags($s);
    $s = preg_replace('/[^\P{C}\n\r\t]/u', '', $s);
    return mb_substr($s, 0, 500);
};
$phone   = $clean($phone);
$name    = $clean($name);
$message = $clean($message);

// 3) Ключи
$configPath = dirname(__DIR__) . '/telegram_config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Файл конфигурации не найден'], JSON_UNESCAPED_UNICODE);
    exit;
}
$config = include $configPath;
$token  = $config['TELEGRAM_TOKEN'] ?? '';
$chatId = $config['CHAT_ID'] ?? '';
if ($token === '' || $chatId === '') {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Отсутствуют TELEGRAM_TOKEN/CHAT_ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4) Отправка
$text = "Новая заявка:\nИмя: {$name}\nТелефон: {$phone}\nСообщение: {$message}";
$url  = "https://api.telegram.org/bot{$token}/sendMessage";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => ['chat_id' => $chatId, 'text' => $text],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 10,
]);
$result = curl_exec($ch);
$errno  = curl_errno($ch);
$code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno !== 0) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Сеть недоступна'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($code === 200) {
    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Не удалось отправить'], JSON_UNESCAPED_UNICODE);
}
