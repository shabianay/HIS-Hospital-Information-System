<?php

namespace App\Policies;

class PatientPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-patients';
    }
}
