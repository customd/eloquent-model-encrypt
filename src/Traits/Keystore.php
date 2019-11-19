<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use CustomD\EloquentModelEncrypt\Model\KeystoreKey;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentModelEncrypt\Model\Keystore as KeystoreModel;

/**
 * these methods all extend over the Eloquent methods.
 */
trait Keystore
{
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
        $id = $this->{$this->primaryKey};
        $table = $this->getTable();

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
     */
    public function updateKeyReferences()
    {
        $id = $this->{$this->primaryKey};
        $table = $this->getTable();

        $cipherData = $this->buildCipherData();

        $keystore = KeystoreModel::where('table', $table)
            ->where('ref', $id)
            ->first();

        $keystore->key = $cipherData['cipherText'];
        $keystore->save();

        foreach ($cipherData['keys'] as $keystoreId => $key) {
            $keystoreKey = KeystoreKey::firstOrNew(
                [
                    'keystore_id' => $keystore->id,
                    'rsa_key_id' => $keystoreId,
                ]
            );

            $keystoreKey->key = $key;
            $keystoreKey->save();
        }
    }

    /**
     * store our encrypted key references.
     */
    public function storeKeyReferences(): void
    {
        $id = $this->{$this->primaryKey};
        $table = $this->getTable();
        $cipherData = $this->buildCipherData();

        $keystore = KeystoreModel::create([
            'table' => $table,
            'ref' => $id,
            'key' => $cipherData['cipherText'],
        ]);

        $keystoreKeys = [];

        foreach ($cipherData['keys'] as $keystoreId => $key) {
            $keystoreKeys[] = [
                'keystore_id' => $keystore->id,
                'rsa_key_id' => $keystoreId,
                'key' => $key,
            ];
        }

        KeystoreKey::insert($keystoreKeys);
    }
}
