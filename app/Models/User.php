<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'serp_id',
        'name',
        'email',
        'profile_photo',
        'password',
        'serp_token',
        'role',

        'can_upload',
        'can_share',

        'upload_limit',
        'upload_reset_at',
        'share_limit',
        'last_login_at',

        'status',
        'created_from',
        'created_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_upload' => 'boolean',
            'can_share' => 'boolean',
            'upload_limit' => 'integer',
            'upload_reset_at' => 'datetime',
            'share_limit' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    public function ebooks()
    {
        return $this->hasMany(\App\Models\Ebook::class, 'user_id');
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSpecialProject(): bool
    {
        return $this->hasAnyRole([
            'specialproject',
            'special_project_dtp',
        ]);
    }

    public function canAccessDashboard(): bool
    {
        return $this->isAdmin() || $this->isSpecialProject();
    }

    public function hasUnlimitedPdfAccess(): bool
    {
        return $this->canAccessDashboard();
    }
}