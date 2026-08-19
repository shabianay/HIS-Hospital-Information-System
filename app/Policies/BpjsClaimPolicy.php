<?php

namespace App\Policies;

class BpjsClaimPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-bpjs';
    }
}