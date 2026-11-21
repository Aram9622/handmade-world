<?php

namespace App\Http\Controllers;

use App\Services\CategorySubscriptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategorySubscriptionController extends Controller
{
    public function __construct(private readonly CategorySubscriptions $subscriptions) {}

    public function participate(string $category): RedirectResponse
    {
        return to_route('catalog', ['category' => $category])->withFragment('category-subscription');
    }

    public function store(Request $request, string $category): RedirectResponse
    {
        $this->subscriptions->subscribe($request->user(), $category);

        return $this->participate($category)->with('status', __('Подписка оформлена.'));
    }

    public function destroy(Request $request, string $category): RedirectResponse
    {
        $this->subscriptions->unsubscribe($request->user(), $category);

        return $this->participate($category)->with('status', __('Подписка отменена.'));
    }
}
