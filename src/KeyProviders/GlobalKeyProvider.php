<?php

namespace CustomD\EloquentModelEncrypt\KeyProviders;

use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

/**
 * these methods all extend over the Eloquent methods.
 */
class GlobalKeyProvider extends KeyProvider
{
    /**
     * Should return keystore_id => public key for the ones we want!
     */
    public static function getPublicKeysForTable($record, $extra = []): array
    {
        return [0 => \storage_path() . '/_certs/public.key'];
    }

    /**
     * Undocumented function.
     *
     * @return string
     */
    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        $rec = self::getKeyFromKeystore($table, $recordId, 0);

        if ($rec === null || $rec->Keystores->isEmpty()) {
            return null;
        }

        $privateKey = \storage_path() . '/_certs/private.key';
        $password = config('app.key');
        $keystore = EloquentAsyncKeys::reset()->setPrivateKey($privateKey)->setPassword($password);
        $priv = $keystore->getDecryptedPrivateKey();

        return EloquentAsyncKeys::reset()->decryptWithKey($priv, $rec->key, $rec->Keystores->first()->key);
    }
}
