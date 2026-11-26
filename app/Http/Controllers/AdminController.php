<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingComment;
use App\Models\MarketplaceSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public const SECTIONS = ['listings' => 'Объявления', 'comments' => 'Комментарии', 'users' => 'Пользователи'];

    private const CONTENT_STATUSES = ['visible' => 'Опубликовано', 'hidden' => 'Скрыто'];

    private const ACCOUNT_STATUSES = ['active' => 'Активные аккаунты', 'suspended' => 'Заблокированные аккаунты', 'admins' => 'Суперадминистраторы'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'section' => ['nullable', Rule::in(array_keys(self::SECTIONS))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $section = $filters['section'] ?? 'listings';
        $statuses = $section === 'users' ? self::ACCOUNT_STATUSES : self::CONTENT_STATUSES;
        $options = $request->validate([
            'status' => ['nullable', Rule::in(array_keys($statuses))],
            'sort' => ['nullable', Rule::in(['new', 'old'])],
        ]);
        $status = $options['status'] ?? '';
        $sort = $options['sort'] ?? 'new';
        $search = $filters['q'] ?? '';
        $pattern = '%'.$search.'%';

        $query = match ($section) {
            'users' => User::query()->where(fn ($query) => $query->whereLike('name', $pattern)->orWhereLike('email', $pattern)),
            'listings' => Listing::query()->with('owner:id,name')->whereLike('title', $pattern),
            'comments' => ListingComment::query()->with(['author:id,name', 'listing:id,title'])->whereLike('body', $pattern),
        };

        match ($status) {
            'visible' => $query->whereNull('hidden_at'),
            'hidden' => $query->whereNotNull('hidden_at'),
            'active' => $query->whereNull('suspended_at'),
            'suspended' => $query->whereNotNull('suspended_at'),
            'admins' => $query->where('is_super_admin', true),
            default => null,
        };

        $direction = $sort === 'old' ? 'asc' : 'desc';
        $records = $query->orderBy('created_at', $direction)->orderBy('id', $direction)->paginate(20)->withQueryString();

        $totals = [
            'listings' => Listing::count(),
            'comments' => ListingComment::count(),
            'users' => User::count(),
        ];

        $activity = $this->weeklyActivity($section);

        return view('admin.index', [
            'sections' => self::SECTIONS,
            ...compact('records', 'section', 'search', 'totals', 'activity', 'statuses', 'status', 'sort'),
        ]);
    }

    private function weeklyActivity(string $section): array
    {
        $today = now()->startOfDay();
        $firstDay = $today->copy()->subDays(6);
        $query = match ($section) {
            'users' => User::query(),
            'listings' => Listing::query(),
            'comments' => ListingComment::query(),
        };

        $counts = $query->where('created_at', '>=', $firstDay)
            ->where('created_at', '<', $today->copy()->addDay())
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        $activity = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $day = $firstDay->copy()->addDays($offset);
            $activity[] = ['day' => $day, 'count' => (int) $counts->get($day->toDateString(), 0)];
        }

        return $activity;
    }

    public function prices(): View
    {
        return view('admin.prices', ['settings' => MarketplaceSetting::current()]);
    }

    public function updatePrices(Request $request): RedirectResponse
    {
        $settings = $request->validate([
            'use_default_price' => ['required', 'boolean'],
            'default_price' => [Rule::requiredIf($request->boolean('use_default_price')), 'nullable', ...Listing::PRICE_RULES],
        ]);

        MarketplaceSetting::whereKey(1)->update([
            'use_default_price' => $settings['use_default_price'],
            'default_price' => $settings['default_price'] ?? null,
        ]);

        return to_route('admin.prices.edit')->with('status', __('Изменения сохранены.'));
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
