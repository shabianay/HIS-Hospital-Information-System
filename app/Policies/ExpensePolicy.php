<?php

namespace App\Policies;

class ExpensePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-finance';
    }
}