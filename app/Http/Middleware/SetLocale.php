<?php

// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Define o locale com base no Accept-Language do navegador
        $locale = $request->getPreferredLanguage(['pt_BR', 'pt', 'en']) ?? 'pt_BR';

        // Padroniza 'pt' para 'pt_BR' para bater com a sua pasta lang/pt_BR
        if (in_array($locale, ['pt', 'pt_BR'])) {
            $locale = 'pt_BR';
        } else {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}