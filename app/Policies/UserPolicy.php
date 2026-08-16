<?php

namespace App\Policies;

class UserPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-users';
    }
}
