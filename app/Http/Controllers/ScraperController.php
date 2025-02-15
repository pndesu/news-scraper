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
        $titles = $xpath->query('//h3[@class="loop-card__title"]/a');
        $links = $xpath->query('//h3[@class="loop-card__title"]/a/@href');

        $data = [];
        foreach ($titles as $index => $title) {
            $data[] = [
                'title' => $title->nodeValue,
                'link' => $links[$index]->nodeValue,
            ];
        }

        // Step 3: Pass Data to a View
        return view('scraped-data', ['data' => $data]);
    }
}