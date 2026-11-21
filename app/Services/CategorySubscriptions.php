<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CategorySubscriptions
{
    /** @return list<string> */
    public function categories(User $user): array
    {
        return $this->forUser($user)->orderBy('category')->pluck('category')->all();
    }

    public function subscribe(User $user, string $category): void
    {
        $this->forUser($user)->insertOrIgnore(['user_id' => $user->id, 'category' => $category]);
    }

    public function unsubscribe(User $user, string $category): void
    {
        $this->forUser($user)->where('category', $category)->delete();
    }

    private function forUser(User $user): Builder
    {
        return DB::table('category_subscriptions')->where('user_id', $user->id);
    }
}
