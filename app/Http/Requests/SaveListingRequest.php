<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveListingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('discount_percent') && $this->input('discount_percent') === null) {
            $this->merge(['discount_percent' => 0]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(array_keys(Listing::CATEGORIES))],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'discount_percent' => ['sometimes', 'required', 'integer', 'min:0', 'max:99'],
            'city' => ['nullable', 'required_if:fulfillment_method,pickup,both', 'string', 'max:100'],
            'fulfillment_method' => ['nullable', Rule::in(array_keys(Listing::FULFILLMENT_METHODS))],
            'contact_email' => ['required', 'email', 'max:254'],
            'photo' => [$this->routeIs('listings.store') ? 'required' : 'sometimes', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }
}
