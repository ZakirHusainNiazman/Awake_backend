<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\Admin\CompanyBrandBanner;
use App\Http\Resources\Admin\CompanyBrandBannerResource;

class CompanyBrandBannerController extends Controller
{

    public function show(){
        $banner = CompanyBrandBanner::firstOrFail();

        return CompanyBrandBannerResource::make($banner);
    }

   public function update(Request $request)
{
    $validatedData = $request->validate([
        'brand_slug' => ['required', 'string', 'max:255'],
        'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    DB::beginTransaction();

    try {
        $banner = CompanyBrandBanner::firstOrFail();
        Log::info("reqeust ",["banner => ",$banner]);
        $data = [
            'brand_slug' => $validatedData['brand_slug'],
        ];

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $path = $this->saveImageFile($file, 'brands');
            $data['image_path'] = $path;
        }

        $banner->update($data);
        DB::commit();

        return response()->json([
            "message" => "Banner updated successfully",
            "data"=>$banner,
        ]);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            "message" => "Failed to update banner",
            "error" => $e->getMessage()
        ], 500);
    }
}

    private function saveImageFile(UploadedFile $file, string $baseFolder, ?string $prefix = null): string
    {
        $datePath = now()->format('Y/m/d');
        $hashSegment = substr(md5($prefix ?? Str::uuid()), 0, 2);
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $dir = public_path("images/{$baseFolder}/{$datePath}/{$hashSegment}");

        File::ensureDirectoryExists($dir, 0755, true);
        $file->move($dir, $filename);

        return "images/{$baseFolder}/{$datePath}/{$hashSegment}/{$filename}";
    }
}
