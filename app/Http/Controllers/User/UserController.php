<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\UserRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\SignupRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Validator;

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

        $user->cart()->create([
            "user_id" => $user->id,
        ]);

        $user->wishlist()->create([
            "user_id" => $user->id,
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

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>'Logged out successfully'], 200);
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


    public function updateUser(Request $request){
        $user= Auth::user();

        $userData = [
            "first_name"=>$request->first_name,
            "last_name"=>$request->last_name,
            "email"=>$request->email,
        ];

        if($request->hasFile("profile_image")){
            $image = $request->file("profile_image");

             $uuid = Str::uuid()->toString();
             $extension = $image->getClientOriginalExtension();
             $fileName = $uuid . '.' . $extension;

             // Define the path (relative to public or storage folder)
             $destinationPath = public_path('images/user/profile_images');

             // Move the file
             $image->move($destinationPath, $fileName);

             $userData['profile_image'] = 'images/user/profile_images/' . $fileName;
        }

        $user->update($userData);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }


    public function updatePassword(Request $request)
{
    Log::info("requst all ",$request->all());
    $validator = Validator::make($request->all(), [
        'old_password' => ['required'],
        'new_password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);


    // If validation fails, return errors
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
        ], 422);
    }

    // Check if old password matches
    if (!Hash::check($request->old_password, auth()->user()->password)) {
        return response()->json([
            'errors' => [
                'old_password' => ['The old password does not match our records.'],
            ],
        ], 422);
    }

    // Update the password
    auth()->user()->update([
        'password' => bcrypt($request->new_password),
    ]);

    return response()->json([
        'message' => 'Password updated successfully',
    ]);
}
}
