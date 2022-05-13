<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use CustomD\EloquentModelEncrypt\Exceptions\EngineNotFoundException;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Decryption
{
    /**
     * Decrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function decryptAttribute(?string $value): ?string
    {
        if (! $this->isValueEncrypted($value)) {
            return $value;
        }

        //false does not create a new key
        $this->assignRecordsSynchronousKey(false);

        try {
            if (! method_exists($this->getEncryptionEngine(), 'decrypt')) {
                Log::critical('No encryption engine method available to decrypt the record');

                throw new EngineNotFoundException('Encryption Engine Not Found');
            }

            return $this->getEncryptionEngine()->decrypt($this->stripEncryptionHeaderFromValue($value));
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * Gets the encrypted string without our identifier.
     *
     * @param string $value
     */
    protected function stripEncryptionHeaderFromValue($value): ?string
    {
        if (substr($value, 0, strlen(self::$encryptionHeader)) === self::$encryptionHeader) {
            $value = substr($value, strlen(self::$encryptionHeader));
        }

        return $value;
    }
}
