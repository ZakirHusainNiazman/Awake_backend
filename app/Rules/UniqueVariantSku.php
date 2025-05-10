<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueVariantSku implements Rule
{
    protected static $seen = [];

    public function passes($attribute, $value): bool
    {
        // In-request duplication
        if (in_array($value, self::$seen)) {
            return false;
        }

        self::$seen[] = $value;

        // DB uniqueness
        return !DB::table('product_variants')->where('sku', $value)->exists();
    }

    public function message(): string
    {
        return 'The variant SKU ":input" has already been taken.';
    }
}
