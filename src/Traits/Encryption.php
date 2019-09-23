<?php

namespace CustomD\EloquentModelEncrypt\Traits;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Encryption
{
    /**
     * Map through and encrypt all our values.
     *
     * @param Model $model
     */
    protected static function _mapEncryptedValues($model): void
    {
        foreach ($model->attributes as $field => $value) {
            $model->_setAttribute($field, $value);
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
    public function _setAttribute($key, $value)
    {
        if ($this->isEncryptable($key)) {
            $value = $this->encryptAttribute($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Encrypt a value.
     *
     * @param string $value
     * @param string $synchronousKey
     *
     * @return string
     */
    protected function encryptAttribute(string $value): ?string
    {
        if ($value) {
            $value = self::$encryptionHeader . self::$encryptionEngine->encrypt($value);
        }

        return $value;
    }
}
