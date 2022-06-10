<?php

namespace CustomD\EloquentModelEncrypt\Traits;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Encryption
{
    public function mapEncryptedValues(): void
    {
        foreach ($this->encryptable as $field) {
            $this->setEncryptableAttribute($field);
        }
    }

    /**
     * Extend the Eloquent method so properties present in
     * $encrypt are encrypted whenever they are set.
     */
    protected function setEncryptableAttribute(string $field): self
    {
        if (! isset($this->attributes[$field])) {
            return $this;
        }

        $value = $this->attributes[$field];

        if ($this->isPlaintext($value)) {
            $this->attributes[$field] = $this->encryptAttribute($value);
        }

        return $this;
    }

    /**
     * Encrypt a value.
     *
     * @param string $value
     * @param string $synchronousKey
     *
     * @return string
     */
    protected function encryptAttribute(?string $value): ?string
    {

        if ($value === null && config('eloquent-model-encrypt.encrypt_null_value', false) === false) {
            return $value;
        }

        if ($value === '' && config('eloquent-model-encrypt.encrypt_empty_string', false) === false) {
            return $value;
        }

        return self::$encryptionHeader . $this->getEncryptionEngine()->encrypt($value);
    }
}
