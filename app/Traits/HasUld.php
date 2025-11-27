<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUld
{
    protected static function bootHasUld(): void
    {
        static::creating(function ($model) {
            if (empty($model->uld)) {
                $model->uld = self::generateUld();
            }
        });
    }

    protected static function generateUld(): string
    {
        return Str::uuid()->toString();
    }
}
