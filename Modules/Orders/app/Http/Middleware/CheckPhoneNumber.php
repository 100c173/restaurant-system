<?php

namespace Modules\Orders\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckPhoneNumber
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user() ;
        if(!$user->phone)
        {
            return response()->json([
                'message' => 'Phone number is required'
            ],403);
        }
        return $next($request);
    }
}
