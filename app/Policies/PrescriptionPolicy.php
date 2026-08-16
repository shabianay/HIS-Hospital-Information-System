<?php

namespace App\Policies;

class PrescriptionPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-pharmacy';
    }
}
