<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmptyCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_start_empty_and_an_admin_can_create_paintings(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();

        $this->assertDatabaseCount('catalog_categories', 0);
        $this->get('/')->assertOk()->assertDontSee('class="category-nav"', false);
        $this->actingAs($admin)->post('/admin/menu/categories/create', [
            'slug' => 'Paintings',
            'names' => ['ru' => 'Картины и рисунки', 'hy' => 'Նկարներ', 'en' => 'Paintings and drawings'],
        ])->assertRedirect('/admin/menu#catalog-category-paintings');

        $this->assertDatabaseHas('catalog_categories', ['slug' => 'paintings']);
        $this->get('/admin/menu')->assertOk()
            ->assertSee('id="catalog-category-paintings"', false)
            ->assertSeeInOrder(['Картины и рисунки', 'Открыть на витрине', 'Редактировать']);
        $this->get('/')->assertOk()->assertDontSee('Картины и рисунки')->assertDontSee('class="category-nav"', false);
        $this->get('/?category=paintings')->assertOk()->assertSee('Картины и рисунки');
        $this->get('/sell')->assertOk()->assertSee('value="paintings"', false);
        $listing = Listing::create([
            'user_id' => $admin->id,
            'category' => 'paintings',
            'title' => 'Пейзаж',
            'description' => 'Картина маслом.',
            'price' => 1200,
            'contact_email' => $admin->email,
            'photo_path' => 'listings/example.webp',
        ]);
        $this->get('/')->assertOk()->assertSeeInOrder(['class="category-nav"', 'Картины и рисунки', 'home-cover', 'class="category-tile"'], false)
            ->assertSee('value="paintings"', false);
        $listing->forceFill(['hidden_at' => now()])->save();
        $this->get('/')->assertOk()->assertDontSee('Картины и рисунки')->assertDontSee('value="paintings"', false);
        $listing->delete();

        $this->get('/admin/menu')->assertSee('Удалить категорию');
        $deletePath = '/admin/catalog/categories/paintings';
        $this->actingAs(User::factory()->create())->delete($deletePath)->assertForbidden();
        $this->actingAs($admin)->post('/admin/menu/categories', ['item' => 'category_paintings'])->assertRedirect();
        $this->delete($deletePath)->assertRedirect('/admin/menu#category-menu');
        $this->assertDatabaseMissing('catalog_categories', ['slug' => 'paintings']);
        $this->assertDatabaseMissing('storefront_menu_items', ['key' => 'category_paintings']);
        $this->get('/')->assertOk()->assertDontSee('Картины и рисунки');
        $this->get('/sell')->assertOk()->assertDontSee('value="paintings"', false);
    }

    public function test_migration_preserves_only_legacy_categories_with_existing_data(): void
    {
        $member = User::factory()->create();
        DB::table('category_subscriptions')->insert(['user_id' => $member->id, 'category' => 'ceramics']);

        $migration = require database_path('migrations/2026_11_23_000000_preserve_used_legacy_catalog_categories.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseCount('catalog_categories', 1);
        $this->assertDatabaseHas('catalog_categories', ['slug' => 'ceramics']);
        $this->get('/?category=ceramics')->assertOk()->assertSee('Керамика');

        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $this->actingAs($admin)->delete('/admin/catalog/categories/ceramics')
            ->assertRedirect('/admin/menu#category-menu')->assertSessionHas('category_delete_error');
        $this->assertDatabaseHas('catalog_categories', ['slug' => 'ceramics']);
        $this->assertDatabaseHas('category_subscriptions', ['user_id' => $member->id, 'category' => 'ceramics']);
    }

    public function test_a_category_can_have_its_own_palette_and_return_to_defaults(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $palette = ['background' => '#101010', 'accent' => '#eeeeee'];
        $this->actingAs($admin)->post('/admin/menu/categories/create', [
            'slug' => 'woodwork',
            'names' => ['ru' => 'Дерево', 'hy' => 'Փայտ', 'en' => 'Woodwork'],
            'custom_palette' => '1',
            'palette' => $palette,
        ])->assertRedirect('/admin/menu#catalog-category-woodwork');

        $this->get('/?category=woodwork')->assertOk()
            ->assertSee('--category-background:#101010;--category-accent:#eeeeee;--category-on-background:#ffffff;--category-on-accent:#000000', false);
        $this->get('/')->assertOk()->assertDontSee('--category-background', false);
        $this->get('/admin/content/woodwork/en')->assertOk()->assertSee('value="#101010"', false);

        $path = '/admin/catalog/categories/woodwork/palette/en';
        $this->patchJson($path, ['custom_palette' => true, 'palette' => ['background' => '#ffffff']])
            ->assertUnprocessable()->assertJsonValidationErrors('palette.accent');
        $this->patchJson($path, ['custom_palette' => true, 'palette' => [...$palette, 'accent' => '#fff;']])
            ->assertUnprocessable()->assertJsonValidationErrors('palette.accent');
        $this->patch($path, ['custom_palette' => true, 'palette' => [...$palette, 'accent' => '#45624d']])
            ->assertRedirect('/admin/content/woodwork/en#category-palette');
        $this->get('/?category=woodwork')->assertSee('--category-accent:#45624d', false);
        $this->patch($path, ['custom_palette' => false])->assertRedirect();
        $this->get('/?category=woodwork')->assertOk()->assertDontSee('--category-background', false);
    }
}
