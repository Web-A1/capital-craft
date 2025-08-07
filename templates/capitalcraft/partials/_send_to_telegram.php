<?php
header('Content-Type: application/json; charset=utf-8');
$phone = trim($_POST['phone'] ?? '');
if ($phone === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указан номер телефона']);
    exit;
}
$name = trim($_POST['name'] ?? '');
$message = trim($_POST['message'] ?? '');
$token = $env['TELEGRAM_TOKEN'];
$chat_id = $env['CHAT_ID'];
$text = "Новая заявка:\nИмя: {$name}\nТелефон: {$phone}\nСообщение: {$message}";
$url = "https://api.telegram.org/bot{$token}/sendMessage";
$params = [
    'chat_id' => $chat_id,
    'text' => $text
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code == 200) {
    echo json_encode(['status' => 'ok']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера']);
}
