<?php

namespace App\Policies;

class AuditLogPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-users';
    }
}