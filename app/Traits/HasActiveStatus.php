<?php

namespace App\Traits;

trait HasActiveStatus
{
    protected static function bootHasActiveStatus(): void
    {
        static::creating(function ($model) {
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }
        });
    }

    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
