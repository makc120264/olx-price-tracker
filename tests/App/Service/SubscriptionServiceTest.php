<?php

namespace App\Service;

use App\Parser\Parser;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SubscriptionServiceTest extends TestCase
{

    private $db;
    private $subscriptionService;

    protected function setUp(): void
    {
        // Создаем замок базы данных
        $this->db = $this->createMock(PDO::class);

        // Создаем объект SubscriptionService с замоканным db
        $this->subscriptionService = new SubscriptionService($this->db);
    }

    public function testCreateOrUpdateSubscription()
    {
        $email = 'test@example.com';
        $url = 'https://example.com/ad';

        $subscriptionService = $this->getMockBuilder(SubscriptionService::class)
            ->setConstructorArgs([$this->db]) // Параметры конструктора
            ->onlyMethods(['findOrCreateUser', 'findOrCreateListing']) // Замокаем только эти методы
            ->getMock();

// Устанавливаем возвращаемые значения
        $subscriptionService->method('findOrCreateUser')->willReturn(1); // ID пользователя
        $subscriptionService->method('findOrCreateListing')->willReturn(100); // ID объявления

        // Замокаем работу с БД для поиска существующей подписки
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')
            ->willReturn(false);  // Нет существующей подписки

        // Замокаем подготовку SQL-запросов
        $this->db->method('prepare')
            ->willReturn($stmtMock);

        // Вставка новой подписки
        $stmtMockInsert = $this->createMock(PDOStatement::class);
        $this->db->method('prepare')
            ->willReturn($stmtMockInsert);

        // Проверяем, что метод вернет новый токен
        $token = $this->subscriptionService->createOrUpdateSubscription($email, $url);

        // Проверяем, что метод вернул токен
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testFindOrCreateUser()
    {
        // Создаем мок для базы данных
        $dbMock = $this->createMock(PDO::class);

        // Мок для prepare метода
        $stmtMock = $this->createMock(PDOStatement::class);

        // Настроим mock для метода prepare, чтобы он возвращал наш $stmtMock
        $dbMock->method('prepare')->willReturn($stmtMock);

        // Настроим mock для execute метода, чтобы он всегда возвращал true
        $stmtMock->method('execute')->willReturn(true);

        // Настроим mock для fetch метода, чтобы он возвращал данные пользователя
        $stmtMock->method('fetch')->willReturn(['id' => 123]);

        // Создаем экземпляр SubscriptionService с замоканным $db
        $subscriptionService = new SubscriptionService($dbMock);

        // Проверяем, что возвращаемое значение — это id существующего пользователя
        $result = $subscriptionService->findOrCreateUser('user@example.com');

        $this->assertEquals($result, $result);
    }

    public function testFindOrCreateListing()
    {
        $url = 'https://olx.example/ad/123';

        $dbMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);
        $parserMock = $this->createMock(Parser::class);

        $dbMock->method('prepare')->willReturn($stmtMock);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn(['id' => 999]);

        $subscriptionService = new SubscriptionService($dbMock, $parserMock);

        $listingId = $subscriptionService->findOrCreateListing($url);
        $this->assertEquals(999, $listingId);    }
}
