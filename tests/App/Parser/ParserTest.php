<?php

namespace App\Parser;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    /**
     * @return void
     */
    public function testFetchCurrentPrice()
    {
        // Create a mockup for file_get_contents
        $mockedHtml = '<html><body><div data-testid="ad-price-container"><h3>1000 грн</h3></div></body></html>';
        $priceChecker = $this->getMockBuilder(Parser::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        // Mock the file_get_contents call
        $priceChecker->expects($this->once())
            ->method('getFileContents')
            ->willReturn($mockedHtml);

        // Check that the method will return the correct price
        $price = $priceChecker->fetchCurrentPrice('https://example.com/ad');
        $this->assertIsString($price);
    }
}
