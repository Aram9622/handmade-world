<?php

namespace Tests\Feature;

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
        $this->get('/')->assertOk()->assertDontSee('value="paintings"', false);
        $this->actingAs($admin)->post('/admin/menu/categories/create', [
            'slug' => 'Paintings',
            'names' => ['ru' => 'Картины и рисунки', 'hy' => 'Նկարներ', 'en' => 'Paintings and drawings'],
        ])->assertRedirect('/admin/menu#category-menu');

        $this->assertDatabaseHas('catalog_categories', ['slug' => 'paintings']);
        $this->get('/?category=paintings')->assertOk()->assertSee('Картины и рисунки');
        $this->get('/sell')->assertOk()->assertSee('value="paintings"', false);
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
    }
}
