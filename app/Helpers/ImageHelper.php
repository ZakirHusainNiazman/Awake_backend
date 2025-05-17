<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageHelper
{
    public static function saveImageFile(UploadedFile $file, string $baseFolder, ?string $prefix = null): string
    {
        $datePath = now()->format('Y/m/d');
        $hashSegment = substr(md5($prefix ?? Str::uuid()), 0, 2);
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $dir = public_path("images/{$baseFolder}/{$datePath}/{$hashSegment}");

        Log::info("Saving image file:", ['path' => $dir]);

        File::ensureDirectoryExists($dir, 0755, true);
        $file->move($dir, $filename);

        return "images/{$baseFolder}/{$datePath}/{$hashSegment}/{$filename}";
    }
}
