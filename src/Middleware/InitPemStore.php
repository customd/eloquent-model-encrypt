<?php

namespace CustomD\EloquentModelEncrypt\Middleware;

use Closure;
use Illuminate\Http\Request;
use CustomD\EloquentModelEncrypt\Facades\PemStore;

class InitPemStore
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            PemStore::loadFromRequest($request);
        }

        return $next($request);
    }
}
