<?php

namespace App\Parser;

class Parser
{
    public function fetchCurrentPrice(string $url): ?string
    {
//        $html = @file_get_contents($url);
        $html = $this->getFileContents($url);
        if (!$html) {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Пример: ищем цену по data-testid атрибуту OLX
        $priceNode = $xpath->query('//*[@data-testid="ad-price-container"]');

        if ($priceNode->length > 0) {
            $length = $priceNode->item(0)->childNodes->length;
            for ($i = 0; $i < $length; $i++) {
                $nodeName = $priceNode->item(0)->childNodes->item($i)->nodeName;
                if ($nodeName === 'h3') {
                    return $priceNode->item(0)->childNodes->item($i)->textContent;
                }
            }
        }

        return null;
    }

    // Обёртка для file_get_contents
    public function getFileContents(string $url): ?string
    {
        return @file_get_contents($url);
    }
}
