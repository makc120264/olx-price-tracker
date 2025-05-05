<?php

namespace App\Service;

use App\Database;
use App\Parser\Parser;

class SubscriptionService
{
    private $db;
    private $parser;

    public function __construct($dbMock = null, $parserMock = null)
    {
        $this->db = is_null($dbMock) ? Database::getInstance() : $dbMock;
        $this->parser = is_null($parserMock) ? new Parser() : $parserMock;;
    }

    public function createOrUpdateSubscription(string $email, string $url): string
    {
        // 1. Найти или создать пользователя
        $userId = $this->findOrCreateUser($email);

        // 2. Найти или создать объявление
        $listingId = $this->findOrCreateListing($url);

        // 3. Проверка на существующую подписку
        $stmt = $this->db->prepare("SELECT token FROM subscriptions WHERE user_id = ? AND listing_id = ?");
        $stmt->execute([$userId, $listingId]);
        $existing = $stmt->fetch();

        if ($existing) {
            return $existing['token'];
        }

        // 4. Создание новой подписки
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare("
            INSERT INTO subscriptions (user_id, listing_id, token, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $listingId, $token]);

        return $token;
    }

    public function findOrCreateUser(string $email): int
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            return $user['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO users (email, created_at) VALUES (?, NOW())");
        $stmt->execute([$email]);
        return $this->db->lastInsertId();
    }

    public function findOrCreateListing(string $url): int
    {
        $stmt = $this->db->prepare("SELECT id FROM listings WHERE url = ?");
        $stmt->execute([$url]);
        $listing = $stmt->fetch();

        if ($listing) {
            return $listing['id'];
        }

        // Получаем цену объявления (временно через парсер)
        $price = $this->parser->fetchCurrentPrice($url);

        $stmt = $this->db->prepare("INSERT INTO listings (url, last_price, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$url, $price]);

        return $this->db->lastInsertId();
    }
}
