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

    /**
     * assigns a synchronous key.
     *
     * @param string|null $synchronousKey
     */
    abstract public function assignSynchronousKey($synchronousKey = null): void;

    /**
     * Retrieves the current synchronous key.
     *
     * @return string|null
     */
    public function getSynchronousKey(): ?string
    {
        return $this->synchronousKey;
    }
}
