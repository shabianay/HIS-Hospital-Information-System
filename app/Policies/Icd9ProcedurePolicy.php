<?php

namespace App\Policies;

class Icd9ProcedurePolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-master-data';
    }
}