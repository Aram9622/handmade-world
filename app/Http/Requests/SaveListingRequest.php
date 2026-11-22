<?php

namespace App\Http\Requests;

use App\Models\Listing;
use App\Services\CatalogCategories;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveListingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('discount_percent') && $this->input('discount_percent') === null) {
            $this->merge(['discount_percent' => 0]);
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
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'discount_percent' => ['sometimes', 'required', 'integer', 'min:0', 'max:99'],
            'city' => ['nullable', 'required_if:fulfillment_method,pickup,both', 'string', 'max:100'],
            'fulfillment_method' => ['nullable', Rule::in(array_keys(Listing::FULFILLMENT_METHODS))],
            'contact_email' => ['required', 'email', 'max:254'],
            'contact_phone' => ['nullable', 'string', 'regex:/\A\+[1-9][0-9]{6,14}\z/'],
            'photo' => [$this->routeIs('listings.store') ? 'required' : 'sometimes', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }
}
