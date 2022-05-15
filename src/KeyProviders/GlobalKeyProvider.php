<?php

namespace CustomD\EloquentModelEncrypt\KeyProviders;

use Illuminate\Database\Eloquent\Model;
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
    public static function getPublicKeysForTable(Model $record, $extra = []): array
    {
        return [0 => config('eloquent-model-encrypt.publickey')];
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

        $privateKey = config('eloquent-model-encrypt.privatekey');
        $password = config('app.key');
        $keystore = EloquentAsyncKeys::reset()->setPrivateKey($privateKey)->setPassword($password);
        $priv = $keystore->getDecryptedPrivateKey();

        return EloquentAsyncKeys::reset()->decryptWithKey($priv, $rec->key, $rec->Keystores->first()->key);
    }
}
