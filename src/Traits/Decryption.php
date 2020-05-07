<?php

namespace CustomD\EloquentModelEncrypt\Traits;

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
        if (!$this->isValueEncrypted($value)) {
            return $value;
        }

        // This optimises retrieval of keys so that we only try loading
        // keystores for an entry if we've got a value to decrypt.
        if (!$this->getEncryptionEngine()->getSynchronousKey()) {
            try {
                $key = $this->getPrivateKeyForRecord();
                $this->getEncryptionEngine()->assignSynchronousKey($key);
            } catch (DecryptException $e) {
                \Log::warning('Did not find a key for ' . $this->getTable(), [
                    'message' => $e->getMessage(),
                    'key' => $this->getKey(),
                    'user' => \Auth::user() ? \Auth::user()->getKey() : null
                ]);
                $key = false;
            }
        }

        try {
            if (!method_exists($this->getEncryptionEngine(), 'decrypt')) {
                \Log::critical('No encryption engine method available to decrypt the record');

                throw new EngineNotFoundException('Encryption Engine Not Found');
            }

            return $this->getEncryptionEngine()->decrypt($this->stripEncryptionHeaderFromValue($value));
        } catch (\Exception $exception) {
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
