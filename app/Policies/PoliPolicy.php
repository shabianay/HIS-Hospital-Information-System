<?php

namespace App\Policies;

class PoliPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-master-data';
    }
}
