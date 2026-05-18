<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'full_name',
        'position',
        'department_id',
        'phone',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'signature_pin',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'signature_pin' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class)->latest('created_at');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Начальник отдела: роль и запись «главный» у своего отдела.
     */
    public function isDepartmentHead(): bool
    {
        if ($this->role !== 'department_head') {
            return false;
        }

        return $this->headedDepartment() !== null;
    }

    public function headedDepartment(): ?Department
    {
        return Department::query()->where('head_user_id', $this->id)->first();
    }

    public function hasSignaturePin(): bool
    {
        return $this->signature_pin !== null && $this->signature_pin !== '';
    }

    public function verifySignaturePin(string $plain): bool
    {
        if (! $this->hasSignaturePin()) {
            return false;
        }

        return Hash::check($plain, $this->signature_pin);
    }

    public function displayName(): string
    {
        return $this->full_name ?: $this->name;
    }
}
