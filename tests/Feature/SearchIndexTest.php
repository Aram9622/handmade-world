<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class SearchIndexTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_product_pages_expose_the_discounted_price_in_safe_structured_data(): void
    {
        $listing = $this->createListing();
        $url = route('listings.show', $listing);

        $response = $this->get($url)->assertOk()
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('<title>Чашка &amp; блюдце', false)
            ->assertSee('750,75 ₽')
            ->assertDontSee('</script><script>alert(1)</script>', false);
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $response->getContent(), $matches);
        $product = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Product', $product['@type']);
        $this->assertSame($listing->description, $product['description']);
        $this->assertSame([route('listings.photo', $listing)], $product['image']);
        $this->assertSame('750.75', $product['offers']['price']);
        $this->assertSame('RUB', $product['offers']['priceCurrency']);
    }

    public function test_sitemap_advertises_live_listings_and_catalog_categories(): void
    {
        $listing = $this->createListing();
        $categoryUrl = route('catalog', ['category' => 'ceramics']);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('sitemap'));

        $response = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $map = simplexml_load_string($response->streamedContent());

        $locations = [];
        foreach ($map->url as $entry) {
            $locations[] = (string) $entry->loc;
        }
        $this->assertSame([route('catalog'), $categoryUrl, route('listings.show', $listing)], $locations);

        $this->get($categoryUrl)->assertOk()
            ->assertSee('Керамика ручной работы')
            ->assertSee('<link rel="canonical" href="'.$categoryUrl.'">', false);
        $this->get('/?q=чашка')->assertOk()->assertSee('name="robots" content="noindex, follow"', false);
        $this->get('/?sort=low')->assertOk()->assertSee('name="robots" content="noindex, follow"', false);
        $this->get('/login')->assertOk()->assertSee('name="robots" content="noindex, follow"', false);
    }

    private function createListing(): Listing
    {
        return Listing::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Чашка & блюдце',
            'description' => 'Керамика ручной работы </script><script>alert(1)</script>',
            'category' => 'ceramics',
            'price' => 1001,
            'discount_percent' => 25,
            'contact_email' => 'maker@example.com',
            'photo_path' => 'listings/cup.webp',
        ]);
    }
}
