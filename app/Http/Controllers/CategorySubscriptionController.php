<?php

namespace App\Http\Controllers;

use App\Services\CatalogCategories;
use App\Services\CategorySubscriptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategorySubscriptionController extends Controller
{
    public function __construct(private readonly CategorySubscriptions $subscriptions, private readonly CatalogCategories $categories) {}

    public function participate(string $category): RedirectResponse
    {
        $this->ensureCategoryExists($category);

        return to_route('catalog', ['category' => $category])->withFragment('category-subscription');
    }

    public function store(Request $request, string $category): RedirectResponse
    {
        $this->ensureCategoryExists($category);
        $this->subscriptions->subscribe($request->user(), $category);

        return to_route('catalog', ['category' => $category])->withFragment('category-subscription')->with('status', __('Подписка оформлена.'));
    }

    public function destroy(Request $request, string $category): RedirectResponse
    {
        $this->ensureCategoryExists($category);
        $this->subscriptions->unsubscribe($request->user(), $category);

        return to_route('catalog', ['category' => $category])->withFragment('category-subscription')->with('status', __('Подписка отменена.'));
    }

    private function ensureCategoryExists(string $category): void
    {
        abort_unless(in_array($category, $this->categories->keys(), true), 404);
    }
}
