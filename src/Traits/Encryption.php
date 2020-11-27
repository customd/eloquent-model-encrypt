<?php

namespace CustomD\EloquentModelEncrypt\Traits;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Encryption
{
    /**
     * Map through and encrypt all our values.
     */
    public function mapEncryptedValues(): void
    {
        foreach ($this->attributes as $field => $value) {
            $this->setEncryptableAttribute($field, $value);
        }
    }

    /**
     * Extend the Eloquent method so properties present in
     * $encrypt are encrypted whenever they are set.
     *
     * @param string $key      The attribute key
     * @param string $value    Attribute value to set
     *
     * @see Model::setAttribute
     *
     * @return mixed
     */
    protected function setEncryptableAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        if ($this->isEncryptable($key) && ! $this->isValueEncrypted($value)) {
            $value = $this->encryptAttribute($value);
            $this->attributes[$key] = $value;
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
        if ($value) {
            $value = self::$encryptionHeader . $this->getEncryptionEngine()->encrypt($value);
        }

        return $value;
    }
}
