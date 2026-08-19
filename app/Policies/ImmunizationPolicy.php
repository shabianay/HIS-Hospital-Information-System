<?php

namespace App\Policies;

class ImmunizationPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-immunization';
    }
}