<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ListingController extends Controller
{
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
            $query->orderBy('price', $sort === 'low' ? 'asc' : 'desc');
        }

        $listings = $query->latest('id')->paginate(12)->withQueryString();

        return view('catalog', compact('listings', 'search', 'category', 'sort'));
    }

    public function show(Listing $listing)
    {
        return view('listing', compact('listing'));
    }

    public function photo(Listing $listing)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($listing->photo_path), 404);

        return $disk->response($listing->photo_path, null, [
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(array_keys(Listing::CATEGORIES))],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'contact_email' => ['required', 'email', 'max:254'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('photo')->store('listings', 'local');
        if ($path === false) {
            throw new RuntimeException('Не удалось сохранить фотографию.');
        }

        unset($data['photo']);
        try {
            $listing = Listing::create([...$data, 'user_id' => $request->user()->id, 'photo_path' => $path]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return to_route('listings.show', $listing)->with('status', 'Объявление опубликовано.');
    }

    public function destroy(Request $request, Listing $listing)
    {
        abort_unless($request->user()->id === $listing->user_id, 403);

        $listing->delete();
        Storage::disk('local')->delete($listing->photo_path);

        return to_route('catalog')->with('status', 'Объявление снято с продажи.');
    }
}
