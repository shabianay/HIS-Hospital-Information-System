<?php

namespace App\Policies;

class SchedulePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-master-data';
    }
}
