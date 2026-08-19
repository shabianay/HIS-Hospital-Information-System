<?php

namespace App\Policies;

class PurchaseOrderPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-purchasing';
    }
}