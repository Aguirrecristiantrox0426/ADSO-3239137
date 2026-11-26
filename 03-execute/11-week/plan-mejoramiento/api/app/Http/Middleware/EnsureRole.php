<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !$user->is_active) abort(403, 'La cuenta no está autorizada.');
        if (!in_array($user->role, $roles, true)) abort(403, 'No tiene permiso para realizar esta operación.');
        return $next($request);
    }
}
