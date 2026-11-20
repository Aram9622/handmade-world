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

    public function test_a_maker_can_manage_only_their_own_listings(): void
    {
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
}
