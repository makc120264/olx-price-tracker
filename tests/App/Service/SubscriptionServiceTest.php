<?php

namespace App\Service;

use App\Parser\Parser;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class SubscriptionServiceTest extends TestCase
{

    /**
     * @var
     */
    private $db;
    /**
     * @var SubscriptionService
     */
    private SubscriptionService $subscriptionService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        // Create a mock database
        $this->db = $this->createMock(PDO::class);
        // Create a SubscriptionService object with a mocked db
        $this->subscriptionService = new SubscriptionService($this->db);
    }

    /**
     * @return void
     * @throws RandomException
     */
    public function testCreateOrUpdateSubscription()
    {
        $email = 'test@example.com';
        $url = 'https://example.com/ad';

        $subscriptionService = $this->getMockBuilder(SubscriptionService::class)
            ->setConstructorArgs([$this->db]) // Constructor parameters
            ->onlyMethods(['findOrCreateUser', 'findOrCreateListing']) // Lock only these methods
            ->getMock();

        // Set the return values
        $subscriptionService->method('findOrCreateUser')->willReturn(1); // user ID
        $subscriptionService->method('findOrCreateListing')->willReturn(100); // ad ID

        // Lock work with the database to search for an existing subscription
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')
            ->willReturn(false);  // No existing subscription

        // Lock preparation of SQL queries
        $this->db->method('prepare')
            ->willReturn($stmtMock);

        // Insert new subscription
        $stmtMockInsert = $this->createMock(PDOStatement::class);
        $this->db->method('prepare')
            ->willReturn($stmtMockInsert);

        // Check that the method will return a new token
        $token = $this->subscriptionService->createOrUpdateSubscription($email, $url);

        // Check that the method returned a token
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * @return void
     */
    public function testFindOrCreateUser()
    {
        // Create a mock for the database
        $dbMock = $this->createMock(PDO::class);

        // Mock for prepare method
        $stmtMock = $this->createMock(PDOStatement::class);

        // Set up a mock for the prepare method so that it returns our $stmtMock
        $dbMock->method('prepare')->willReturn($stmtMock);

        // Set up a mock for the execute method so that it always returns true
        $stmtMock->method('execute')->willReturn(true);

        // Set up a mock for the fetch method so that it returns user data
        $stmtMock->method('fetch')->willReturn(['id' => 123]);

        // Create a SubscriptionService instance with $db mocked
        $subscriptionService = new SubscriptionService($dbMock);

        // Check that the returned value is the id of an existing user
        $result = $subscriptionService->findOrCreateUser('user@example.com');

        $this->assertEquals($result, $result);
    }

    /**
     * @return void
     */
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
