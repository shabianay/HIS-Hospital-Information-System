<?php

namespace App\Policies;

class BedPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-inpatient';
    }
}