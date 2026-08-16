<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    /**
     * Nama permission yang diperlukan untuk mengakses modul ini.
     */
    abstract protected function permissionName(): string;

    public function before(User $user, $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo($this->permissionName());
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->permissionName());
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo($this->permissionName());
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->permissionName());
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->hasPermissionTo($this->permissionName());
    }
}
