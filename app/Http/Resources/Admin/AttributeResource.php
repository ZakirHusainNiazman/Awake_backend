<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'category'    => [
                                'id'   => $this->category?->id,
                                'name' => $this->category?->name,
                             ],
            'options'     => $this->options
                     ? json_decode($this->options, true)
                     : [],
            'is_required' => $this->is_required,
        ];
    }
}
