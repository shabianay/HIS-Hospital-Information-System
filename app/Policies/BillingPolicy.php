<?php

namespace App\Policies;

class BillingPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-billing';
    }
}
