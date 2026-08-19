<?php

namespace App\Policies;

class RoomPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-inpatient';
    }
}