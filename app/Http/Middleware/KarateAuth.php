<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KarateAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('logged_in')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}