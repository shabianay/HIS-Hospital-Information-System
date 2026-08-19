<?php

namespace App\Policies;

class RefundPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-finance';
    }
}