<?php

namespace App\Parser;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    public function testFetchCurrentPrice()
    {
        // Создаём мока для file_get_contents
        $mockedHtml = '<html><body><div data-testid="ad-price-container"><h3>1000 грн</h3></div></body></html>';
        $priceChecker = $this->getMockBuilder(Parser::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        // Мокаем вызов file_get_contents
        $priceChecker->expects($this->once())
            ->method('getFileContents')
            ->willReturn($mockedHtml);

        // Проверяем, что метод вернёт правильную цену
        $price = $priceChecker->fetchCurrentPrice('https://example.com/ad');
        $this->assertIsString($price);
    }
}
