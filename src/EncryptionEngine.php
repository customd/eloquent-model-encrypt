<?php

namespace CustomD\EloquentModelEncrypt;

use Illuminate\Encryption\Encrypter;
use CustomD\EloquentModelEncrypt\Abstracts\Engine;

class EncryptionEngine extends Engine
{

    public static $CLEAR_RECORD = 0;
    public static $CLEAR_TABLE = 1;
    public static $CLEAR_ALL = 2;
    /**
     * Holds cached versions of keys
     */
    protected static $cachedKeys = [];


    protected string $cipher = 'AES-128-CBC';

    /**
     * @param string|null $synchronousKey
     */
    public function assignSynchronousKey(?string $synchronousKey = null): void
    {
        if ($synchronousKey === null) {
            $synchronousKey = \random_bytes($this->keyLength);
        }

        $this->synchronousKey = $synchronousKey;

        $this->encryptionEngine = new Encrypter($synchronousKey, $this->cipher);
    }

    /**
     * Decrypt a value.
     *
     * @param string $cipherText
     *
     * @return string
     */
    public function decrypt(?string $cipherText): ?string
    {
        if ($cipherText && $this->encryptionEngine) {
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
    public function encrypt(?string $plainText): ?string
    {
        return $this->encryptionEngine->encrypt($plainText);
    }


    public static function getCachedKey(string $table, int|string $id): ?string
    {
        return isset(static::$cachedKeys[$tableKey]) && ! empty(static::$cachedKeys[$tableKey][$id]){
            static::$cachedKeys[$tableKey][$id];
        }
        return null;
    }

    public static function setCachedKey(string $table, int|string $id, string $value): void
    {
        static::$cachedKeys[$table][$id] = $value;
    }

    public static function clearCachedKey(int $level = 2, ?string $table = null, int|string|null $id = null): void
    {
        switch ($level) {
            case self::$CLEAR_RECORD:
                if (filled $table && filled($id) ){
                    unset(static::$cachedKeys[$table][$id]);
                }
                break;
            case self::$CLEAR_TABLE:
                if(filled($table)){
                    unset(static::$cachedKeys[$table]);
                }
                break;
            case self::$CLEAR_ALL:
                static::$cachedKeys = [];
                break;
            default:
                throw new EncryptException("Clear level not defined");
        }
    }

}
