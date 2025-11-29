<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasActiveStatus;
use App\Traits\HasUld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasUld, HasActiveStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type_user_id',
        'licence_id',
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the type user associated with this user.
     */
    public function typeUser()
    {
        return $this->belongsTo(TypeUser::class);
    }

    /**
     * Get the licence associated with this user.
     */
    public function licence()
    {
        return $this->belongsTo(Licence::class);
    }

    /**
     * Get all applications owned by this user.
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get all user-application-job relationships for this user.
     */
    public function userApplicationJobs()
    {
        return $this->hasMany(UserApplicationJob::class);
    }

    /**
     * Get all authorized applications through the pivot table.
     */
    public function authorizedApplications()
    {
        return $this->belongsToMany(Application::class, 'user_application_job')
            ->withPivot('job_application_id')
            ->withTimestamps();
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        if (!$this->typeUser) {
            return false;
        }

        return $this->typeUser->slug === 'admin';
    }
}
