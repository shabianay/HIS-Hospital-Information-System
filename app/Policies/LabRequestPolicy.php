<?php

namespace App\Policies;

use App\Models\User;

class LabRequestPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-lab';
    }

    private function canManageRequest(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('manage-lab') || $user->hasPermissionTo('manage-emr');
    }

    public function viewAny(User $user): bool
    {
        return $this->canManageRequest($user);
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $this->canManageRequest($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageRequest($user);
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $this->canManageRequest($user);
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $this->canManageRequest($user);
    }
}