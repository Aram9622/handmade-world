<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Services\CatalogCategories;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use XMLWriter;

class SearchIndexController extends Controller
{
    public function robots(): Response
    {
        return response("User-agent: *\nDisallow:\nSitemap: ".route('sitemap')."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(CatalogCategories $catalog): StreamedResponse
    {
        return response()->stream(function () use ($catalog): void {
            $xml = new XMLWriter;
            $xml->openUri('php://output');
            $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('urlset');
            $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            $this->writeUrl($xml, route('catalog'));

            $categories = Listing::visible()->distinct()->pluck('category');
            foreach ($categories->intersect($catalog->keys()) as $category) {
                $this->writeUrl($xml, route('catalog', ['category' => $category]));
            }

            foreach (Listing::visible()->select(['id', 'updated_at'])->lazyById() as $listing) {
                $this->writeUrl($xml, route('listings.show', $listing), $listing->updated_at?->toAtomString());
            }

            $xml->endElement();
            $xml->endDocument();
            $xml->flush();
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function writeUrl(XMLWriter $xml, string $location, ?string $lastModified = null): void
    {
        $xml->startElement('url');
        $xml->writeElement('loc', $location);
        if ($lastModified !== null) {
            $xml->writeElement('lastmod', $lastModified);
        }
        $xml->endElement();
    }
}
