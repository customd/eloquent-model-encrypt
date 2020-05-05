<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use Illuminate\Contracts\Encryption\EncryptException;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Extenders
{
    /**
     * Extend the Eloquent method so properties present in
     * $encrypt are decrypted when directly accessed.
     *
     * @param string $key  The attribute key
     *
     * @return string
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isEncryptable($key)) {
            $value = $this->decryptAttribute($value);
        }

        return $value;
    }

    /**
     * Extend the Eloquent method so properties in
     * $encrypt are decrypted when toArray()
     * or toJson() is called.
     *
     * @return mixed
     */
    public function getArrayableAttributes()
    {
        $attributes = parent::getArrayableAttributes();
        foreach ($attributes as $key => $attribute) {
            if ($this->isEncryptable($key)) {
                $attributes[$key] = $this->decryptAttribute($attribute);
            }
        }

        return $attributes;
    }

    public function insert()
    {
        throw new EncryptException('Cannot Mass insert encrypted records, please use create');
    }

    public function update(array $attributes = [], array $options = [])
    {
        throw new EncryptException('Cannot Mass update encrypted records, please use model methods');
    }
}
