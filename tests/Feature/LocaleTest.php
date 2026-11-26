<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithCatalogCategories;

class LocaleTest extends TestCase
{
    use RefreshDatabase, WithCatalogCategories;

    public function test_language_switch_preserves_the_category_and_persists_on_other_pages(): void
    {
        $category = '/?category=paintings';

        $this->get($category.'&lang=hy')->assertRedirectContains($category);
        $this->get($category)->assertOk()
            ->assertSee('<html lang="hy">', false)
            ->assertSee('Գույն, լույս և շարժում')
            ->assertSee('Հետիմպրեսիոնիզմ');
        $this->get('/register')->assertOk()->assertSee('Ստեղծել հաշիվ');

        $this->get('/register?lang=en')->assertRedirect('/register');
        $this->get('/register')->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Create an account');
        $this->get('/?lang=unsupported')->assertOk()->assertSee('Browse new arrivals');

        $this->get('/?lang=ru')->assertRedirect('/');
        $this->get('/')->assertOk()->assertSee('<html lang="ru">', false)->assertSee('Смотреть новинки');
    }

    public function test_validation_uses_the_selected_language(): void
    {
        $this->withSession(['locale' => 'hy'])->from('/register')
            ->post('/register', ['email' => 'incorrect'])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email' => 'Մուտքագրեք վավեր էլ․ փոստի հասցե։']);
    }
}
