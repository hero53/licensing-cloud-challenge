<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    use HasFactory, HasUld, HasSlug;

    protected $fillable = [
        'application_id',
        'wording',
        'slug',
    ];

    /**
     * Get the application that owns the job application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get all user-application-job relationships for this job application.
     */
    public function userApplicationJobs(): HasMany
    {
        return $this->hasMany(UserApplicationJob::class);
    }
}
