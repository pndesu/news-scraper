<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;

class ScraperController extends Controller
{
    private $deepseekApiKey = 'sk-792d3347c6404811ab9ebfa8dfaa9140'; // Add to .env later
    private $deepseekEndpoint = 'https://api.deepseek.com/v1/chat/completions'; // Verify endpoint

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
            $summary = $this->generateSummary($link, $client);
            // Store the data for the current card
            if ($title){
                $data[] = [
                    'title'      => $title,
                    'link'       => $link,
                    'category' => $category,
                    'summary'    => $summary,  // may be empty if no categories exist
                ];
            }
        }
        // Step 3: Pass Data to a View
        return view('scraped-data', ['data' => $data]);
    }
    private function generateSummary($articleUrl, $client)
    {
        try {
            // 1. Fetch article content
            $articleResponse = $client->get($articleUrl);
            $articleHtml = $articleResponse->getBody()->getContents();
            
            // 2. Extract main content
            $articleDom = new DOMDocument();
            @$articleDom->loadHTML($articleHtml);
            $xpath = new DOMXPath($articleDom);
            
            // TechCrunch-specific content selector (adjust as needed)
            $contentNodes = $xpath->query('//div[contains(@class, "article-content")]//p');
            $contentText = '';
            foreach ($contentNodes as $node) {
                $contentText .= $node->textContent . "\n";
            }

            // 3. Call DeepSeek API
            $response = $client->post($this->deepseekEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->deepseekApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Summarize this article in 3 bullet points:\n" . 
                                        mb_substr($contentText, 0, 12000) // Truncate to token limit
                        ]
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            return $result['choices'][0]['message']['content'] ?? 'Summary unavailable';

        } catch (\Exception $e) {
            return 'Could not generate summary: ' . $e->getMessage();
        }
    }
}