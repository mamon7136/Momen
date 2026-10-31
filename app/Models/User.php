<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'locale'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_staff')->withPivot(['role', 'permissions', 'branch_id'])->withTimestamps();
    }

    public function roleIn(Store $store): ?string
    {
        if ($store->owner_id === $this->id) {
            return 'owner';
        }

        return $this->stores()->whereKey($store->id)->first()?->pivot->role;
    }

    /** Role-based permission check inside a store. */
    public function canInStore(Store $store, string $permission): bool
    {
        $role = $this->roleIn($store);
        if ($role === null) {
            return false;
        }
        $map = config('platform.roles.'.$role, []);

        return in_array('*', $map, true) || in_array($permission, $map, true);
    }
}
