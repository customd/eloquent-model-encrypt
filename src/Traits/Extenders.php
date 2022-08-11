<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->isEncryptable($key)) {
            $value = $this->decryptAttribute($value);
        }

        return $this->transformModelValue($key, $value);
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

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param  string  $key
     * @return bool
     */
    public function encryptedIsEquivalent($key)
    {
        if (! $this->isEncryptable($key)) {
            return $this->originalIsEquivalent($key);
        }

        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = $this->decryptAttribute(Arr::get($this->attributes, $key));
        $original = $this->decryptAttribute(Arr::get($this->original, $key));

        if ($attribute === $original) {
            return true;
        } elseif (is_null($attribute)) {
            return false;
        } elseif ($this->isDateAttribute($key) || $this->isDateCastableWithCustomFormat($key)) {
            return $this->fromDateTime($attribute) ===
            $this->fromDateTime($original);
        } elseif ($this->hasCast($key, ['object', 'collection'])) {
            return $this->fromJson($attribute) ===
            $this->fromJson($original);
        } elseif ($this->hasCast($key, ['real', 'float', 'double'])) {
            if ($original === null) {
                return false;
            }

            return abs($this->castAttribute($key, $attribute) - $this->castAttribute($key, $original)) < PHP_FLOAT_EPSILON * 4;
        } elseif ($this->hasCast($key, static::$primitiveCastTypes)) {
            return $this->castAttribute($key, $attribute) ===
            $this->castAttribute($key, $original);
        } elseif ($this->isClassCastable($key) && in_array($this->getCasts()[$key], [AsArrayObject::class, AsCollection::class])) {
            return $this->fromJson($attribute) === $this->fromJson($original);
        }

        return is_numeric($attribute) && is_numeric($original)
        && strcmp((string) $attribute, (string) $original) === 0;
    }

    /**
     * Get the attributes that have been changed since the last sync.
     * Overrides original method to parse through encrypted vs non-encrypted values
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (! $this->encryptedIsEquivalent($key)) {
                $dirty[$key] = $this->isEncryptable($key) ? $this->decryptAttribute($value) : $value;
            }
        }

        return $dirty;
    }

    public function insert()
    {
        throw new EncryptException('Cannot Mass insert encrypted records, please use create');
    }

    public function update(/** @scrutinizer ignore-unused */array $attributes = [], /** @scrutinizer ignore-unused */array $options = [])
    {
        throw new EncryptException('Cannot Mass update encrypted records, please use model methods');
    }
}
