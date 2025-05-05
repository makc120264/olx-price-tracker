<?php

namespace App\Controller;

use App\Service\SubscriptionService;
use App\Service\Mailer;
use Dotenv\Dotenv;

class SubscribeController
{
    /**
     * @param array $data
     * @return void
     */
    public function subscribe(array $data): void
    {
        // Load env variables
        if (!getenv('DB_HOST')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
        }

        $email = trim($data['email'] ?? '');
        $url = trim($data['url'] ?? '');

        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo 'Invalid email';
            return;
        }

        if (!preg_match('#^https://www\.olx\.[a-z]{2,}/.+#', $url)) {
            http_response_code(400);
            echo 'Invalid OLX URL';
            return;
        }

        $subscriptionService = new SubscriptionService();
        $token = $subscriptionService->createOrUpdateSubscription($email, $url);

        // Send confirmation email
        $mailer = new Mailer();
        $confirmUrl = sprintf($_SERVER["REQUEST_SCHEME"] . '://' .$_SERVER["HTTP_HOST"] . '/verify.php?token=%s', $token);
        $mailer->sendConfirmationEmail($email, $confirmUrl);

        echo 'Subscription created. Check your email to confirm.';
    }
}
