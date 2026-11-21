<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

Artisan::command('admin:reset-password {email}', function () {
    $user = User::where('email', $this->argument('email'))
        ->where('is_super_admin', true)
        ->whereNull('suspended_at')
        ->first();

    if ($user === null) {
        $this->error('An active super administrator with that email was not found.');

        return 1;
    }

    if (! $this->input->isInteractive()) {
        $this->error('Run this command in an interactive terminal.');

        return 1;
    }

    $password = $this->secret('New password', false);
    if (! is_string($password) || mb_strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
        $this->error('Use at least 12 characters and no more than 72 bytes.');

        return 1;
    }

    if ($password !== $this->secret('Confirm new password', false)) {
        $this->error('Passwords do not match.');

        return 1;
    }

    $user->forceFill(['password' => Hash::make($password), 'remember_token' => null])->save();

    if (config('session.driver') === 'database') {
        DB::connection(config('session.connection'))->table(config('session.table'))
            ->where('user_id', $user->id)->delete();
    }

    $this->info('Super administrator password reset.');

    return 0;
})->purpose('Reset the password of an existing super administrator');
