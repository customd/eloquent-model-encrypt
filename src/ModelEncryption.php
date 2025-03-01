<?php

namespace CustomD\EloquentModelEncrypt;

use CustomD\EloquentModelEncrypt\Traits\Keystore;
use CustomD\EloquentModelEncrypt\Traits\Extenders;
use CustomD\EloquentModelEncrypt\Traits\Decryption;
use CustomD\EloquentModelEncrypt\Traits\Encryption;
use CustomD\EloquentModelEncrypt\KeyProviders\GlobalKeyProvider;
use CustomD\EloquentModelEncrypt\Observers\Encryption as EncryptionObserver;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-ignore trait.unused
 */
trait ModelEncryption
{
    use Extenders;
    use Encryption;
    use Decryption;
    use Keystore;

    /**
     * Which Engine are we using to encrypt / decrypt.
     *
     * @var \CustomD\EloquentModelEncrypt\Abstracts\Engine
     */
    protected $encryptionEngine;

    /**
     * Header used to identify whether the value is encrypted or not.
     *
     * @var string
     */
    protected static $encryptionHeader = '$cd.enc$';

    protected static $defaultKeyProviders = [
        GlobalKeyProvider::class,
    ];

    protected static function getKeyProviders()
    {
        return self::$keyProviders ?? self::$defaultKeyProviders;
    }

    /**
     * Boot the Encryptable trait for a model.
     */
    public static function bootModelEncryption(): void
    {
        //Initialise our observers
        static::observe(EncryptionObserver::class);
    }

    /**
     * Initialialize our encryption engine for this model.
     */
    protected function initEncryptionEngine()
    {
        // Load our config
        $config = config('eloquent-model-encrypt');

        // Encryption Engines available
        $engines = $config['engines'];

        // Table Specific Encryption Engine Listing
        $tables = $config['tables'];

        // Which Engine are we loading
        /** @var class-string<\CustomD\EloquentModelEncrypt\Abstracts\Engine> $engine */
        $engine = $engines[$tables[self::class] ?? 'default'];

        // Instansiate our Engine
        $this->encryptionEngine = new $engine();

        return $this->encryptionEngine;
    }

    public function getEncryptionEngine()
    {
        return $this->encryptionEngine ??= $this->initEncryptionEngine();
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


    public function isCyphertext(?string $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        return strpos($value, self::$encryptionHeader) === 0;
    }

    public function isPlaintext(?string $value): bool
    {
        return ! $this->isCyphertext($value);
    }

    public function isUpdatingEncryptedFields(): bool
    {
        return collect($this->encryptable)
            ->filter(function ($field) {
                return $this->isDirty($field);
            })
            ->isNotEmpty();
    }

    /**
     * method to get the table reference for storage in the database column
     * this allows you to change the way the references are stored ie md5(table name etc)
     */
    public function getTableKeystoreReference(): string
    {
        return method_exists($this, 'getTableKeystoreName') ? $this->getTableKeystoreName() : $this->getTable();
    }

    public function forceEncrypt(): void
    {

        if (! $this->exists) {
            throw new EncryptException("ForceEncrypt can only be called on existing models");
        }

        DB::beginTransaction();
        if ($this->getConnectionName() !== DB::getDefaultConnection()) {
            DB::connection($this->getConnectionName())->beginTransaction();
        }
        $hasTimestamps = $this->timestamps;
        $this->timestamps = false;

        try {
            $this->assignRecordsSynchronousKey(true);
            $this->storeKeyReferences();
            $this->mapEncryptedValues();

            $data = $this->attributes;

            DB::connection($this->getConnectionName())->table($this->getTable())->where($this->getKeyName(), $this->getKey())->update($data);

            if ($this->getConnectionName() !== DB::getDefaultConnection()) {
                DB::connection($this->getConnectionName())->commit();
            }

            DB::commit();
            $this->timestamps = $hasTimestamps;
        } catch (\Exception $e) {
            if ($this->getConnectionName() !== DB::getDefaultConnection()) {
                DB::connection($this->getConnectionName())->rollback();
            }
            DB::rollBack();
            $this->timestamps = $hasTimestamps;
            throw $e;
        }
    }

    //keeping for some debugging temporarily
    public function forceEncrypt_test(): void
    {
        if (! $this->exists) {
            throw new EncryptException("ForceEncrypt can only be called on existing models");
        }

        DB::beginTransaction();
        if ($this->getConnectionName() !== DB::getDefaultConnection()) {
            DB::connection($this->getConnectionName())->beginTransaction();
        }

        $hasTimestamps = $this->timestamps;
        $this->timestamps = false;

        try {
            $this->assignRecordsSynchronousKey(true);
            $this->storeKeyReferences();
            $this->mapEncryptedValues();

            $data = $this->attributes;

            $this->forceFill($data);
            $query = $this->newModelQuery();
            $this->setKeysForSaveQuery($query)->update($data);

            if ($this->getConnectionName() !== DB::getDefaultConnection()) {
                DB::connection($this->getConnectionName())->commit();
            }

            DB::commit();
            $this->timestamps = $hasTimestamps;
        } catch (\Exception $e) {
            if ($this->getConnectionName() !== DB::getDefaultConnection()) {
                DB::connection($this->getConnectionName())->rollback();
            }
            DB::rollBack();
            $this->timestamps = $hasTimestamps;
            throw $e;
        }
    }
}
