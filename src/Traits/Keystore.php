<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Keystore
{

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
                throw new DecryptException("You cannot update an encrpyted record without updating all fields");
            }

            \Log::warning('Did not find a key for ' . $this->getTableKeystoreReference(), [
                'message' => $e->getMessage(),
                'key'     => $this->getKey(),
                'user'    => \Auth::user() ? \Auth::user()->getKey() : null
            ]);
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
        $id = $this->getKey();
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
     * Update our key references:.
     * @deprecated
     */
    public function updateKeyReferences()
    {
        $this->storeKeyReferences();
    }

    /**
     * store our encrypted key references.
     */
    public function storeKeyReferences(): void
    {
        $id = $this->getKey();
        $table = $this->getTableKeystoreReference();
        $cipherData = $this->buildCipherData();

        $keystore = $this->getKeystoreModel()::updateOrCreate(
            [
                'table' => $table,
                'ref'   => $id,
            ],
            [

                'key' => $cipherData['cipherText'],
            ]
        );

        foreach ($cipherData['keys'] as $keystoreId => $key) {
            $this->getKeystoreKeyModel()::updateOrCreate(
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

    public function getKeystoreKeyModel()
    {
        return config('eloquent-model-encrypt.models.keystore_key');
    }

    public function getKeystoreModel()
    {
        return config('eloquent-model-encrypt.models.keystore');
    }
}
