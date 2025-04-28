<?php

namespace App\Http\Controllers\User;

use App\Models\User\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\AddressResource;
use App\Http\Requests\User\StoreAddressRequest;

class AddressController extends Controller
{
     /**
     * Display a listing of the user's addresses.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->with(['country', 'state', 'city'])->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAddressRequest $request)
    {
        // Retrieve the validated input data
        $validated = $request->validated();

        // Add the authenticated user's ID to the data
        $validated['user_id'] = $request->user()->id;

        // Check if the address is marked as default and handle existing default address
        if ($request->is_default) {
            // Find and reset any existing default address
            $request->user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        // Create the address record
        $address = Address::create($validated);

        // Return a JSON response with the created address
        return response()->json([
            'message' => 'Address created successfully.',
            'data' => $address,
        ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $address = Auth::user()
        ->addresses()
        ->with(['country', 'state', 'city'])
        ->find($id);

        if(!$address){
            return \response()->json([
                "status"=>"faild",
                "message"=>"The address does not exist.",
            ],404);
        }


       return AddressResource::make($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAddressRequest $request, string $id)
    {
        $address = Auth::user()
        ->addresses()
        ->with(['country', 'state', 'city'])
        ->find($id);

        if(!$address){
            return \response()->json([
                "status"=>"faild",
                "message"=>"The address does not exist.",
            ],404);
        }

        $validated = $request->validated();

        // Check if the address is marked as default
        if ($request?->is_default) {
            // Find the existing default address and update it
            $defaultAddress = $request->user()->addresses()->where('is_default', true)->first();

            // If a default address exists, set it to false
            if ($defaultAddress) {
                $defaultAddress->is_default = false;
                $defaultAddress->save();
            }
        }

        $address->update($validated);

         return response()->json([
            'message' => 'Address updated successfully.',
            'data' => AddressResource::make($address),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = Auth::user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'status' => 'failed',
                'message' => 'The address does not exist.',
            ], 404);
        }

        // If the address being deleted is the default, we need to ensure there's another default address
        if ($address->is_default) {
            $nextDefaultAddress = Auth::user()->addresses()->where('is_default', false)->first();

            if ($nextDefaultAddress) {
                // Set a new default address if one exists
                $nextDefaultAddress->update(['is_default' => true]);
            }
        }

        // Delete the address
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully.',
        ], 200);
    }
}
