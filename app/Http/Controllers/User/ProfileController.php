<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserResourceWithSeller;

class ProfileController extends Controller
{
    // it will return a single user
    public function show(Request $request){

        $user = $request->user()->load([
            // 'roles',
            // 'permissions',
            'seller',
            'seller.user.addresses'   // eager load user's addresses for seller resource
        ]);
        return UserResourceWithSeller::make($user)->additional(['status_code'=>200]);

    }
}
