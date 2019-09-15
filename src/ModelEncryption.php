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
    use Extenders, Encryption, Decryption, Keystore;

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
        self::_initEncryptionEngine();

        //Initialise our observers
        self::_initEncryptionObservers();
    }

    /**
     * Initialialize our encryption engine for this model.
     */
    protected static function _initEncryptionEngine(): void
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
    protected static function _initEncryptionObservers(): void
    {
        //When we start saving - start our transaction
        static::saving(function ($model) {
            //transactionStart on our $model
            DB::beginTransaction();
        });

        // We are creating a new record, lets setup a new sync key for the record and encrypt the fields
        static::creating(function ($model) {
            $model::$encryptionEngine->assignSyncronousKey();
            self::_mapEncryptedValues($model);
        });

        // Editing a record, lets get the sync key for this record and encrypt the fields that are set.
        static::updating(function ($model) {
            //$syncronousKey Get existging one somehow
            self::_mapEncryptedValues($model);
        });

        // Record is created, lets store the new keystore records....
        static::created(function ($model) {
            // create the keystore entries
            $model->storeKeyReferences();
        });

        // Everything is complete, commit our transaction!
        static::saved(function ($model) {
            //TransactionCommit on our $model
            DB::commit();
        });

        static::retrieved(function ($model) {
            //decrypt current values
            $key = $model->getPrivateKeyForRecord();
            $model::$encryptionEngine->assignSyncronousKey($key);
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
