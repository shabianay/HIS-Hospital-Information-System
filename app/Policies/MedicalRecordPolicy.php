<?php

namespace App\Policies;

class MedicalRecordPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-emr';
    }
}
