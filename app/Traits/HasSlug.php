<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->wording)) {
                $model->slug = static::generateSlug($model->wording);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('wording')) {
                $model->slug = static::generateSlug($model->wording);
            }
        });
    }

    protected static function generateSlug(string $wording): string
    {
        $slug = Str::slug($wording, "_");
        return $slug;
    }
}
