<?php

namespace App\KeyProviders;

use CustomD\EloquentModelEncrypt\KeyProviders\RoleUserKeyProvider;

/**
 * these methods all extend over the Eloquent methods.
 */
class DeveloperUserKeyProvider extends RoleUserKeyProvider
{
    /**
     * what role is this key for
     *
     * @var string
     */
    protected static $role = 'Developer';
}
