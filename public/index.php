<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\SubscribeController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/api/subscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SubscribeController();
    $data = json_decode(file_get_contents('php://input'), true);
    $controller->subscribe($data);
} else {
    http_response_code(404);
    echo 'Not Found';
}
