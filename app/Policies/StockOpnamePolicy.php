<?php

namespace App\Policies;

class StockOpnamePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-stock-opname';
    }
}