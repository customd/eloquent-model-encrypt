<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

abstract class Engine
{

    protected string $cipher = 'AES-128-CBC';

    protected int $keyLength = 16;

    protected ?string $synchronousKey = null;

    protected $encryptionEngine;

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
    abstract public function assignSynchronousKey(?string $synchronousKey = null): void;

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
