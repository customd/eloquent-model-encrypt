<?php

namespace CustomD\EloquentModelEncrypt;

use Illuminate\Support\Facades\DB;
use CustomD\EloquentModelEncrypt\Traits\Keystore;
use CustomD\EloquentModelEncrypt\Traits\Extenders;
use CustomD\EloquentModelEncrypt\Traits\Decryption;
use CustomD\EloquentModelEncrypt\Traits\Encryption;
use CustomD\EloquentModelEncrypt\KeyProviders\GlobalKeyProvider;

trait ModelEncryption
{
    use Extenders;
    use Encryption;
    use Decryption;
    use Keystore;

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

    protected static $keyProviders = [
        GlobalKeyProvider::class,
    ];

    /**
     * Boot the Encryptable trait for a model.
     */
    public static function bootModelEncryption(): void
    {
        //Initialiase our encryption engine
        self::initEncryptionEngine();

        //Initialise our observers
        self::initEncryptionObservers();
    }

    /**
     * Initialialize our encryption engine for this model.
     */
    protected static function initEncryptionEngine(): void
    {
        // Load our config
        $config = config('eloquent-model-encrypt');

        // Encryption Engines available
        $engines = $config['engines'];

        // Table Specific Encryption Engine Listing
        $tables = $config['tables'];

        // Which Engine are we loading
        $engine = isset($tables[self::class]) ? $engines[$tables[self::class]] : $engines['default'];

        // Instansiate our Engine
        self::$encryptionEngine = new $engine();
    }

    /**
     * Setup our observers for this model.
     */
    protected static function initEncryptionObservers(): void
    {
        static::saving(function ($model) {
            //When we start saving - start our transaction
            DB::beginTransaction();
        });

        static::creating(function ($model) {
            // We are creating a new record, lets setup a new sync key for the record and encrypt the fields
            $model::$encryptionEngine->assignSynchronousKey();
            self::mapEncryptedValues($model);
        });

        static::updating(function ($model) {
            // Editing a record, lets get the sync key for this record and encrypt the fields that are set.
            if (! $model::$encryptionEngine->getSynchronousKey()) {
                $model::$encryptionEngine->assignSynchronousKey();
                $model->storeKeyReferences();
            }
            self::mapEncryptedValues($model);
        });

        static::created(function ($model) {
            // Record is created, lets store the new keystore records....
            $model->storeKeyReferences();
        });

        static::saved(function ($model) {
            // Everything is complete, commit our transaction!
            DB::commit();
        });

        static::retrieved(function ($model) {
            //assign the key to allow for decryption
            try {
                $key = $model->getPrivateKeyForRecord();
                $model::$encryptionEngine->assignSynchronousKey($key);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
                \Log::debug('Did not find a key for ' . $model->getTable());
                // Do nothig for now
                // could be we have some items that are not already enctypted
        // (ie encrypted added in after the records where created)
            }
        });
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
