<?php

namespace CustomD\EloquentModelEncrypt\Facades;

use Illuminate\Support\Facades\Facade;

class EloquentModelEncrypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'eloquent-model-encrypt';
    }
}
