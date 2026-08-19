<?php

namespace App\Policies;

class OnlineRegistrationPolicy extends BasePolicy
{
    protected function permissionName(): string
    {
        return 'manage-online-registration';
    }
}