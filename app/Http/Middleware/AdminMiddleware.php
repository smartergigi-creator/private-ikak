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

    $role = session('user_role');
// Only Operator can access dashboard
        if ($role !== 'Operator') {
            abort(403, 'Access Denied');
        }
    // if (!in_array($role, [
    //     'Operator',
    //     'Branch Chief',
    // ])) {
    //     abort(403, 'Access Denied');
    // }

    return $next($request);
}
}