<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Keystore
{
    public static $CLEAR_RECORD = 0;
    public static $CLEAR_TABLE = 1;
    public static $CLEAR_ALL = 2;
    /**
     * Holds cached versions of keys
     */
    protected static $cachedKeys = [];

    public function assignRecordsSynchronousKey(bool $create = false): void
    {
        //alrady set in the encryption engine
        if ($this->getEncryptionEngine()->getSynchronousKey()) {
            return;
        }

        $tableKey = $this->getTableKeystoreReference();
        $id = $this->getKey();

        //grabbing a cached version
        if ($id && isset(static::$cachedKeys[$tableKey]) && ! empty(static::$cachedKeys[$tableKey][$id])) {
            $this->getEncryptionEngine()->assignSynchronousKey(static::$cachedKeys[$tableKey][$id]);
            return;
        }

        //try getting the existing key
        try {
            $recordKey = $this->getPrivateKeyForRecord();
            $this->getEncryptionEngine()->assignSynchronousKey($recordKey);
        } catch (ModelNotFoundException $e) {
            if ($create) {
                $this->getEncryptionEngine()->assignSynchronousKey();
                return;
            }
            throw new DecryptException();
        } catch (DecryptException $e) {
            if ($create) {
                $encryptedFields = collect($this->encryptable);

                //remove all fields that are empty
                $fields = $encryptedFields->filter(function ($field) {
                    return ! empty($this->attributes[$field]);
                })->toArray();

                $dirty = array_keys($this->getDirty());

                $matchingKeys = array_intersect($fields, $dirty);
                $hasMissing = array_diff($fields, $matchingKeys);

                //make sure all the fields that are needed to be encrypted are set for this action,
                //else will break others data.
                if (empty($hasMissing)) {
                    $this->getEncryptionEngine()->assignSynchronousKey();
                    return;
                }
                throw new DecryptException("You cannot update an encrypted record without updating all fields");
            }

            Log::warning('Did not find a key for ' . $this->getTableKeystoreReference(), [
                'message' => $e->getMessage(),
                'key'     => $this->getKey(),
                'user'    => Auth::user() ? Auth::user()->getKey() : null
            ]);

            if (config('eloquent-model-encrypt.throw_on_missing_key')) {
                throw new DecryptException("You cannot decrypt this record without the correct key");
            }
        }
    }

    /**
     * Gets the keys for our current table.
     *
     * @return array
     */
    protected function getPublicKeysForTable(): array
    {
        $keys = [];
        foreach (self::getKeyProviders() as $keyProvider) {
            $keys += $keyProvider::getPublicKeysForTable($this);
        }

        if (count($keys) === 0) {
            throw new EncryptException('No Keys found to encypt with');
        }

        return $keys;
    }

    /**
     * Gets the key to decrypt the record with.
     *
     * @return string
     */
    public function getPrivateKeyForRecord(): string
    {
        /** @scrutinizer ignore-call */
        $id = $this->getKey();
        /** @scrutinizer ignore-call */
        $table = $this->getTableKeystoreReference();

        $this->getKeystoreModel()::where('table', $table)->where('ref', $id)->firstOrFail();

        foreach (self::getKeyProviders() as $keyProvider) {
            $key = $keyProvider::getPrivateKeyForRecord($table, $id);

            if ($key) {
                return $key;
            }
        }

        throw new DecryptException('No Keys found to decypt with');
    }

    protected function buildCipherData()
    {
        $synchronousKey = $this->getEncryptionEngine()->getSynchronousKey();
        $keys = $this->getPublicKeysForTable();

        return EloquentAsyncKeys::encryptWithKey($keys, $synchronousKey);
    }


    /**
     * store our encrypted key references.
     */
    public function storeKeyReferences(): void
    {
        $id = $this->getKey();
        $table = $this->getTableKeystoreReference();
        $cipherData = $this->buildCipherData();

        $keystore = $this->getKeystoreModel()->updateOrCreate(
            [
                'table' => $table,
                'ref'   => $id,
            ],
            [

                'key' => $cipherData['cipherText'],
            ]
        );

        foreach ($cipherData['keys'] as $keystoreId => $key) {
            $this->getKeystoreKeyModel()->updateOrCreate(
                [
                    'keystore_id' => $keystore->id,
                    'rsa_key_id'  => $keystoreId,
                ],
                [
                    'key' => $key,
                ]
            );
        }

        static::$cachedKeys[$table][$id] = $this->getEncryptionEngine()->getSynchronousKey();
    }


    public function getKeystoreKeyModel(): \Illuminate\Database\Eloquent\Model
    {
        return resolve(config('eloquent-model-encrypt.models.keystore_key'));
    }


    public function getKeystoreModel(): \Illuminate\Database\Eloquent\Model
    {
        return resolve(config('eloquent-model-encrypt.models.keystore'));
    }

    public function recordKeystore()
    {
        return $this->hasOne(
            config('eloquent-model-encrypt.models.keystore'),
            'ref',
            $this->getKeyName()
        )->where('table', $this->getTableKeystoreReference());
    }

    public function scopeWhereHasKeystore(Builder $builder)
    {
        $builder->whereHas('recordKeystore');
    }

    public function scopeWhereDoesntHaveKeystore(Builder $builder)
    {
        $builder->whereDoesntHave('recordKeystore');
    }

    public function clearKeystoreCache(int $level = 0): void
    {
        $table = $this->getTableKeystoreReference();

        switch ($level) {
            case self::$CLEAR_RECORD:
                $id = $this->getKey();
                if ($id) {
                    unset(static::$cachedKeys[$table][$id]);
                }
                $this->initEncryptionEngine();
                break;
            case self::$CLEAR_TABLE:
                unset(static::$cachedKeys[$table]);
                break;
            case self::$CLEAR_ALL:
                static::$cachedKeys = [];
                break;
            default:
                throw new EncryptException("Clear level not defined");
        }
    }
}
