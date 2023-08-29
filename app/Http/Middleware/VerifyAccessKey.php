<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAccessKey
{
    public $except = [
        'api/token'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->headers->get('X_API_KEY');
        
        if(in_array($request->path(), $this->except)) {
            return $next($request);
        }

        if(isset($key) == env('API_KEY')) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 'error',
                'error' => 'Unauthorized'
            ], 401);
        }
    }
}
