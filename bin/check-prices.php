#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Parser\Parser;
use App\Service\Mailer;

$pdo = require __DIR__ . '/../config/db.php';
$parser = new Parser();
$mailer = new Mailer();

// Get all unique ads with subscriptions
$stmt = $pdo->query(
    "
    SELECT l.id, l.url, l.last_price
    FROM listings l
    JOIN subscriptions s ON s.listing_id = l.id
    GROUP BY l.id
"
);

$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($listings as $listing) {
    echo "Checking {$listing['url']}... ";

    $currentPrice = $parser->fetchCurrentPrice($listing['url']);

    if ($currentPrice === null) {
        echo "Failed to fetch price.\n";
        continue;
    }

    if ((float)$currentPrice !== (float)$listing['last_price']) {
        echo "Price changed: {$listing['last_price']} → {$currentPrice}\n";

        // Update the price
        $stmt = $pdo->prepare("UPDATE listings SET last_price = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$currentPrice, $listing['id']]);

        // Get all subscribers
        $stmt = $pdo->prepare("SELECT email FROM subscriptions WHERE listing_id = ?");
        $stmt->execute([$listing['id']]);
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($emails as $email) {
            $mailer->send(
                $email,
                "Price changed",
                <<<EOL
Ad price by link {$listing['url']} has changed:
Old price: {$listing['last_price']}
New price: {$currentPrice}
EOL
            );
        }
    } else {
        echo "No change.\n";
    }
}
