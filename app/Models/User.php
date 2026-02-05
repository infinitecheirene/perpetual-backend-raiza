<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'fraternity_number',
        'membership_id',
        'status',
        'role',
        'rejection_reason',
    ];

    protected $visible = [
        'id',
        'membership_id',
        'name',
        'email',
        'phone_number',
        'address',
        'fraternity_number',
        'status',
        'role',
        'rejection_reason',
        'created_at',
        'updated_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Generate membership ID
     */
    public function generateMembershipId(): string
    {
        $year = now()->year;
        $sequence = str_pad($this->id, 5, '0', STR_PAD_LEFT);

        return "MEM-{$year}-{$sequence}";
    }

    /**
     * Role checks
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isUser(): bool
    {
        return $this->isMember();
    }

    /**
     * Status checks
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isDeactivated(): bool
    {
        return $this->status === 'deactivated';
    }

    /**
     * Relationships
     */
    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id');
    }

    public function juantapProfile(): HasOne
    {
        return $this->hasOne(JuanTapProfile::class, 'user_id');
    }
}
