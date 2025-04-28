<?php

namespace App\Http\Controllers\Fullfillment;

use Illuminate\Http\Request;
use App\Models\FullfillmentCity;
use App\Models\FullfillmentState;
use App\Models\FullfillmentCountry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fullfillment\CityRequest;
use App\Http\Requests\Fullfillment\StateRequest;
use App\Http\Requests\Fullfillment\AddCountryRequest;
use App\Http\Requests\Fullfillment\UpdateStateRequest;
use App\Http\Requests\Fullfillment\UpdateCountryRequest;
use App\Http\Resources\FullfillmentLocations\CityResource;
use App\Http\Resources\FullfillmentLocations\CityWithStateResource;
use App\Http\Resources\FullfillmentLocations\StateWithCitiesResource;
use App\Http\Resources\FullfillmentLocations\CountryWithStatesResource;

class FullfillmentController extends Controller
{
    // country related routes
    // this function will store a new country
    public function AddCountry(AddCountryRequest $request)
    {
        $file = $request->file('country_flag');

        // Generate unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move file to public/images/country_images
        $file->move(public_path('images/country_images'), $filename);

        // Store the relative path
        $imagePath = 'images/country_images/' . $filename;

        // Save in DB and store the model
        $country = FullfillmentCountry::create([
            "country_name" => $request->country_name,
            "country_flag" => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Country created successfully',
            'data' => CountryWithStatesResource::make($country),
        ], 200);
    }

    // this will return a spacific country
    public function EditCountry($id)
    {
        $country = FullfillmentCountry::where('id', $id)->whereNull('deleted_at')->first();

        if(!$country){
            return response()->json([
            'status' => 'error',
            'message' => 'Country not found.'
        ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => CountryWithStatesResource::make($country),
        ], 200);
    }

    // this will update a spacific country
    public function UpdateCountry(UpdateCountryRequest $request)
    {
        $country = FullfillmentCountry::where('id', $request->id)->whereNull('deleted_at')->first();

        if (!$country) {
            return response()->json([
                'status' => 'error',
                'message' => 'Country not found.'
            ], 404);
        }

        // Prepare update data
        $updateData = [
            "country_name" => $request->country_name,
        ];
        // Check if a new flag was uploaded
        if ($request->hasFile('country_flag')) {
            // Delete old file if exists
            if ($country->country_flag && file_exists(public_path($country->country_flag))) {
                unlink(public_path($country->country_flag));
            }

            $file = $request->file('country_flag');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/country_images'), $filename);
            $imagePath = 'images/country_images/' . $filename;

            // Add flag to update data
            $updateData['country_flag'] = $imagePath;
        }

        // Update the country model
        $country->update($updateData);
        $country->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Country Update successfully',
            'data' => CountryWithStatesResource::make($country),
        ], 200);
    }


    public function DeleteCountry($id)
    {
        $country = FullfillmentCountry::where('id', $id)->whereNull('deleted_at')->first();

        if(!$country){
            return response()->json([
                'status' => 'error',
                'message' => 'Country not found.'
            ], 404);
        }

        if ($country->country_flag && file_exists(public_path($country->country_flag))) {
            unlink(public_path($country->country_flag));
        }

        $country->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Country Deleted successfull",
        ], 200);
    }





         // this function will return all States
    public function AllState()
    {
        $states = FullfillmentState::whereNull('deleted_at')->with('cities')->get();


        return response()->json([
            'status' => 'success',
            'data' => StateWithCitiesResource::collection($states),
        ], 200);
    }

        // this function will store a new State
    public function AddState(StateRequest $request)
    {
        $state = FullfillmentState::create([
            "state_name" => $request->state_name,
            "fullfillment_country_id"=>$request->fullfillment_country_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'New State Added successfully',
            'data' => StateWithCitiesResource::make($state),
        ], 200);
    }

       // this function will delete State by id
    public function DeleteState($id)
    {
        $state = FullfillmentState::where('id', $id)->whereNull('deleted_at')->first();

        if(!$state){
            return response()->json([
            'status' => 'error',
            'message' => 'State not found.'
        ], 404);
        }

        $state->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'State Deleted successfully',
        ], 200);
    }

        // this function will return State by id
    public function EditState($id)
    {
        $state = FullfillmentState::where('id', $id)->whereNull('deleted_at')->first();

        if(!$state){
            return response()->json([
            'status' => 'error',
            'message' => 'State not found.'
        ], 404);
        }


        return response()->json([
            'status' => 'success',
            'data' => StateWithCitiesResource::make($state),
        ], 200);
    }



      // this function will update a State
    public function UpdateState(UpdateStateRequest $request)
    {
        $state = FullfillmentState::where('id', $request->id)->whereNull('deleted_at')->first();


        if(!$state){
            return response()->json([
            'status' => 'error',
            'message' => 'State not found.'
        ], 404);
        }

        $state->update([
            "state_name" => $request->state_name,
            "fullfillment_country_id"=>$request->fullfillment_country_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'State Updated successfully',
            'data' => StateWithCitiesResource::make($state),
        ], 200);
    }


     // this function will store a new city
    public function AllCity()
    {
        $cities = FullfillmentCity::with('state')->whereNull('deleted_at')->get();

        return response()->json([
            'status' => 'success',
            'data'   => CityWithStateResource::collection($cities),
        ], 200);
    }

     // this function will store a new city
    public function AddCity(CityRequest $request)
    {
        $city = FullfillmentCity::create([
            "city_name" => $request->city_name,
            "fullfillment_state_id"=>$request->fullfillment_state_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'New City Added successfully',
            'data' => CityWithStateResource::make($city),
        ], 201);
    }

       // this function will delete State by id
    public function DeleteCity($id)
    {
        $city = FullfillmentCity::where('id', $id)->whereNull('deleted_at')->first();

        if(!$city){
            return response()->json([
            'status' => 'error',
            'message' => 'City not found.'
        ], 404);
        }

        $city->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'City Deleted successfully',
        ], 200);
    }

        // this function will return State by id
    public function EditCity($id)
    {
        $city = FullfillmentCity::where('id', $id)->whereNull('deleted_at')->first();

        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => CityResource::make($city),
        ], 200);
    }



      // this function will update a State
    public function UpdateCity(CityRequest $request)
    {
        $city = FullfillmentCity::where('id', $request->id)->whereNull('deleted_at')->first();


        if(!$city){
            return response()->json([
            'status' => 'error',
            'message' => 'City not found.'
        ], 404);
        }

        $city->update([
            "city_name" => $request->city_name,
            "fullfillment_state_id"=>$request->fullfillment_state_id
        ]);

        $city->refresh();

         return response()->json([
        'status'  => 'success',
        'message' => 'City updated successfully',
    ], 200);
    }


    //this will return all the countries along with their state and cities
    public function GetAllFullfillmentLocations(){

        $countries = FullfillmentCountry::with([
        'states' => function ($query) {
            $query->whereNull('deleted_at');
        },
        'states.cities' => function ($query) {
            $query->whereNull('deleted_at');
        }
        ])->get();

        return CountryWithStatesResource::collection($countries);
    }
}
