<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        // Priority: query param > session > browser Accept-Language > default
        if ($request->has('lang') && in_array($request->query('lang'), $this->supported)) {
            $locale = $request->query('lang');
            session(['locale' => $locale]);
        } elseif (session()->has('locale') && in_array(session('locale'), $this->supported)) {
            $locale = session('locale');
        } else {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
