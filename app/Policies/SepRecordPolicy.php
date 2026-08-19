<?php

namespace App\Policies;

class SepRecordPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-bpjs';
    }
}