<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LANGUAGES = ['ru' => 'Русский', 'hy' => 'Հայերեն', 'en' => 'English'];

    public function handle(Request $request, Closure $next): Response
    {
        $selected = $request->query('lang');
        if ($request->isMethod('GET') && is_string($selected) && isset(self::LANGUAGES[$selected])) {
            $request->session()->put('locale', $selected);

            return redirect()->to($request->fullUrlWithoutQuery('lang'));
        }

        $locale = $request->session()->get('locale', 'ru');
        app()->setLocale(isset(self::LANGUAGES[$locale]) ? $locale : 'ru');

        return $next($request);
    }
}
