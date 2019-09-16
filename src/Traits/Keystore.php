<?php

namespace CustomD\EloquentModelEncrypt\Traits;

use CustomD\EloquentAsyncKeys\Keys;
use CustomD\EloquentModelEncrypt\Model\TableKeystore;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;

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
        foreach (self::$keyProviders as $keyProvider) {
            $keys = array_merge($keys, $keyProvider::getPublicKeysForTable($this));
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
    protected function getPrivateKeyForRecord(): string
    {
        $id = $this->{$this->primaryKey};
        $table = $this->getTable();

        foreach (self::$keyProviders as $keyProvider) {
            $key = $keyProvider::getPrivateKeyForRecord($table, $id);

            if ($key) {
                return $key;
            }
        }

        throw new DecryptException('No Keys found to decypt with');
    }

    /**
     * store our encrypted key references.
     */
    protected function storeKeyReferences(): void
    {
        //1 which keystore records::::
        //... 1 get all the public keys for encrypting this record:
        $syncronousKey = self::$encryptionEngine->getSyncronousKey();
        $keys = $this->getPublicKeysForTable();

        $id = $this->{$this->primaryKey};
        $table = $this->getTable();

        foreach ($keys as $keystoreId => $publicKey) {
            $keystore = new Keys();
            $key = $keystore->setKeys($publicKey)->encrypt($syncronousKey, true);

            TableKeystore::create([
                'table' => $table,
                'ref' => $id,
                'key' => $key,
                'rsa_keystore_id' => $keystoreId,
            ]);
        }
    }
}
