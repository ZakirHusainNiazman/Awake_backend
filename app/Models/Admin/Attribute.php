<?php

namespace App\Models\Admin;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attribute extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'type',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
    ];



    /**
     * Get the category that owns the Attribute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
