<?php

namespace App\Policies;

class DoctorPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-master-data';
    }
}
