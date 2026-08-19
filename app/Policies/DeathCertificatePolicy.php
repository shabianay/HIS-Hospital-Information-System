<?php

namespace App\Policies;

class DeathCertificatePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-death-certificate';
    }
}