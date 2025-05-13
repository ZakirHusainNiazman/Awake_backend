<?php

namespace App\Http\Controllers\Seller;

use App\Models\Seller\Brand;
use Illuminate\Http\Request;
use App\Models\Seller\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::info("seller info ",$request->all());
            DB::beginTransaction();

        try {

            // 1) create the seller
            $file = $request->file('proof_of_identity');

            // Generate unique filename
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('images/sellers_images'), $filename);

            // Store the relative path
            $imagePath = 'images/sellers_images/' . $filename;

            $sellerData = $request->only([
                'dob',
                'whatsapp_no',
                'store_name',
                'business_description',
                'identity_type',
            ]);

            $user = Auth::user();
            $sellerData['user_id'] = $user->id;
            $sellerData[ 'proof_of_identity'] = $imagePath;
            //create seller
            $seller = Seller::create($sellerData);

            //uploading brand image
            $brandImage = $request->file('brand_logo');
            // Generate unique filename
            $brandImageName = uniqid() . '.' . $brandImage->getClientOriginalExtension();
            $brandImage->move(public_path('images/brands_images'), $brandImageName);
            // Store the relative path
            $brandImagePath = 'images/brands_images/' . $brandImageName;

            $seller->brand()->create([
                'name'=>$request->brand_name,
                'logo'=>$brandImagePath,
            ]);


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
            $seller->load(['user.addresses', 'brand']);

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
    public function update(UpdateSellerRequest $request, Seller $seller)
    {
        DB::beginTransaction();

        try {
            // 1) Basic seller fields
            $sellerData = $request->only([
                'account_status',
                'dob',
                'whatsapp_no',
                'store_name',
                'business_description',
                'identity_type',
            ]);

            // 2) Proof-of-identity upload
            if ($file = $request->file('proof_of_identity')) {
                // remove old
                if (file_exists(public_path($seller->proof_of_identity))) {
                    unlink(public_path($seller->proof_of_identity));
                }
                $proofFilename = uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/sellers_images'), $proofFilename);
                $sellerData['proof_of_identity'] = 'images/sellers_images/'.$proofFilename;
            }

            // 3) Update seller row
            $seller->update($sellerData);

            // 4) Brand update
            $brandData = ['name' => $request->brand_name];
            if ($brandFile = $request->file('brand_logo')) {
                // remove old
                if ($seller->brand && file_exists(public_path($seller->brand->logo))) {
                    unlink(public_path($seller->brand->logo));
                }
                $brandFilename = uniqid().'.'.$brandFile->getClientOriginalExtension();
                $brandFile->move(public_path('images/brands_images'), $brandFilename);
                $brandData['logo'] = 'images/brands_images/'.$brandFilename;
            }
            $seller->brand()->update($brandData);

            // 5) User info
            $seller->user->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
            ]);

            // 6) Addresses: either update the default or create new
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

            // reset any existing default
            $seller->user->addresses()
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // then upsert
            // if you have an ID in the request you could update, otherwise always create
            if ($request->filled('address_id')) {
                $seller->user->addresses()
                    ->updateOrCreate(
                        ['id' => $request->address_id],
                        $addressData
                    );
            } else {
                $seller->user->addresses()->create($addressData);
            }

            DB::commit();

            // 7) Eager-load for the resource
            $seller->load(['user.addresses', 'brand']);

            return SellerResource::make($seller);

        } catch (\Throwable $e) {
            DB::rollBack();

            // Clean up any newly uploaded files
            if (!empty($sellerData['proof_of_identity']) && file_exists(public_path($sellerData['proof_of_identity']))) {
                unlink(public_path($sellerData['proof_of_identity']));
            }
            if (!empty($brandData['logo']) && file_exists(public_path($brandData['logo']))) {
                unlink(public_path($brandData['logo']));
            }

            return response()->json([
                'message' => 'Updating seller failed',
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
