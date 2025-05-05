<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Database;

$token = $_GET['token'] ?? '';

if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(400);
    exit('Invalid token.');
}

$db = Database::getInstance();

$stmt = $db->prepare(
    "
    SELECT s.id, s.confirmed_at, u.id AS user_id
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    WHERE s.token = ?
"
);
$stmt->execute([$token]);
$sub = $stmt->fetch();

if (!$sub) {
    http_response_code(404);
    exit('Subscription not found.');
}

if ($sub['confirmed_at']) {
    echo 'Already confirmed.';
    exit;
}

// Обновляем флаг подтверждения
$db->prepare("UPDATE subscriptions SET confirmed_at = NOW() WHERE id = ?")->execute([$sub['id']]);
$db->prepare("UPDATE users SET confirmed_at = NOW() WHERE id = ?")->execute([$sub['user_id']]);

echo 'Subscription confirmed. You will be notified on price changes.';
