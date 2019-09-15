<?php

namespace CustomD\EloquentModelEncrypt;

use Illuminate\Encryption\Encrypter;
use CustomD\EloquentModelEncrypt\Abstracts\Engine;

class EncryptionEngine extends Engine
{
    protected $cipher = 'AES-128-CBC';

    protected $keyLength = 16;

    protected $syncronousKey = null;

    protected $encryptionEngine;

    /**
     * Undocumented function.
     *
     * @param [type] $encryptionKey
     * @param string $cipher
     */
    public function assignSyncronousKey($syncronousKey = null): void
    {
        if ($syncronousKey === null) {
            $syncronousKey = \random_bytes($this->keyLength);
        }

        $this->syncronousKey = $syncronousKey;

        $this->encryptionEngine = new Encrypter($syncronousKey, $this->cipher);
    }

    public function getSyncronousKey(): string
    {
        return $this->syncronousKey;
    }

    /**
     * Decrypt a value.
     *
     * @param string $cipherText
     *
     * @return string
     */
    public function decrypt(string $cipherText): ?string
    {
        if ($cipherText) {
            $cipherText = $this->encryptionEngine->decrypt($cipherText);
        }

        return $cipherText;
    }

    /**
     * Encrypt a value.
     *
     * @param string $plainText
     *
     * @return string
     */
    public function encrypt(string $plainText): ?string
    {
        if ($plainText) {
            $plainText = $this->encryptionEngine->encrypt($plainText);
        }

        return $plainText;
    }
}
