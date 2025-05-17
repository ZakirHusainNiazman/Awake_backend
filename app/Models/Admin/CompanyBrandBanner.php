<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CompanyBrandBanner extends Model
{
    protected $table = 'company_brand_banner';
    protected $fillable = [
        'brand_slug',
        'image_path',
    ];
}
