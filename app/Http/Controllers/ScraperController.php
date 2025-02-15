<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;

class ScraperController extends Controller
{
    public function scrape()
    {
        // Step 1: Scrape Data
        $client = new Client();
        $response = $client->request('GET', 'https://techcrunch.com/');
        $html = $response->getBody()->getContents();

        // Step 2: Parse Data
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Example: Extract titles and links
        $cards = $xpath->query('//div[@class="loop-card__content"]');

        $data = [];

        foreach ($cards as $card) {
            // Query for the title within the current card using a relative path (note the dot prefix)
            $titleNode = $xpath->query('.//h3[@class="loop-card__title"]/a', $card)->item(0);
            // Query for the link attribute within the current card
            $linkNode = $xpath->query('.//h3[@class="loop-card__title"]/a/@href', $card)->item(0);
            // Query for the category nodes (both <a> and <span>) within the current card
            $categoryNode = $xpath->query('.//div[@class="loop-card__cat-group"]//a | .//div[@class="loop-card__cat-group"]//span', $card)->item(0);
            
            // Extract values, if available
            $title = $titleNode ? trim($titleNode->textContent) : '';
            $link = $linkNode ? trim($linkNode->nodeValue) : '';
            
            $category = $categoryNode ? trim($categoryNode->nodeValue) : '';
            
            // Store the data for the current card
            if ($title){
                $data[] = [
                    'title'      => $title,
                    'link'       => $link,
                    'category' => $category,  // may be empty if no categories exist
                ];
            }
        }
        // Step 3: Pass Data to a View
        return view('scraped-data', ['data' => $data]);
    }
}