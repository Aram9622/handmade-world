<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class ListingInteractionTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_members_can_like_once_and_remove_their_own_like(): void
    {
        $listing = $this->listing();
        $buyer = User::factory()->create();
        $url = $listing->publicUrl();
        $like = route('listings.like', $listing);

        $this->put($like)->assertRedirect(route('login'));
        $this->assertDatabaseCount('listing_likes', 0);

        $this->actingAs($buyer)->put($like)->assertRedirect($url.'#interactions');
        $this->put($like)->assertRedirect($url.'#interactions');
        $this->assertDatabaseCount('listing_likes', 1);
        $this->get($url)->assertOk()->assertSee('aria-pressed="true"', false);

        $this->actingAs(User::factory()->create())->delete($like)->assertRedirect($url.'#interactions');
        $this->assertDatabaseCount('listing_likes', 1);
        $this->actingAs($buyer)->delete($like)->assertRedirect($url.'#interactions');
        $this->assertDatabaseCount('listing_likes', 0);
    }

    public function test_comments_are_public_and_rendered_as_text(): void
    {
        $listing = $this->listing();
        $buyer = User::factory()->create(['name' => 'Покупатель']);
        $url = $listing->publicUrl();
        $commentUrl = route('listings.comment', $listing);

        $this->post($commentUrl, ['comment' => 'Вопрос'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('listing_comments', 0);
        $this->get(route('listings.participate', $listing))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => $buyer->email, 'password' => 'password'])
            ->assertRedirect(route('listings.participate', $listing));
        $this->get(route('listings.participate', $listing))->assertRedirect($url.'#interactions');

        $this->from($url)->post($commentUrl, ['comment' => '  '])->assertSessionHasErrors('comment');
        $body = 'Можно забрать завтра? <script>alert(1)</script>';
        $this->post($commentUrl, ['comment' => $body, 'user_id' => $listing->user_id])->assertRedirect($url.'#comments');
        $this->assertDatabaseHas('listing_comments', ['listing_id' => $listing->id, 'user_id' => $buyer->id, 'body' => $body]);

        $this->post(route('logout'));
        $this->get($url)->assertOk()
            ->assertSee('Покупатель')
            ->assertSee($body)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Поделиться объявлением');
    }

    private function listing(): Listing
    {
        return Listing::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Кружка',
            'description' => 'Ручная работа.',
            'category' => 'ceramics',
            'price' => 1500,
            'contact_email' => 'maker@example.com',
            'photo_path' => 'listings/cup.webp',
        ]);
    }
}
