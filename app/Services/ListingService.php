<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
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

    public function update(Listing $listing, array $data, ?UploadedFile $photo): bool
    {
        if ($photo === null) {
            return $this->catalog->withExistingCategory($data['category'], fn () => $listing->update($data));
        }

        $oldPath = $listing->photo_path;
        $newPath = $this->storePhoto($photo);

        try {
            $updated = $this->catalog->withExistingCategory(
                $data['category'],
                fn () => Listing::whereKey($listing->id)->where('photo_path', $oldPath)
                    ->update([...$data, 'photo_path' => $newPath]),
            );
        } catch (Throwable $exception) {
            $this->photos->delete($newPath);
            throw $exception;
        }

        if (! $updated) {
            $this->photos->delete($newPath);

            return false;
        }

        $this->photos->delete($oldPath);

        return true;
    }

    public function delete(Listing $listing): bool
    {
        $deleted = Listing::whereKey($listing->id)->where('photo_path', $listing->photo_path)->delete();

        if (! $deleted) {
            return false;
        }

        $this->photos->delete($listing->photo_path);

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
