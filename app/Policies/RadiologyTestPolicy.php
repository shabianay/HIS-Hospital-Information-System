<?php

namespace App\Policies;

class RadiologyTestPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-radiology';
    }
}