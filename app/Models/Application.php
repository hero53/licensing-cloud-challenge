<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use App\Traits\HasSlug;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory, HasUld, HasSlug, HasActiveStatus;

    protected $fillable = [
        'user_id',
        'wording',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all job applications for this application.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get all user-application-job relationships for this application.
     */
    public function userApplicationJobs(): HasMany
    {
        return $this->hasMany(UserApplicationJob::class);
    }

    /**
     * Get all users through the pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_application_job')
            ->withPivot('job_application_id')
            ->withTimestamps();
    }
}
