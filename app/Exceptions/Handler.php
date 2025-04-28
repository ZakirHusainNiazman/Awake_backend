<?php

namespace App\Exceptions;

use Exception;

class Handler extends Exception
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status_code' => 401,
                'success' => false,
                'message' => 'Please login first.'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
