<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontMenuTest extends TestCase
{
    use RefreshDatabase;

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
        foreach (config('storefront_menu') as $key => $item) {
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

        $this->actingAs($admin)->get('/admin/menu')->assertOk()->assertSee('Меню витрины');
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
    }
}
