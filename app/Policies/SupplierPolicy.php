<?php

namespace App\Policies;

class SupplierPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-purchasing';
    }
}