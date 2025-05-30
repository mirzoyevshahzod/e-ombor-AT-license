<?php

namespace App\Http\Controllers;

use App\Exports\QuotesExport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Quote;

class ScrapingController extends Controller
{
    public function scrapeQuotes(): JsonResponse
    {
        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', 'https://quotes.toscrape.com/');

        $quoteElements = $crawler->filter('.quote');
        $savedQuotes = [];

        foreach ($quoteElements as $element) {
            $quoteCrawler = new Crawler($element);

            $rawText = $quoteCrawler->filter('.text')->text();
            $text = str_replace(['“', '”'], '', $rawText);
            $author = $quoteCrawler->filter('.author')->text();

            $tags = $quoteCrawler->filter('.tag')->each(function (Crawler $tag) {
                return $tag->text();
            });

            // Bazaga yozish
            $quote = Quote::updateOrCreate(
                ['text' => $text], // unique text bilan qidiriladi
                ['author' => $author, 'tags' => $tags]
            );

            $savedQuotes[] = $quote;
        }

        return response()->json([
            'message' => count($savedQuotes) . ' ta quote saqlandi',
            'data' => $savedQuotes
        ]);
    }

    public function viewQuotes()
    {
        $quotes = Quote::query()->latest()->get(); // so‘nggi quote’lar

        return view('quote/index', compact('quotes'));
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new QuotesExport, 'quotes.xlsx');
    }

}
