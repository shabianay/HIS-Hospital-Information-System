<?php

namespace App\Policies;

class MedicinePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-pharmacy';
    }
}
