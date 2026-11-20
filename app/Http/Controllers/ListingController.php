<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveListingRequest;
use App\Models\Listing;
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

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(array_keys(Listing::CATEGORIES))],
            'sort' => ['nullable', Rule::in(['new', 'low', 'high'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $category = $filters['category'] ?? '';
        $sort = $filters['sort'] ?? 'new';
        $query = Listing::query();

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

        return view('catalog', compact('listings', 'search', 'category', 'sort', 'showFeed'));
    }

    public function show(Request $request, Listing $listing)
    {
        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $listing->title,
            'description' => $listing->description,
            'image' => [route('listings.photo', $listing)],
            'category' => __(Listing::CATEGORIES[$listing->category]),
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
        $comments = $listing->comments()->with('author:id,name')->latest('id')->paginate(15, pageName: 'comments_page')
            ->fragment('comments');

        return view('listing', compact('listing', 'product', 'liked', 'comments'));
    }

    public function photo(Listing $listing)
    {
        abort_unless($this->photos->exists($listing->photo_path), 404);

        return $this->photos->response($listing->photo_path, null, [
            'Cache-Control' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function mine(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)->latest('id')->paginate(12);

        return view('my-listings', compact('listings'));
    }

    public function edit(Listing $listing)
    {
        return view('sell', compact('listing'));
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
