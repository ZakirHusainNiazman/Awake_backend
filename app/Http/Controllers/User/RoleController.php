<?php

namespace App\Http\Controllers\User;

use App\Models\User\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\RoleResource;

class RoleController extends Controller
{
    public function getAllRoles(){
        $roles = Role::all();

        return RoleResource::collection($roles);
    }
}
