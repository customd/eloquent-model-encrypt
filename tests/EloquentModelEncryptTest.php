<?php

namespace CustomD\EloquentModelEncrypt\Tests;

use Orchestra\Testbench\TestCase;
use CustomD\EloquentModelEncrypt\ServiceProvider;
use CustomD\EloquentModelEncrypt\Facades\EloquentModelEncrypt;

class EloquentModelEncryptTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'eloquent-model-encrypt' => EloquentModelEncrypt::class,
        ];
    }

    public function testExample()
    {
        $this->assertSame(1, 1);
    }
}
