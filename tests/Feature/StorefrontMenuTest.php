<?php

namespace Tests\Feature;

use App\Models\EditorialPage;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class StorefrontMenuTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_only_an_active_super_admin_can_manage_the_menu(): void
    {
        $this->get('/admin/menu')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/menu')->assertForbidden();
        $this->put('/admin/menu', ['items' => []])->assertForbidden();
    }

    public function test_saved_labels_visibility_and_order_apply_to_both_menus(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $member = User::factory()->create();
        $items = [];
        foreach (app(\App\Services\StorefrontMenu::class)->definitions() as $key => $item) {
            $items[$key] = [
                'labels' => ['ru' => $item['label'], 'en' => $item['label'], 'hy' => $item['label']],
                'position' => $item['default_position'],
                'enabled' => '1',
            ];
        }
        $items['catalog']['labels'] = ['ru' => 'Ремесленный рынок', 'en' => 'Craft market', 'hy' => 'Արհեստների շուկա'];
        $items['catalog']['position'] = 0;
        $items['categories']['enabled'] = '0';
        $items['admin']['labels']['en'] = 'Marketplace management';
        $items['category_ceramics']['labels'] = ['ru' => null, 'en' => 'Clay works', 'hy' => null];
        $items['category_ceramics']['position'] = 0;
        $items['category_jewelry']['enabled'] = '0';
        EditorialPage::create([
            'page' => 'ceramics',
            'locale' => 'ru',
            'content' => ['title' => 'Изделия из глины'],
            'images' => [],
        ]);

        $this->actingAs($admin)->get('/admin/menu')->assertOk()->assertSee('Меню витрины')->assertSee('Меню категорий');
        $this->from('/admin/menu')->put('/admin/menu', ['items' => ['catalog' => $items['catalog']]])
            ->assertSessionHasErrors('items');
        $this->assertDatabaseCount('storefront_menu_items', 0);

        $this->put('/admin/menu', ['items' => $items])->assertRedirect('/admin/menu');
        $this->get('/admin/menu')->assertSee('Craft market');
        $response = $this->actingAs($member)->withSession(['locale' => 'en'])->get('/');
        $response->assertOk()
            ->assertSeeInOrder(['Craft market', 'Главная', 'Craft market', 'Главная'])
            ->assertDontSee('Marketplace management')
            ->assertDontSee('href="'.route('catalog').'#category-heading"', false);
        $this->withSession(['locale' => 'hy'])->get('/')->assertSee('Արհեստների շուկա');

        $this->withSession(['locale' => 'en'])->get('/?category=ceramics')
            ->assertOk()
            ->assertSeeInOrder(['Clay works', 'Все категории'])
            ->assertSee('aria-current="page" >Clay works</a>', false)
            ->assertDontSee('href="'.route('catalog', ['category' => 'jewelry']).'"', false);

        $this->withSession(['locale' => 'ru'])->get('/?category=ceramics')
            ->assertSee('aria-current="page" >Изделия из глины</a>', false);
    }

    public function test_category_menu_entries_can_be_removed_and_added_back(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $link = 'href="'.route('catalog', ['category' => 'ceramics']).'"';
        $editPath = '/admin/menu#category-menu';

        $this->actingAs($admin)->get('/?category=paintings')->assertSee($link, false);
        $this->delete('/admin/menu/categories/category_ceramics')->assertRedirect($editPath);
        $this->get('/?category=paintings')->assertDontSee($link, false);
        $this->get('/?category=ceramics')->assertOk();
        $this->get('/admin/menu')->assertDontSee('value="category_ceramics"', false)
            ->assertDontSee('items[category_ceramics][position]', false);

        $this->post('/admin/menu/categories', ['item' => 'category_ceramics'])->assertRedirect($editPath);
        $this->get('/?category=paintings')->assertSee($link, false);
        $this->post('/admin/menu/categories', ['item' => 'category_ceramics'])->assertRedirect($editPath);
        $this->assertDatabaseCount('storefront_menu_items', 1);

        $this->postJson('/admin/menu/categories', ['item' => 'home'])->assertUnprocessable();
        $this->actingAs(User::factory()->create())
            ->delete('/admin/menu/categories/category_ceramics')->assertForbidden();
    }

    public function test_super_admin_can_create_a_new_catalog_category(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $member = User::factory()->create();
        $path = '/admin/menu/categories/create';
        $category = [
            'slug' => 'wood-craft',
            'names' => ['ru' => 'Изделия из дерева', 'hy' => 'Փայտե իրեր', 'en' => 'Wood crafts'],
        ];

        $this->actingAs($member)->post($path, $category)->assertForbidden();
        $this->actingAs($admin)->post($path, $category)->assertRedirect('/admin/menu#catalog-category-wood-craft');
        $this->postJson($path, $category)->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->postJson($path, [...$category, 'slug' => 'ceramics'])->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->get('/admin/content/wood-craft/ru')->assertOk()->assertSee('Изделия из дерева');
        $this->get('/admin/menu')->assertOk()->assertSee('Новая категория')->assertDontSee('items[category_wood-craft][position]', false);

        $this->actingAs($member)->withSession(['locale' => 'en'])
            ->get('/?category=wood-craft')->assertOk()->assertSee('Wood crafts');
        $this->get('/sell')->assertOk()->assertSee('value="wood-craft"', false);
        $this->post('/items', [
            'title' => 'Carved tray',
            'category' => 'wood-craft',
            'price' => 1500,
            'description' => 'Hand-carved wooden tray.',
            'contact_email' => 'maker@example.com',
            'photo' => new UploadedFile(public_path('images/workshop.webp'), 'workshop.webp', 'image/webp', null, true),
        ])->assertRedirect();
        $this->assertDatabaseHas('listings', ['category' => 'wood-craft', 'title' => 'Carved tray']);
        $viewer = User::factory()->create();
        $this->actingAs($viewer)->put('/categories/wood-craft/subscription')->assertRedirect();
        $this->get('/subscriptions')->assertOk()->assertSee('Carved tray');
        $this->get('/items/'.Listing::sole()->id)->assertOk()->assertSee('Wood crafts');
        $this->assertStringContainsString(
            '?category=wood-craft',
            $this->get('/sitemap.xml')->assertOk()->streamedContent(),
        );
    }
}
