<?php

namespace App\Models\Category;

use Spatie\Sluggable\HasSlug;
use App\Models\Category\Category;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasSlug,SoftDeletes;

    // Use the $fillable property to define which fields can be mass-assigned
    protected $fillable = [
        'name',
        'slug',
        'commission_rate',
        'category_icon',
        'parent_id',
        'is_active'
    ];

    protected $casts =[
        'is_active'=> 'boolean',
    ];


    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name') // you can just pass 'name' here instead of a closure
            ->saveSlugsTo('slug');
    }

    /**
     * Get the parent that owns the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, "parent_id");
    }


    /**
     * Get all of the children for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
