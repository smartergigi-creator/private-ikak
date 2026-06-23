<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanShare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
{
    if (!session()->has('logged_in') && !auth()->check()) {
        abort(401);
    }

    $user = auth()->user();
    $role = strtolower((string) session('user_role', 'guest'));
    $sessionRoles = collect(session('ikak_roles', []))
        ->map(fn ($role) => strtolower((string) $role))
        ->all();

    $hasRoleShareAccess = in_array($role, ['admin', 'operator', 'branch chief'], true)
        || in_array('operator', $sessionRoles, true)
        || in_array('branch chief', $sessionRoles, true);

    $hasShareAccess = $hasRoleShareAccess
        || ($user && ($user->hasUnlimitedPdfAccess() || (bool) $user->can_share));

    if (!$hasShareAccess) {
        abort(403, 'Share permission denied');
    }

    return $next($request);
}

}
