<?php

namespace App\Policies;

class SurgeryPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-surgery';
    }
}