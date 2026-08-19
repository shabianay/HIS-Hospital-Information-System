<?php

namespace App\Policies;

class EmergencyVisitPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-igd';
    }
}