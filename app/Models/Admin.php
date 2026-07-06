<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by');
    }

    public function confirmedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'confirmed_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
