<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

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
}
