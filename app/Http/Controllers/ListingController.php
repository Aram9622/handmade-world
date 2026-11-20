<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveListingRequest;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $path = $this->storePhoto($request->file('photo'));

        try {
            $listing = Listing::create([
                ...$request->safe()->except('photo'),
                'user_id' => $request->user()->id,
                'photo_path' => $path,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return to_route('listings.show', $listing)->with('status', 'Объявление опубликовано.');
    }

    public function update(SaveListingRequest $request, Listing $listing)
    {
        $data = $request->safe()->except('photo');
        $oldPhoto = $listing->photo_path;

        if ($request->hasFile('photo')) {
            $newPhoto = $this->storePhoto($request->file('photo'));

            try {
                $updated = Listing::whereKey($listing->id)->where('photo_path', $oldPhoto)
                    ->update([...$data, 'photo_path' => $newPhoto]);
                abort_unless($updated, 409, 'Объявление изменилось. Обновите страницу и попробуйте ещё раз.');
            } catch (Throwable $exception) {
                Storage::disk('local')->delete($newPhoto);
                throw $exception;
            }

            Storage::disk('local')->delete($oldPhoto);
        } else {
            $listing->update($data);
        }

        return to_route('listings.show', $listing)->with('status', 'Изменения сохранены.');
    }

    public function destroy(Listing $listing)
    {
        $deleted = Listing::whereKey($listing->id)->where('photo_path', $listing->photo_path)->delete();
        abort_unless($deleted, 409, 'Объявление изменилось. Обновите страницу и попробуйте ещё раз.');

        Storage::disk('local')->delete($listing->photo_path);

        return to_route('catalog')->with('status', 'Объявление снято с продажи.');
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $photo->store('listings', 'local');
        if ($path === false) {
            throw new RuntimeException('Не удалось сохранить фотографию.');
        }

        return $path;
    }
}
