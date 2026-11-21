<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_follow_categories_and_browse_a_private_feed(): void
    {
        $categoryPath = '/categories/ceramics/subscription';
        $catalogPath = '/?category=ceramics#category-subscription';
        $this->get('/subscriptions')->assertRedirect('/login');
        $this->get($categoryPath)->assertRedirect('/login');

        $member = User::factory()->create();
        $maker = User::factory()->create();
        $details = [
            'user_id' => $maker->id,
            'category' => 'ceramics',
            'price' => 1200,
            'description' => 'Ручная работа.',
            'contact_email' => 'maker@example.com',
            'photo_path' => 'listings/cup.webp',
        ];
        Listing::create([...$details, 'title' => 'Белая чашка']);
        Listing::create([...$details, 'title' => 'Глиняная ваза']);
        Listing::create([...$details, 'title' => 'Картина маслом', 'category' => 'paintings']);
        $hidden = Listing::create([...$details, 'title' => 'Скрытая кружка']);
        $hidden->forceFill(['hidden_at' => now()])->save();

        $this->actingAs($member)->get('/subscriptions')->assertOk()->assertDontSee('Белая чашка');
        $this->put('/categories/invalid/subscription')->assertNotFound();
        $this->put($categoryPath)->assertRedirect($catalogPath);
        $this->put($categoryPath)->assertRedirect($catalogPath);
        $this->assertDatabaseCount('category_subscriptions', 1);

        $this->get('/?category=ceramics')->assertSee('Отписаться от категории');
        $this->get('/subscriptions')->assertOk()
            ->assertSeeInOrder(['Глиняная ваза', 'Белая чашка'])
            ->assertDontSee('Картина маслом')->assertDontSee('Скрытая кружка');
        $this->get('/subscriptions?q=чашка')->assertOk()->assertSee('Белая чашка')->assertDontSee('Глиняная ваза');
        $this->actingAs($maker)->get('/subscriptions')->assertOk()->assertDontSee('Белая чашка');

        $this->actingAs($member)->delete($categoryPath)->assertRedirect($catalogPath);
        $this->assertDatabaseCount('category_subscriptions', 0);
        $this->get('/?category=ceramics')->assertSee('Подписаться на категорию');
        $this->get('/subscriptions')->assertOk()->assertDontSee('Белая чашка');
    }
}
