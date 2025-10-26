<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the notification routing information for mail.
     *
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\WelcomeEmailNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'avatar',
        'date_of_birth',
        'gender',
        'nationality',
        'state',
        'city',
        'address',
        'last_login_at',
        'preferences',
        'profile_completion',
        'profile_completed',
        'franchise_id',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'preferences' => 'array',
            'profile_completion' => 'array',
            'profile_completed' => 'boolean',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the franchise owned by this user (for franchisors)
     */
    public function franchise(): HasOne
    {
        return $this->hasOne(Franchise::class, 'franchisor_id');
    }

    /**
     * Get the franchise this user belongs to (for brokers and franchisees)
     */
    public function belongsToFranchise()
    {
        return $this->belongsTo(Franchise::class, 'franchise_id');
    }

    /**
     * Get the units managed by this user (for franchisees)
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'franchisee_id');
    }

    /**
     * Get the leads managed by this user
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Get the tasks assigned to this user
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the technical requests created by this user
     */
    public function technicalRequests(): HasMany
    {
        return $this->hasMany(TechnicalRequest::class, 'user_id');
    }

    /**
     * Scope to filter users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if account is currently locked
     */
    public function isLocked(): bool
    {
        if ($this->locked_until === null) {
            return false;
        }

        if ($this->locked_until->isPast()) {
            // Lock period has expired, reset the lock
            $this->update([
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login_at' => now()]);

        // Lock account after 5 failed attempts for 15 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(15)]);
        }
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login_at' => null,
        ]);
    }

    /**
     * Get remaining lock time in minutes
     */
    public function remainingLockTime(): ?int
    {
        if (!$this->isLocked()) {
            return null;
        }

        return (int) now()->diffInMinutes($this->locked_until, false);
    }
}
