<?php

namespace CustomD\EloquentModelEncrypt\Tests;

use Orchestra\Testbench\TestCase;
use CustomD\EloquentModelEncrypt\ServiceProvider;
use CustomD\EloquentModelEncrypt\Facades\EloquentModelEncrypt;
use CustomD\EloquentModelEncrypt\EncryptionEngine;


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

	public function testBasicEngine(){
		$original = 'ThisIs My test String';
		$engine = new EncryptionEngine;
		$encoded = $engine->encrypt($original);
		$decoded = $engine->decrypt($encoded);
		$this->assertSame($original, $decoded);
	}

    public function testExample()
    {
        $this->assertSame(1, 1);
    }
}
