<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\UserRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\SignupRequest;
use App\Http\Resources\User\UserResource;

class UserController extends Controller
{
    // it will log the user in using web session and cookies
    public function signup(SignupRequest $request){
        $user = User::create([
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        $user->refresh();

        $token = $user->createToken('AwakeBazar')->plainTextToken;

        return UserResource::make($user)->additional(['status_code'=>200, 'token'=>$token]);
    }

    // it will log the user in using web session and cookies
    public function login(LoginRequest $request)
    {
        // Attempt to authenticate using the provided credentials
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication successful, generate token for the user
            $user = Auth::user();

            $token = $user->createToken('AwakeBazar')->plainTextToken;

            return UserResource::make($user)->additional(['status_code'=>200, 'token'=>$token]);
        } else {
            // Authentication failed, return a structured error response
            return response()->json([
                'error' => [
                    'status_code'    => 401, // Unauthorized
                    'type'           => 'invalid_credentials',  // Type of the error
                    'global_message' => 'Invalid email or password.',  // General error message
                    'errors'         => []  // Can be extended to add field-specific errors
                ]
            ], 401);
        }
    }

    

    // it will return a single user by user id
    public function getUserById($id){
        // dd($request);
        $user = User::find($id); // Should return the authenticated user

        if(!$user){
            return response()->json([
                "status"=>"not found",
                "message"=>"User does Not exist.",
            ],404);
        }

        return UserResource::make($user)->additional(['status_code'=>200]);
    }


    // // it will log the user out and will destroy all of the user's authenticated session data including session cookies.
    // public function webLogout(){
    //     Auth::logout();
    //     return response()->json(['message' => 'Logout Successful'], 200);
    // }

    // // it will return a list of all users
    // public function getAllUsers(){
    //     return UserResource::collection(User::all());
    // }



    // public function createUser(UserRequest $request){
    //     $path = null; // Initialize path to handle cases without image

    //     if($request->hasFile('image')){
    //         $filename = Carbon::now()->format('Ymd_His')."_".uniqid().".".$request->file('image')->getClientOriginalExtension();

    //         // Store the file in the 'public' disk under 'user_images' directory
    //         $path = $request->file('image')->storeAs(
    //             'user_images',
    //             $filename,
    //             'public'
    //         );
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'role_id' => $request->role_id,
    //         'image' => $path,
    //         'created_by' => Auth::id(),
    //     ]);

    //     return response()->json(['message' => 'User created successfully', 'user' => new UserResource($user)], 201);
    // }

    // // it will update a specific user's information
    // public function updateUser(UserRequest $request, $id){
    //     $user = User::find($id);
    //     if(!$user){
    //         return response()->json(['message' => 'User not found'], 404);
    //     }

    //     $path = $user->image; // Initialize path to the current image path

    //     if($request->hasFile('image')){
    //          // Delete old image if exists
    //         if($path && Storage::disk('public')->exists($path)){
    //             Storage::disk('public')->delete($path);; // Delete the old image file
    //         }
    //         // Generate a new unique filename to avoid overwriting the old file
    //         $filename = Carbon::now()->format('Ymd_His')."_".uniqid().".".$request->file('image')->getClientOriginalExtension();

    //         // Store the file in the 'public' disk under 'user_images' directory
    //         $path = $request->file('image')->storeAs(
    //             'user_images',
    //             $filename,
    //             'public'
    //         );
    //     }

    //     $user->update([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
    //         'role_id' => $request->role_id,
    //         'image' => $path,
    //     ]);
    //     return new UserResource($user);
    // }

    // // it will simplay delete specific user
    // public function deleteUser($id){
    //     $user = User::find($id);
    //     if(!$user){
    //         return response()->json(['message' => 'User not found'], 404);
    //     }


    //     if($user->image && Storage::disk('public')->exists($user->image)){
    //         Storage::disk('public')->delete($user->image); // Delete user's image file
    //     }

    //     $user->delete();
    //     return response()->json(['message' => 'User deleted successfully'], 200);
    // }

}
