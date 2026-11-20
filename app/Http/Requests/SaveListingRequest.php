<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveListingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(array_keys(Listing::CATEGORIES))],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'contact_email' => ['required', 'email', 'max:254'],
            'photo' => [$this->routeIs('listings.store') ? 'required' : 'sometimes', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }
}
