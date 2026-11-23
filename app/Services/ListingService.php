<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ListingService
{
    public function __construct(
        private readonly FilesystemAdapter $photos,
        private readonly CatalogCategories $catalog,
    ) {}

    public function create(int $ownerId, array $data, UploadedFile $photo): Listing
    {
        $path = $this->storePhoto($photo);

        try {
            return $this->catalog->withExistingCategory(
                $data['category'],
                fn () => Listing::create([...$data, 'user_id' => $ownerId, 'photo_path' => $path]),
            );
        } catch (Throwable $exception) {
            $this->photos->delete($path);
            throw $exception;
        }
    }

    public function update(Listing $listing, array $data, ?UploadedFile $photo, array $galleryPhotos, array $remove): bool
    {
        $cover = null;
        $added = [];
        try {
            $cover = $photo ? $this->storePhoto($photo) : null;
            foreach ($galleryPhotos as $image) {
                $added[Str::random(24)] = $this->storePhoto($image);
            }

            $removed = $this->catalog->withExistingCategory($data['category'], function () use ($listing, $data, $cover, $added, $remove): ?array {
                $current = Listing::whereKey($listing->id)->lockForUpdate()->first();
                if (!$current || ($cover !== null && $current->photo_path !== $listing->photo_path)) {
                    return null;
                }

                $gallery = $current->gallery ?? [];
                $discard = array_intersect_key($gallery, array_flip($remove));
                $gallery = array_diff_key($gallery, $discard) + $added;
                if (count($gallery) > Listing::MAX_GALLERY_PHOTOS) {
                    throw ValidationException::withMessages([
                        'gallery_photos' => __('В галерее может быть до :count дополнительных фото.', ['count' => Listing::MAX_GALLERY_PHOTOS]),
                    ]);
                }

                $current->fill($data);
                $current->gallery = $gallery;
                if ($cover !== null) {
                    $discard[] = $current->photo_path;
                    $current->photo_path = $cover;
                }
                $current->save();

                return array_values($discard);
            });
        } catch (Throwable $exception) {
            $this->photos->delete(array_filter([$cover, ...array_values($added)]));
            throw $exception;
        }

        if ($removed === null) {
            $this->photos->delete(array_filter([$cover, ...array_values($added)]));

            return false;
        }

        $this->photos->delete($removed);

        return true;
    }

    public function delete(Listing $listing): bool
    {
        $paths = DB::transaction(function () use ($listing): ?array {
            $current = Listing::whereKey($listing->id)->lockForUpdate()->first();
            if (!$current || $current->photo_path !== $listing->photo_path) {
                return null;
            }

            $paths = [$current->photo_path, ...array_values($current->gallery ?? [])];
            $current->delete();

            return $paths;
        });

        if ($paths === null) {
            return false;
        }

        $this->photos->delete($paths);

        return true;
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $this->photos->putFile('listings', $photo);
        if ($path === false) {
            throw new RuntimeException(__('Не удалось сохранить фотографию.'));
        }

        return $path;
    }
}
