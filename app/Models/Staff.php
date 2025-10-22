<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'department',
        'salary',
        'hire_date',
        'shift_start',
        'shift_end',
        'status',
        'employment_type',
        'notes',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date',
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
    ];

    // Relationships
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'staff_unit')
            ->withPivot(['assigned_date', 'end_date', 'role', 'is_primary'])
            ->withTimestamps();
    }

    // Accessors
    public function getFullShiftTimeAttribute(): string
    {
        if ($this->shift_start && $this->shift_end) {
            return $this->shift_start->format('g:i A').' - '.$this->shift_end->format('g:i A');
        }

        return 'Not Set';
    }

    public function getYearsOfServiceAttribute(): int
    {
        return $this->hire_date->diffInYears(now());
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByJobTitle($query, string $jobTitle)
    {
        return $query->where('job_title', $jobTitle);
    }

    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full_time');
    }

    public function scopePartTime($query)
    {
        return $query->where('employment_type', 'part_time');
    }

    // Methods
    public function assignToUnit(int $unitId, string $role = 'broker', bool $isPrimary = false): void
    {
        $this->units()->attach($unitId, [
            'role' => $role,
            'is_primary' => $isPrimary,
            'assigned_date' => now(),
        ]);
    }

    public function removeFromUnit(int $unitId): void
    {
        $this->units()->updateExistingPivot($unitId, [
            'end_date' => now(),
        ]);
    }

    public function updateRoleInUnit(int $unitId, string $role): void
    {
        $this->units()->updateExistingPivot($unitId, [
            'role' => $role,
        ]);
    }

    public function getPrimaryUnit()
    {
        return $this->units()->wherePivot('is_primary', true)->first();
    }

    public function getCurrentUnits()
    {
        return $this->units()->wherePivotNull('end_date');
    }
}
