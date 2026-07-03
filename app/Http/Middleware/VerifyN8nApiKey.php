<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyN8nApiKey
{
    /**
     * Autenticación simple por API key para llamadas server-to-server desde n8n.
     * No usa Sanctum a propósito: estas llamadas no tienen un usuario asociado y
     * el trait BelongsToEntity (que depende de Auth::user()) no aplicaría aquí.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');
        $expected = config('services.n8n.api_key');

        if (!$expected || !$key || !hash_equals($expected, $key)) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        return $next($request);
    }
}
