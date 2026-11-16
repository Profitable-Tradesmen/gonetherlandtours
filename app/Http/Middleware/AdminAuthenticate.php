<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return redirect()->route('admin.login');
        }
        // Editors (restricted users) only reach content areas; everything else is for administrators.
        abort_unless($user->canAccessRoute($request->route()?->getName()), 403, 'You do not have access to this section.');
        Auth::shouldUse('admin');

        return $next($request);
    }
}
