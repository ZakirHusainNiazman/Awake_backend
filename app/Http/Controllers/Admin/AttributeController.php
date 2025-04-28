<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Attribute;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AttributeResource;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\UpdateAttributeRequest;

class AttributeController extends Controller
{
     // List attributes for a category
    public function allAttributes()
    {
        $attributes = Attribute::with('category')->get();
        return AttributeResource::collection($attributes);
    }

    public function index($categoryId)
    {
        $attributes = Attribute::where('category_id', $categoryId)->get(['id','name','type','options','is_required','is_global']);
        return AttributeResource::collection($attributes);
    }

    // Store new attribute
    public function store(StoreAttributeRequest $request)
    {
        $attribute = Attribute::create($request->validated());
        return response()->json([
            "status"=>'success',
            "message"=>"Attribute Created Successfully",
        ],201);
    }


    // Show single attribute
    public function show(Attribute $attribute)
    {
        return response()->json([
            "status"=>'success',
            "data"=>AttributeResource::make($attribute),
        ]);
    }


    // Update attribute
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());

        return response()->json([
            "status"=>'success',
            "message"=>"Attribute Updated Successfully",
        ]);
    }


        // Delete attribute
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json([
            "status"=>'success',
            "data"=>"Attribute Deleted Successfully",
        ]);;
    }
}
