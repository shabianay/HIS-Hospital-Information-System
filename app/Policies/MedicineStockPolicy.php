<?php

namespace App\Policies;

class MedicineStockPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-pharmacy';
    }
}
