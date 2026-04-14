<?php
namespace App\Http\Middleware;

use App\Models\LoginHistory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackLoginHistory
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($request->isMethod('post') && $request->routeIs('login') && Auth::check()) {
            LoginHistory::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
                'email' => Auth::user()->email,
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}