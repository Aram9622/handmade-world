<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:grant {email}', function () {
    $updated = User::where('email', $this->argument('email'))
        ->whereNull('suspended_at')
        ->update(['is_super_admin' => true]);

    if (! $updated) {
        $this->error('An active account with that email was not found.');

        return 1;
    }

    $this->info('Super administrator access granted.');

    return 0;
})->purpose('Grant super administrator access to an existing active account');
