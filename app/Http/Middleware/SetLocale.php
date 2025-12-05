<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $user_locale = $request->header("lang");
        $app_locale = App::getLocale();
        if($app_locale !==  $user_locale) {
            App::setLocale($user_locale);
        }
        return $next($request);
    }
}