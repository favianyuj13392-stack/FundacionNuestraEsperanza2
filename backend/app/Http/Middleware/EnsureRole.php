<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) abort(401, 'Unauthenticated');
        $has = $user->roles()->whereIn('name', $roles)->exists();
        if (!$has) abort(403, 'Unauthorized');
        return $next($request);
    }
}
