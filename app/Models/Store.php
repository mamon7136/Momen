<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public const RESERVED_SLUGS = ['www', 'admin', 'api', 'app', 'dashboard', 'mail', 'cdn', 'static', 'help', 'support', 'blog', 'status', 'ftp', 'cpanel', 'directadmin', 'webmail'];

    protected function casts(): array
    {
        return [
            'theme_settings' => 'array',
            'settings' => 'array',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(StoreDomain::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_staff')->withPivot(['role', 'permissions', 'branch_id'])->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest('id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'active'
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    /** Merged theme settings (theme defaults + merchant overrides from the page builder). */
    public function themeConfig(): array
    {
        $defaults = config('platform.theme_defaults');
        $theme = $this->theme?->default_settings ?? [];

        return array_replace_recursive($defaults, $theme, $this->theme_settings ?? []);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function primaryUrl(): string
    {
        $custom = $this->domains->firstWhere(fn ($d) => $d->is_primary && $d->verified_at);
        $scheme = config('platform.scheme');

        if ($custom) {
            return $scheme.'://'.$custom->domain;
        }
        if (config('platform.domain') === '*') {
            return url('/s/'.$this->slug);
        }
        if (config('platform.storefront_mode') === 'path') {
            return $scheme.'://'.config('platform.domain').'/s/'.$this->slug;
        }

        return $scheme.'://'.$this->slug.'.'.config('platform.domain');
    }

    public function nextOrderNumber(): string
    {
        $count = Order::withoutGlobalScope('store')->where('store_id', $this->id)->lockForUpdate()->count();

        return (string) (10001 + $count);
    }
}
