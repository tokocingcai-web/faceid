<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'role',
    'password',
    'nis',
    'nip',
    'gender',
    'phone',
    'address',
    'photo',
    'is_active',
    'class_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'face_descriptor' => 'array',
            'face_registered_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'student_id');
    }

    public function submittedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'requested_by');
    }

    public function reviewedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'reviewed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRole(Builder $query, string ...$roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

    public function hasRegisteredFace(): bool
    {
        return is_array($this->face_descriptor) && count($this->face_descriptor) > 0;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'dev' => 'Developer',
            'admin' => 'Admin',
            'guru' => 'Guru',
            'student' => 'Siswa',
            'wali' => 'Wali Murid',
            default => $this->role,
        };
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode(''));
    }
}
