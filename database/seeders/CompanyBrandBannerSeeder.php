<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompanyBrandBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('company_brand_banner')->insert([
            'brand_slug' => 'awake-bazar',
            'image_path' => 'banners/example.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
