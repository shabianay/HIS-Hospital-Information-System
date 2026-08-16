<?php

namespace App\Policies;

class LabTestPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-lab';
    }
}