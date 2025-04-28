<?php

namespace App\Http\Middleware\Seller;

use Closure;
use Illuminate\Http\Request;
use App\Models\Seller\Seller;
use Symfony\Component\HttpFoundation\Response;

class CheckSellerStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $seller = $user->seller;  

        if ($seller->account_status === Seller::STATUS_APPROVED) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Your account is ' . $seller->account_status,
            'status'  => $seller->account_status
        ], 403);
    }
}
