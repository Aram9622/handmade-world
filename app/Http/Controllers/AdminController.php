<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingComment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public const SECTIONS = ['listings' => 'Объявления', 'comments' => 'Комментарии', 'users' => 'Пользователи'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'section' => ['nullable', Rule::in(array_keys(self::SECTIONS))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $section = $filters['section'] ?? 'listings';
        $search = $filters['q'] ?? '';
        $pattern = '%'.$search.'%';

        $query = match ($section) {
            'users' => User::query()->where(fn ($query) => $query->whereLike('name', $pattern)->orWhereLike('email', $pattern)),
            'listings' => Listing::query()->with('owner:id,name')->whereLike('title', $pattern),
            'comments' => ListingComment::query()->with(['author:id,name', 'listing:id,title'])->whereLike('body', $pattern),
        };

        $records = $query->latest('id')->paginate(20)->withQueryString();

        $totals = [
            'listings' => Listing::count(),
            'comments' => ListingComment::count(),
            'users' => User::count(),
        ];

        return view('admin.index', ['sections' => self::SECTIONS, ...compact('records', 'section', 'search', 'totals')]);
    }

    public function account(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_super_admin, 403);
        $state = $request->validate(['suspended' => ['required', 'boolean']]);

        User::whereKey($user->id)->where('is_super_admin', false)->update([
            'suspended_at' => $state['suspended'] ? now() : null,
        ]);

        return back()->with('status', __('Изменения сохранены.'));
    }

    public function listing(Request $request, Listing $listing): RedirectResponse
    {
        $state = $request->validate([
            'hidden' => ['required', 'boolean'],
            'moderation_reason' => ['nullable', 'string', 'max:500'],
        ]);
        $listing->forceFill([
            'hidden_at' => $state['hidden'] ? now() : null,
            'moderation_reason' => $state['hidden'] ? ($state['moderation_reason'] ?? null) : null,
        ])->save();

        return back()->with('status', __('Изменения сохранены.'));
    }

    public function comment(Request $request, ListingComment $comment): RedirectResponse
    {
        $state = $request->validate(['hidden' => ['required', 'boolean']]);
        $comment->forceFill(['hidden_at' => $state['hidden'] ? now() : null])->save();

        return back()->with('status', __('Изменения сохранены.'));
    }
}
