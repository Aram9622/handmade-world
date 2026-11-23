<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_a_maker_can_publish_and_remove_a_listing(): void
    {
        Storage::fake('local');

        $this->get('/sell')->assertRedirect('/login');
        $this->post('/register', [
            'name' => 'Мастер',
            'email' => 'maker@example.com',
            'password' => 'test-password',
            'password_confirmation' => 'test-password',
        ])->assertRedirect('/sell');
        $owner = User::sole();
        $this->assertAuthenticatedAs($owner);

        $response = $this->post('/items', [
            'title' => 'Керамическая чашка',
            'category' => 'ceramics',
            'price' => 1400,
            'description' => 'Глазурованная кружка ручной работы.',
            'contact_email' => 'sales@example.com',
            'photo' => new UploadedFile(public_path('images/workshop.webp'), 'workshop.webp', 'image/webp', null, true),
        ]);
        $listing = Listing::sole();
        $response->assertRedirect(route('listings.show', $listing));
        Storage::disk('local')->assertExists($listing->photo_path);

        $this->post('/logout')->assertRedirect('/');
        $this->get('/?'.http_build_query(['q' => 'КЕРАМИЧЕСКАЯ', 'category' => 'ceramics', 'sort' => 'low']))
            ->assertOk()->assertSee('Керамическая чашка');
        $this->get('/?category=jewelry')->assertOk()->assertDontSee('Керамическая чашка', false);
        $this->get(route('listings.show', $listing))->assertOk()->assertSee('Написать продавцу');
        $this->get(route('listings.photo', $listing))->assertOk()->assertHeader('Content-Type', 'image/webp');

        $this->actingAs(User::factory()->create())->delete(route('listings.destroy', $listing))->assertForbidden();
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'test-password',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($owner);

        $this->delete(route('listings.destroy', $listing))->assertRedirect('/');
        $this->assertModelMissing($listing);
        Storage::disk('local')->assertMissing($listing->photo_path);
    }

    public function test_a_maker_can_manage_only_their_own_listings(): void
    {
        config(['filesystems.default' => 'public']);
        $disk = Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $data = [
            'title' => 'Чашка',
            'category' => 'ceramics',
            'price' => 1200,
            'description' => 'Изделие ручной работы.',
            'contact_email' => 'maker@example.com',
        ];
        $disk->put('listings/original.webp', file_get_contents(public_path('images/workshop.webp')));
        $listing = Listing::create([...$data, 'user_id' => $owner->id, 'photo_path' => 'listings/original.webp']);
        Listing::create([...$data, 'title' => 'Чужое изделие', 'user_id' => $other->id, 'photo_path' => 'listings/other.webp']);

        $edit = route('listings.edit', $listing);
        $update = route('listings.update', $listing);

        $this->get(route('listings.mine'))->assertRedirect(route('login'));
        $this->actingAs($other)->get($edit)->assertForbidden();
        $this->put($update, [...$data, 'price' => 1])->assertForbidden();
        $this->assertSame(1200, $listing->refresh()->price);

        $this->actingAs($owner)->get(route('listings.mine'))
            ->assertOk()->assertSee($listing->title)->assertDontSee('Чужое изделие');
        $this->get($edit)->assertOk()->assertSee('value="1200"', false);

        $this->put($update, [...$data, 'price' => 1500])->assertRedirect(route('listings.show', $listing));
        $this->assertSame(1500, $listing->refresh()->price);
        $this->assertSame('listings/original.webp', $listing->photo_path);
        $disk->assertExists($listing->photo_path);

        $this->from($edit)->put($update, [...$data, 'photo' => UploadedFile::fake()->create('text.txt')])
            ->assertRedirect($edit)->assertSessionHasErrors('photo');
        $disk->assertExists('listings/original.webp');

        $photo = new UploadedFile(public_path('images/workshop.webp'), 'replacement.webp', 'image/webp', null, true);
        $this->put($update, [...$data, 'photo' => $photo])->assertRedirect(route('listings.show', $listing));
        $disk->assertMissing('listings/original.webp');
        $disk->assertExists($listing->refresh()->photo_path);
        $this->get(route('listings.photo', $listing))->assertOk()->assertHeader('Content-Type', 'image/webp');
    }

    public function test_discounts_affect_catalog_prices_and_shared_listing_details(): void
    {
        $owner = User::factory()->create();
        $data = [
            'title' => 'Чашка & блюдце',
            'category' => 'ceramics',
            'price' => 1001,
            'description' => 'Керамика ручной работы.',
            'contact_email' => 'maker@example.com',
        ];
        $listing = Listing::create([...$data, 'user_id' => $owner->id, 'photo_path' => 'listings/cup.webp']);
        Listing::create([...$data, 'title' => 'Блюдо без скидки', 'price' => 900, 'user_id' => $owner->id, 'photo_path' => 'listings/plate.webp']);
        $update = route('listings.update', $listing);
        $url = route('listings.show', $listing);

        $this->actingAs($owner)->put($update, [...$data, 'discount_percent' => 100])
            ->assertSessionHasErrors('discount_percent');
        $this->assertSame(0, $listing->refresh()->discount_percent);

        $this->put($update, [...$data, 'discount_percent' => 25])->assertRedirect($url);
        $this->assertSame(25, $listing->refresh()->discount_percent);
        $this->get('/?sort=low')->assertSeeInOrder(['Чашка & блюдце', '750,75 ₽', 'Блюдо без скидки']);
        $this->get('/?sort=high')->assertSeeInOrder(['Блюдо без скидки', 'Чашка & блюдце']);

        $this->post(route('logout'));
        $this->get($url)->assertOk()
            ->assertSee('property="og:url" content="'.$url.'"', false)
            ->assertSee('property="og:image" content="'.route('listings.photo', $listing).'"', false)
            ->assertSee('750,75 ₽')
            ->assertSee('https://t.me/share/url?url='.rawurlencode($url))
            ->assertSee('https://vk.com/share.php?url='.rawurlencode($url));

        $this->actingAs($owner)->put($update, [...$data, 'discount_percent' => ''])->assertRedirect($url);
        $this->assertSame(0, $listing->refresh()->discount_percent);
    }

    public function test_a_maker_can_offer_pickup_and_delivery(): void
    {
        $owner = User::factory()->create();
        $data = [
            'title' => 'Кружка',
            'category' => 'ceramics',
            'price' => 1500,
            'description' => 'Ручная работа.',
            'contact_email' => 'maker@example.com',
        ];
        $listing = Listing::create([...$data, 'user_id' => $owner->id, 'photo_path' => 'listings/cup.webp']);
        $update = route('listings.update', $listing);
        $url = route('listings.show', $listing);

        $this->get($url)->assertOk()->assertSee('По договорённости');
        $this->actingAs($owner)->put($update, [...$data, 'fulfillment_method' => 'pickup'])
            ->assertSessionHasErrors('city');
        $this->put($update, [...$data, 'fulfillment_method' => 'courier'])
            ->assertSessionHasErrors('fulfillment_method');

        $this->put($update, [...$data, 'city' => 'Казань', 'fulfillment_method' => 'both'])->assertRedirect($url);
        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'city' => 'Казань', 'fulfillment_method' => 'both']);
        $this->get($url)->assertOk()->assertSee('Казань')->assertSee('Самовывоз и доставка');
        $this->get('/')->assertOk()->assertSee('Казань');

        $this->put($update, [...$data, 'city' => '', 'fulfillment_method' => 'delivery'])->assertRedirect($url);
        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'city' => null, 'fulfillment_method' => 'delivery']);
    }

    public function test_homepage_feed_uses_publication_date(): void
    {
        $details = [
            'user_id' => User::factory()->create()->id,
            'category' => 'ceramics',
            'price' => 1200,
            'description' => 'Ручная работа.',
            'contact_email' => 'maker@example.com',
            'photo_path' => 'listings/cup.webp',
        ];
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-21 09:00:00'));
        Listing::create([...$details, 'title' => 'Новое изделие']);
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-20 09:00:00'));
        Listing::create([...$details, 'title' => 'Раннее изделие']);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Новые изделия', 'Новое изделие', 'Раннее изделие'])
            ->assertSee('21 ноября 2026');
        $this->get('/?category=ceramics')->assertOk()
            ->assertSee('Результаты поиска')->assertDontSee('Новые изделия');
    }

    public function test_a_maker_can_add_and_remove_the_public_phone_number(): void
    {
        $owner = User::factory()->create();
        $data = [
            'title' => 'Кружка',
            'category' => 'ceramics',
            'price' => 1500,
            'description' => 'Ручная работа.',
            'contact_email' => 'maker@example.com',
        ];
        $listing = Listing::create([...$data, 'user_id' => $owner->id, 'photo_path' => 'listings/cup.webp']);
        $update = route('listings.update', $listing);
        $url = route('listings.show', $listing);

        $this->get($url)->assertOk()->assertDontSee('href="tel:', false);
        $this->actingAs($owner)->put($update, [...$data, 'contact_phone' => '+7 900 ABC-45-67'])
            ->assertSessionHasErrors('contact_phone');
        $this->put($update, [...$data, 'contact_phone' => '+7 (900) 123-45-67'])->assertRedirect($url);
        $this->assertSame('+79001234567', $listing->refresh()->contact_phone);

        $this->post(route('logout'));
        $this->get($url)->assertOk()->assertSee('href="tel:+79001234567"', false);

        $this->actingAs($owner)->put($update, [...$data, 'contact_phone' => ''])->assertRedirect($url);
        $this->assertNull($listing->refresh()->contact_phone);
    }

    public function test_categories_have_distinct_editorials_and_filter_their_listings(): void
    {
        $data = [
            'user_id' => User::factory()->create()->id,
            'price' => 1500,
            'description' => 'Ручная работа.',
            'contact_email' => 'maker@example.com',
            'photo_path' => 'listings/example.webp',
        ];
        Listing::create([...$data, 'title' => 'Пейзаж маслом', 'category' => 'paintings']);
        Listing::create([...$data, 'title' => 'Чашка из глины', 'category' => 'ceramics']);

        $this->get('/')->assertOk()->assertSee('Ремёсла и искусство')->assertSee('?category=paintings');
        $this->get('/?category=paintings')->assertOk()
            ->assertSeeInOrder(['Микеланджело', 'Ван Гог', 'По мотивам известных работ', 'Объявления мастеров', 'Пейзаж маслом'])
            ->assertDontSee('Чашка из глины');
        $this->get('/?category=ceramics')->assertOk()
            ->assertSee('Земля, вода и огонь')->assertSee('Чашка из глины')->assertDontSee('Пейзаж маслом');
        $this->get('/?category=paintings&q=Пейзаж')->assertOk()
            ->assertSee('Пейзаж маслом')->assertDontSee('История раздела');
    }

    public function test_an_admin_can_edit_listing_details_and_manage_the_gallery(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $data = [
            'category' => 'textile',
            'title' => 'Льняная скатерть',
            'description' => 'Лён ручной работы',
            'price' => 5000,
            'contact_email' => $owner->email,
        ];
        $listing = Listing::create([...$data, 'user_id' => $owner->id, 'photo_path' => 'listings/cover.webp']);
        $edit = route('admin.listings.edit', $listing);
        $save = route('admin.listings.save', $listing);

        $this->actingAs($owner)->get($edit)->assertForbidden();
        $this->put($save, $data)->assertForbidden();
        $this->actingAs($admin)->get($edit)->assertOk()
            ->assertSee('contenteditable="true"', false)->assertSee('name="gallery_photos[]"', false);

        $this->put($save, [
            ...$data,
            'description_html' => '<h3>Состав</h3><p><strong onclick="bad()">Лён</strong></p><script>bad()</script>',
            'characteristics' => [['label' => 'Размер', 'value' => '120 × 160 см'], ['label' => '', 'value' => '']],
            'gallery_photos' => [
                new UploadedFile(public_path('images/workshop.webp'), 'photo.webp', 'image/webp', null, true),
            ],
        ])->assertRedirect($edit);
        $listing->refresh();
        $id = array_key_first($listing->gallery);
        $path = $listing->gallery[$id];
        Storage::disk('local')->assertExists($path);
        $this->assertSame("Состав
Лён", $listing->description);
        $this->assertSame($owner->id, $listing->user_id);
        $photo = route('listings.gallery-photo', [$listing, $id]);
        $this->get(route('listings.show', $listing))->assertOk()
            ->assertSee('<strong>Лён</strong>', false)->assertDontSee('bad()')
            ->assertSee('120 × 160 см')->assertSee('data-gallery-thumb', false)
            ->assertSee('data-gallery-dialog', false);
        $this->get($photo)->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->get($edit)->assertOk()->assertSee('class="gallery-remove-icon"', false)
            ->assertSee('aria-label="Удалить фото 1"', false);

        $listing->forceFill(['hidden_at' => now()])->save();
        $this->actingAs(User::factory()->create())->get($photo)->assertNotFound();
        $this->actingAs($admin)->get($photo)->assertOk();
        $this->put($save, [...$data, 'remove_gallery' => [$id], 'characteristics' => [['label' => '', 'value' => '']]])
            ->assertRedirect($edit);
        $this->assertSame([], $listing->refresh()->characteristics);
        Storage::disk('local')->assertMissing($path);
        $this->get($photo)->assertNotFound();
    }
}
