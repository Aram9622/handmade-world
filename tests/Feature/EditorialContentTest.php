<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class EditorialContentTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_only_admins_can_edit_and_publish_localized_content(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $url = route('admin.editorial.update', ['paintings', 'en']);
        $publicUrl = route('catalog', ['category' => 'paintings']);

        $content = [
            'title' => 'Oil paintings',
            'heading' => 'Available works',
            'intro' => 'Paintings by independent artists.',
            'cta' => 'View listings',
            'alt' => 'An art studio',
            'history_title' => 'Painting through the ages',
            'gallery_title' => 'Selected works',
            'timeline' => [['period' => '1889', 'title' => 'An era', 'description' => 'Timeline text.']],
            'works' => [['title' => 'Gallery example', 'museum' => 'Museum', 'url' => 'https://example.com/work']],
        ];

        $this->actingAs(User::factory()->create())->put($url, $content)->assertForbidden();
        $this->actingAs($admin)->get($url)->assertOk()->assertSee('Контент');

        $this->put($url, array_replace($content, ['works' => [['title' => 'Bad link', 'url' => 'javascript:alert(1)']]]))
            ->assertSessionHasErrors('works.0.url');

        $photo = new UploadedFile(public_path('images/editorial/paintings.webp'), 'cover.webp', 'image/webp', null, true);
        $this->put($url, [...$content, 'cover' => $photo])->assertRedirect($url);

        $this->get($publicUrl)->assertOk()->assertSee('Цвет, свет и жест')->assertDontSee('Oil paintings');
        $this->withSession(['locale' => 'en'])->get($publicUrl)->assertOk()
            ->assertSee('Oil paintings')
            ->assertSee('Timeline text.')
            ->assertSee('Gallery example')
            ->assertSee(route('editorial.image', ['paintings', 'en', 'cover']));
        $this->get(route('editorial.image', ['paintings', 'en', 'cover']))->assertOk()
            ->assertHeader('Content-Type', 'image/webp');
    }

    public function test_an_admin_can_replace_homepage_text(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_super_admin' => true])->save();
        $url = route('admin.editorial.update', ['home', 'ru']);

        $this->actingAs($admin)->put($url, [
            'title' => 'Ручная работа',
            'heading' => 'Из мастерской',
            'intro' => 'Новая обложка витрины.',
            'cta' => 'В каталог',
            'alt' => 'Руки мастера',
            'directory_title' => 'Ремесленные разделы',
        ])->assertRedirect($url);

        $this->get('/')->assertOk()->assertSee('Новая обложка витрины.')->assertSee('Ремесленные разделы');
        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee('Browse new arrivals');
    }
}
