<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueVariantSku implements Rule
{
    protected static $seen = [];

    protected ?int $ignoreVariantId;

    public function __construct(?int $ignoreVariantId = null)
    {
        $this->ignoreVariantId = $ignoreVariantId;
    }

    public function passes($attribute, $value): bool
    {
        // In-request duplication check
        if (in_array($value, self::$seen)) {
            return false;
        }

        self::$seen[] = $value;

        // DB uniqueness check, ignoring current variant ID if provided
        $query = DB::table('product_variants')->where('sku', $value);

        if ($this->ignoreVariantId) {
            $query->where('id', '!=', $this->ignoreVariantId);
        }

        return !$query->exists();
    }

    public function message(): string
    {
        return 'The variant SKU ":input" has already been taken.';
    }
}
