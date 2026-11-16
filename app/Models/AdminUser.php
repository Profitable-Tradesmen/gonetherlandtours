<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator (full access)',
        self::ROLE_EDITOR => 'Editor (content only)',
    ];

    /** Admin areas an editor may use: the route-name prefixes after "admin.". Everything else is administrators only. */
    public const EDITOR_AREAS = ['dashboard', 'enquiries', 'packages', 'tour-types', 'destinations', 'pages', 'faqs', 'testimonials', 'blog', 'media', 'logout'];

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Can this user open the admin route with this name (e.g. "admin.packages.edit")? */
    public function canAccessRoute(?string $routeName): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $area = explode('.', (string) $routeName)[1] ?? '';

        return in_array($area, self::EDITOR_AREAS, true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
