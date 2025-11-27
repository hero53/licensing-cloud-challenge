<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory, HasUld, HasSlug;

    protected $fillable = [
        'application_id',
        'wording',
        'slug',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobExecutions()
    {
        return $this->hasMany(JobExecution::class);
    }
}
