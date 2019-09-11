<?php

namespace CustomD\EloquentModelEncrypt;

use CustomD\EloquentModelEncrypt\Abstracts\Engine;

class EncryptionEngine extends Engine
{
    /**
     * Decrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    public function decrypt(string $value): ?string
    {
        if ($value) {
            $value = \decrypt($value);
        }

        return $value;
    }

    /**
     * Encrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    public function encrypt(string $value): ?string
    {
        if ($value) {
            $value = \encrypt($value);
        }

        return $value;
    }
}
