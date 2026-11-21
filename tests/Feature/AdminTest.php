<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_access_is_granted_from_the_console(): void
    {
        $user = User::factory()->create();
        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/admin')->assertForbidden();

        $this->artisan('admin:grant', ['email' => $user->email])->assertSuccessful();
        $this->actingAs($user->refresh())->get('/admin')->assertOk()->assertSee('Управление площадкой');
        $this->get('/admin?section=users')->assertOk()
            ->assertSee('<table class="admin-table">', false)
            ->assertSee($user->email)
            ->assertSee('Суперадминистратор');
        $this->patch(route('admin.users.update', $user), ['suspended' => 1])->assertForbidden();
    }

    public function test_the_console_can_reset_only_an_existing_admin_password(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true, 'remember_token' => 'old-token'])->save();
        $member = User::factory()->create();

        $this->artisan('admin:reset-password', ['email' => $member->email])->assertFailed();

        $this->artisan('admin:reset-password', ['email' => $admin->email])
            ->expectsQuestion('New password', 'new-admin-password')
            ->expectsQuestion('Confirm new password', 'not-the-same-password')
            ->assertFailed();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $admin->refresh()->password));

        $this->artisan('admin:reset-password', ['email' => $admin->email])
            ->expectsQuestion('New password', 'new-admin-password')
            ->expectsQuestion('Confirm new password', 'new-admin-password')
            ->assertSuccessful();
        $this->assertNull($admin->refresh()->remember_token);
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'new-admin-password'])
            ->assertRedirect(route('admin.index'));
    }

    public function test_admin_can_moderate_public_content_and_suspend_accounts(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $maker = User::factory()->create();
        $listing = Listing::create([
            'user_id' => $maker->id,
            'title' => 'Кружка с узором',
            'category' => 'ceramics',
            'description' => 'Изделие ручной работы.',
            'price' => 500,
            'contact_email' => $maker->email,
            'photo_path' => 'listings/cup.webp',
        ]);
        $comment = $listing->comments()->create(['user_id' => $maker->id, 'body' => 'Комментарий для проверки.']);
        $url = route('listings.show', $listing);

        $this->actingAs($maker)->patch(route('admin.comments.update', $comment), ['hidden' => 1])->assertForbidden();
        $this->actingAs($admin)->from('/admin')->patch(route('admin.comments.update', $comment), ['hidden' => 1])->assertRedirect('/admin');
        $this->get($url)->assertOk()->assertDontSee($comment->body);

        $this->patch(route('admin.listings.update', $listing), ['hidden' => 1, 'moderation_reason' => 'Проверка описания'])->assertRedirect();
        $this->actingAs($maker)->get('/my-items')->assertOk()->assertSee('Проверка описания');
        $this->post(route('logout'));
        $this->get($url)->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee($listing->title);
        $this->assertStringNotContainsString($url, $this->get('/sitemap.xml')->streamedContent());

        $this->actingAs($admin)->get('/admin?section=comments')->assertOk()->assertSee($comment->body);
        $this->patch(route('admin.comments.update', $comment), ['hidden' => 0])->assertRedirect();
        $this->patch(route('admin.listings.update', $listing), ['hidden' => 0])->assertRedirect();
        $this->get($url)->assertOk()->assertSee($comment->body);

        $this->patch(route('admin.users.update', $maker), ['suspended' => 1])->assertRedirect();
        $this->actingAs($maker->refresh())->get('/my-items')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->from('/login')->post('/login', ['email' => $maker->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->actingAs($admin)->patch(route('admin.users.update', $maker), ['suspended' => 0])->assertRedirect();
        $this->assertNull($maker->refresh()->suspended_at);
    }
}
