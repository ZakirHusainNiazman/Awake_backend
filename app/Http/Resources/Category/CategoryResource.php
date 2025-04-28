<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Category\CategoryResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "name"=> $this->name,
            'slug' => $this->slug,
            'commission_rate' => $this->commission_rate,
            'category_icon' => url($this->category_icon),
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'parent'=> new CategoryResource($this->whenLoaded("parent")),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
