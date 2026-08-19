<?php

namespace App\Policies;

class AdmissionPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-inpatient';
    }
}