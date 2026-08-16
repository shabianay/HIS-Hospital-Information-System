<?php

namespace App\Policies;

class AppointmentPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-appointments';
    }
}
