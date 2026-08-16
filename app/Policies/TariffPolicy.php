<?php

namespace App\Policies;

class TariffPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-master-data';
    }
}
