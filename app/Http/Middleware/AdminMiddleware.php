<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('logged_in')) {
            return redirect('/login');
        }

        $roles = session('ikak_roles', []);

        if (
            !in_array('Operator', $roles) &&
            !in_array('Branch Chief', $roles)
        ) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}