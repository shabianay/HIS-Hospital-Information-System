<?php

namespace App\Policies;

class RadiologyRequestPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-radiology';
    }
}