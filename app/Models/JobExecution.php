<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobExecution extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job_application_id',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the job application that owns the job execution.
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    /**
     * Get the user that owns the job execution.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
