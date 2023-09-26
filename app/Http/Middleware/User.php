<?php

namespace App\Http\Middleware;

use Closure;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->level == 'admin') {
            $response = [
                'status' => '401',
                'pesan' => 'anda admin tidak bisa akses'
            ];
            return response()->json($response);
        }
        return $next($request);
        
    }
}
