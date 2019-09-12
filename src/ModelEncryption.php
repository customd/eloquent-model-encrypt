<?php

namespace CustomD\EloquentModelEncrypt;

use CustomD\EloquentModelEncrypt\Traits\Extenders;

trait ModelEncryption
{
    use Extenders;

    /**
     * Which Engine are we using to encrypt / decrypt.
     *
     * @var CustomD\EloquentModelEncrypt\Abstracts\Engine
     */
    protected static $encryptionEngine;

    /**
     * Header used to identify whether the value is encrypted or not.
     *
     * @var string
     */
    protected static $encryptionHeader = '$cd.enc$';

    /**
     * Boot the Encryptable trait for a model.
     */
    public static function bootModelEncryption(): void
    {
        $config = config('eloquent-model-encrypt');
        $engines = $config['engines'];
        $tables = $config['tables'];

        $engine = (
            isset($tables[self::class])
            && class_exists($engines[$tables[self::class]])
        ) ? $engines[$tables[self::class]] : $engines['default'];

        self::$encryptionEngine = new $engine();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isEncryptable(string $key): bool
    {
        if (! isset($this->encryptable)) {
            return false;
        }

        return in_array($key, $this->encryptable);
    }

    /**
     * Decrypt a value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function decryptAttribute(string $value): ?string
    {
        if ($value && $this->isValueEncrypted($value)) {
            $value = self::$encryptionEngine->decrypt($this->stripEncryptionHeaderFromValue($value));
        }

        return $value;
    }

    /**
     * Gets the encrypted string without our identifier.
     *
     * @param [type] $value
     */
    protected function stripEncryptionHeaderFromValue($value): ?string
    {
        if (substr($value, 0, strlen(self::$encryptionHeader)) === self::$encryptionHeader) {
            $value = substr($value, strlen(self::$encryptionHeader));
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
    protected function encryptAttribute(string $value): ?string
    {
        if ($value) {
            $value = self::$encryptionHeader.self::$encryptionEngine->encrypt($value);
        }

        return $value;
    }

    /**
     * checks whether the value is currently encrypted or not.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValueEncrypted(string $value): bool
    {
        //if position 0 has the header string we are a match :-)
        return strpos($value, self::$encryptionHeader) === 0;
    }
}
