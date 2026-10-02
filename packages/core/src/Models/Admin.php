<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Reyhan\Core\Database\Factories\AdminFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Guarded(['id'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable implements FilamentUser, HasName, ProvidesActivityTitle
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(['created_at', 'updated_at', 'password', 'remember_token'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function activityTitle(): ?string
    {
        return $this->name.' ('.$this->email.')';
    }

    /**
     * Determine if admin can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Display name for Filament UI.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Default guard for admin permissions.
     */
    protected string $guard_name = 'admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
