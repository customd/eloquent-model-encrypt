<?php

namespace App\KeyProviders;

use CustomD\EloquentModelEncrypt\KeyProviders\RoleKeyProvider;

class MyRoleKeyProvider extends RoleKeyProvider
{

    /**
     * what role are we working with
     *
     * @var string $role
     */
    protected static $role = 'Developer';

    /**
     * What Model stores the keys
     *
     * @var class-string $model
     */
    protected static $model = \App\Models\DeveloperKey::class;
}
