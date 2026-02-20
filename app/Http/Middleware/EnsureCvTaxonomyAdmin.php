<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCvTaxonomyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = mb_strtolower(trim((string) $request->user()?->email));
        if ($email !== 'jrivera@unamis.com.mx') {
            abort(403);
        }

        return $next($request);
    }
}
