<?php

namespace App\Policies;

use App\Models\User;

class AppointmentPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-appointments';
    }

    public function viewVitals(User $user): bool
    {
        return $user->hasPermissionTo('input-vital-signs') || $user->hasPermissionTo($this->permissionName());
    }
}