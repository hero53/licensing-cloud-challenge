<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use App\Traits\HasSlug;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licence extends Model
{
    use HasFactory, HasUld, HasSlug, HasActiveStatus;

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
