<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveListingRequest;
use App\Models\Listing;
use App\Services\CatalogCategories;
use App\Services\CategorySubscriptions;
use App\Services\EditorialContent;
use App\Services\ListingService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    private const CONFLICT_MESSAGE = 'Объявление изменилось. Обновите страницу и попробуйте ещё раз.';

    public function __construct(
        private readonly ListingService $listings,
        private readonly FilesystemAdapter $photos,
    ) {}

    public function index(Request $request, EditorialContent $content, CategorySubscriptions $subscriptions, CatalogCategories $categories)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in($categories->keys())],
            'sort' => ['nullable', Rule::in(['new', 'low', 'high'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $category = $filters['category'] ?? '';
        $sort = $filters['sort'] ?? 'new';
        $subscriptionFeed = $request->routeIs('subscriptions.index');
        $user = $request->user();
        $subscribedCategories = $user && ($subscriptionFeed || $category !== '') ? $subscriptions->categories($user) : [];
        $query = Listing::visible();

        if ($subscriptionFeed) {
            $query->whereIn('category', $subscribedCategories);
        }

        if ($search !== '') {
            $escapedSearch = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search));
            $pattern = '%'.$escapedSearch.'%';
            if ($query->getConnection()->getDriverName() === 'sqlite') {
                $query->getConnection()->getPdo()->sqliteCreateFunction('unicode_lower', mb_strtolower(...), 1);
                $query->whereRaw("(unicode_lower(title) LIKE ? ESCAPE '!' OR unicode_lower(description) LIKE ? ESCAPE '!')", [$pattern, $pattern]);
            } else {
                $query->whereRaw("(LOWER(title) LIKE ? ESCAPE '!' OR LOWER(description) LIKE ? ESCAPE '!')", [$pattern, $pattern]);
            }
        }

        if ($category !== '') {
            $query->where('category', $category);
        }
        if ($sort !== 'new') {
            $direction = $sort === 'low' ? 'asc' : 'desc';
            $query->orderByRaw('price * (100 - discount_percent) '.$direction);
        }

        $listings = $query->latest('created_at')->latest('id')->paginate(12)->withQueryString();
        $showFeed = $search === '' && $category === '' && $sort === 'new';

        $editorial = $content->all(app()->getLocale());

        $populatedCategories = array_intersect_key(
            $editorial,
            Listing::visible()->distinct()->pluck('category')->flip()->all(),
        );

        return view('catalog', compact('editorial', 'listings', 'search', 'category', 'sort', 'showFeed', 'subscriptionFeed', 'subscribedCategories', 'populatedCategories'));
    }

    public function show(Request $request, Listing $listing, EditorialContent $editorial)
    {
        $this->ensureListingCanBeViewed($request, $listing);

        $categoryTitle = $editorial->get($listing->category, app()->getLocale())['title'];
        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $listing->title,
            'description' => $listing->description,
            'image' => [route('listings.photo', $listing)],
            'category' => $categoryTitle,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('listings.show', $listing),
                'priceCurrency' => 'RUB',
                'price' => number_format($listing->salePriceInKopecks() / 100, 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
            ],
        ];

        $listing->loadCount('likes');
        $user = $request->user();
        $liked = $user && $listing->likes()->whereKey($user->id)->exists();
        $comments = $listing->comments()->whereNull('hidden_at')->with('author:id,name')->latest('id')->paginate(15, pageName: 'comments_page')
            ->fragment('comments');

        return view('listing', compact('listing', 'product', 'liked', 'comments', 'categoryTitle'));
    }

    public function photo(Request $request, Listing $listing)
    {
        $this->ensureListingCanBeViewed($request, $listing);
        abort_unless($this->photos->exists($listing->photo_path), 404);

        return $this->photos->response($listing->photo_path, null, [
            'Cache-Control' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function ensureListingCanBeViewed(Request $request, Listing $listing): void
    {
        $user = $request->user();
        abort_if($listing->hidden_at !== null && ! $user?->is_super_admin && $user?->id !== $listing->user_id, 404);
    }

    public function mine(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)->latest('id')->paginate(12);

        return view('my-listings', compact('listings'));
    }

    public function create(EditorialContent $editorial)
    {
        return view('sell', ['listing' => null, 'categories' => $editorial->all(app()->getLocale())]);
    }

    public function edit(Listing $listing, EditorialContent $editorial)
    {
        return view('sell', ['listing' => $listing, 'categories' => $editorial->all(app()->getLocale())]);
    }

    public function store(SaveListingRequest $request)
    {
        $listing = $this->listings->create(
            $request->user()->id,
            $request->safe()->except('photo'),
            $request->file('photo'),
        );

        return to_route('listings.show', $listing)->with('status', __('Объявление опубликовано.'));
    }

    public function update(SaveListingRequest $request, Listing $listing)
    {
        $updated = $this->listings->update($listing, $request->safe()->except('photo'), $request->file('photo'));
        abort_unless($updated, 409, __(self::CONFLICT_MESSAGE));

        return to_route('listings.show', $listing)->with('status', __('Изменения сохранены.'));
    }

    public function destroy(Listing $listing)
    {
        abort_unless($this->listings->delete($listing), 409, __(self::CONFLICT_MESSAGE));

        return to_route('catalog')->with('status', __('Объявление снято с продажи.'));
    }
}
