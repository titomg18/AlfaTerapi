<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pemakaian di route: ->middleware('role:admin') atau 'role:admin,pegawai'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole(...$roles)) {
            return $next($request);
        }

        // Role tidak cocok: arahkan ke dashboard miliknya sendiri.
        if ($home = $user?->homeRoute()) {
            return redirect()->route($home);
        }

        abort(403, 'Akun Anda belum memiliki role.');
    }
}
