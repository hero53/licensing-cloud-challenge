<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use App\Traits\HasSlug;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Licence extends Model
{
    use HasFactory, HasUld, HasSlug, HasActiveStatus;

    protected $fillable = [
        'wording',
        'slug',
        'description',
        'max_apps',
        'max_executions_per_24h',
        'valid_from',
        'valid_to',
        'status',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
        'max_apps' => 'integer',
        'max_executions_per_24h' => 'integer',
    ];

    /**
     * Get all users associated with this licence.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
