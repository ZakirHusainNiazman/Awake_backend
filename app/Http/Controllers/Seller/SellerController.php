<?php

namespace App\Http\Controllers\Seller;

use App\Models\Seller\Brand;
use Illuminate\Http\Request;
use App\Models\Seller\Seller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Seller\SellerRequest;
use App\Http\Resources\Seller\SellerResource;
use App\Http\Requests\Seller\UpdateSellerRequest;
use App\Http\Resources\Seller\SellerResourceWithUser;

class SellerController extends Controller
{



    // functions for changing account status
    public function approve(Seller $seller)
    {
        $seller->approve();   // ← calls your state-transition helper
        return response()->json(['message'=>'Seller Approved Successfully']);
    }

    public function block(Seller $seller, Request $request)
    {
        $reason = $request->input('reason');
        $seller->block($reason);
        return response()->json(['message'=>'Seller Blocked Successfully']);
    }

//// end of functions for changing account status
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SellerResourceWithUser::collection(
            Seller::with(['user'])->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SellerRequest $request)
    {
            DB::beginTransaction();

        try {

            // 1) create the seller
            $file = $request->file('proof_of_identity');

            // Generate unique filename
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('images/sellers_images'), $filename);

            // Store the relative path
            $imagePath = 'images/sellers_images/' . $filename;


            //uploading brand image
            $brandImage = $request->file('brand_logo');
            // Generate unique filename
            $brandImageName = uniqid() . '.' . $brandImage->getClientOriginalExtension();
            $brandImage->move(public_path('images/brands_images'), $brandImageName);
            // Store the relative path
             $brandImage = 'images/brands_images/' . $brandImageName;

            $sellerData = $request->only([
                'account_type',
                'dob',
                'whatsapp_no',
                'store_name',
                'business_description',
                'identity_type',
                'brand_name',
            ]);

            $user = Auth::user();
            $sellerData['user_id'] = $user->id;
            $sellerData[ 'proof_of_identity'] = $imagePath;
            $sellerData[ 'brand_logo' ] = $brandImageName;

            $seller = Seller::create($sellerData);

            $seller->user->update([
                'user_type'=>'seller',
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'email'=>$request->email,
            ]);

            $user->addresses()->delete();//deletes all the current addresses and add only this new one

            $addressData = $request->only([
                'fullfillment_country_id',
                'fullfillment_state_id',
                'fullfillment_city_id',
                'address_line1',
                'address_line2',
                'postal_code',
                'phone',
            ]);

            $addressData['is_default'] =  true;

            $seller->user->addresses()->create($addressData);

            DB::commit();

            // eager‑load user, addresses, brands
            $seller->load(['user.addresses']);

                return SellerResource::make($seller);

        } catch (\Throwable $e) {
            // Delete the uploaded image if it exists
            if (isset($imagePath) && file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }

            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $seller = Seller::find($id);

        if(!$seller){
            return response()->json([
                "status"=>'fiald',
                "message"=>'Seller does Not Exists',
            ],404);
        }
        $seller->load(['user.addresses']);
        return new SellerResourceWithUser($seller);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSellerRequest $request, string $id)
    {
            DB::beginTransaction();

        try {


            $seller = Seller::find($id);
            if(!$seller){
                return \response()->json([
                    'status'=>"faild",
                    'message'=>"Seller not found",
                ],404);
            }


            $sellerData = $request->only([
                'account_status',
                'dob',
                'whatsapp_no',
                'store_name',
                'business_description',
                'identity_type',
            ]);

            $file = $request->file('proof_of_identity');
            $brandLogo = $request->file('brand_logo');

            if($file){
                $imagePath = $seller->proof_of_identity;
                // Delete the uploaded image if it exists
                if (isset($imagePath) && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                // Generate unique filename
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('images/sellers_images'), $filename);

                // Store the relative path
                $imagePath = 'images/sellers_images/' . $filename;

                $sellerData[ 'proof_of_identity'] = $imagePath;
            }

            if($brandLogo){
                $imagePath = $seller->brand_logo;
                // Delete the uploaded image if it exists
                if (isset($imagePath) && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                // Generate unique filename
                $brandLogoName = uniqid() . '.' . $brandLogo->getClientOriginalExtension();

                $brandLogo->move(public_path('images/brands_images'), $brandLogoName);

                // Store the relative path
                $imagePath = 'images/brands_images/' . $brandLogoName;

                $sellerData[ 'brand_logo'] = $imagePath;
            }


            $seller->update($sellerData);

            $seller->user->update([
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'email'=>$request->email,
            ]);

            // Check if the address is marked as default and handle existing default address
            if ($request->boolean('is_default')) {
                // Find and reset any existing default address
                $seller->user->addresses()->where('is_default', true)->update(['is_default' => false]);
            }

            $addressData = $request->only([
                'fullfillment_country_id',
                'fullfillment_state_id',
                'fullfillment_city_id',
                'address_line1',
                'address_line2',
                'postal_code',
                'phone',
            ]);

            $addressData['is_default'] = true;

             $seller->user->addresses()->where('is_default',1)->update($addressData);

            DB::commit();

            // eager‑load user, addresses, brands
            $seller->load(['user.addresses']);

                return SellerResource::make($seller);

        } catch (\Throwable $e) {
            // Delete the uploaded image if it exists
            if (isset($imagePath) && file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }

            DB::rollBack();
            return response()->json([
                'message' => 'Updating Seller failed failed',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $seller = Seller::find($id);

        if(!$seller){
            return \response()->json([
                'status'=>"faild",
                'message'=>"Seller not found",
            ],404);
         }


         $seller->user->addresses()->delete();


         $brandLogo = $seller->brand_logo;
         if (isset($brandLogo) && file_exists(public_path($brandLogo))) {
             unlink(public_path($brandLogo));
            }
            $seller->delete();

            $seller->user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Seller deleted successfully'
            ], 200);

        }
}
