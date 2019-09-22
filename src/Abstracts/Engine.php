<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

abstract class Engine
{
    /**
     * Decrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    abstract public function encrypt(string $value): ?string;

    /**
     * Encrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    abstract public function decrypt(string $value): ?string;

    abstract public function assignSynchronousKey($synchronousKey = null): void;
}
