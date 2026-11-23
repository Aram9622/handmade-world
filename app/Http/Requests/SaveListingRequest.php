<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Services\CatalogCategories;
use App\Services\ListingDescription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveListingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('discount_percent') && $this->input('discount_percent') === null) {
            $this->merge(['discount_percent' => 0]);
        }

        $html = $this->input('description_html');
        if (is_string($html) && mb_strlen($html) <= 12000) {
            $description = app(ListingDescription::class);
            $html = $description->clean($html);
            $this->merge(['description_html' => $html, 'description' => $description->plain($html)]);
        }

        $phone = $this->input('contact_phone');
        if (is_string($phone) && preg_match('/\A\+[0-9\h().-]{1,29}\z/u', $phone)) {
            $this->merge(['contact_phone' => '+'.preg_replace('/[^0-9]/', '', $phone)]);
        }
    }

    public function rules(CatalogCategories $categories): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in($categories->keys())],
            'description' => ['required', 'string', 'max:2000'],
            'description_html' => ['nullable', 'string', 'max:12000'],
            'characteristics' => ['sometimes', 'array', 'max:'.Listing::MAX_CHARACTERISTICS],
            'characteristics.*' => ['array:label,value'],
            'characteristics.*.label' => ['nullable', 'required_with:characteristics.*.value', 'string', 'max:80'],
            'characteristics.*.value' => ['nullable', 'required_with:characteristics.*.label', 'string', 'max:180'],
            'price' => ['nullable', 'required_without_all:price_amd,price_usd', 'integer', 'min:1', 'max:10000000'],
            'price_amd' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'price_usd' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'discount_percent' => ['sometimes', 'required', 'integer', 'min:0', 'max:99'],
            'city' => ['nullable', 'required_if:fulfillment_method,pickup,both', 'string', 'max:100'],
            'fulfillment_method' => ['nullable', Rule::in(array_keys(Listing::FULFILLMENT_METHODS))],
            'contact_email' => ['required', 'email', 'max:254'],
            'contact_phone' => ['nullable', 'string', 'regex:/\A\+[1-9][0-9]{6,14}\z/'],
            'gallery_photos' => [$this->routeIs('listings.store') ? 'prohibited' : 'sometimes', 'array', 'max:'.Listing::MAX_GALLERY_PHOTOS],
            'gallery_photos.*' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_gallery' => [$this->routeIs('listings.store') ? 'prohibited' : 'sometimes', 'array', 'max:'.Listing::MAX_GALLERY_PHOTOS],
            'remove_gallery.*' => ['required', 'string', 'regex:/\A[a-zA-Z0-9]{24}\z/', 'distinct'],
            'photo' => [$this->routeIs('listings.store') ? 'required' : 'sometimes', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return ['price.required_without_all' => __('Укажите цену хотя бы в одной валюте.')];
    }

    public function listingData(): array
    {
        $data = $this->safe()->except(['photo', 'gallery_photos', 'remove_gallery']);
        $data['description_html'] ??= null;

        if (array_key_exists('characteristics', $data)) {
            $data['characteristics'] = array_values(array_filter(
                $data['characteristics'],
                fn (array $row) => isset($row['label'], $row['value']),
            ));
        }

        return $data;
    }
}
