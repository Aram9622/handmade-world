<?php

return [
    'custom' => [
        'city' => ['required_if' => 'Enter a city if you offer pickup.'],
        'contact_phone' => ['regex' => 'Enter a phone number with a country code, for example +7 900 123-45-67.'],
    ],
    'attributes' => [
        'contact_email' => 'contact email',
        'contact_phone' => 'contact phone',
        'discount_percent' => 'discount',
        'fulfillment_method' => 'fulfillment method',
        'photo' => 'photo',
    ],
];
