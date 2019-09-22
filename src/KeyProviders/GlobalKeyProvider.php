<?php

namespace CustomD\EloquentModelEncrypt\KeyProviders;

use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;

/**
 * these methods all extend over the Eloquent methods.
 */
class GlobalKeyProvider extends KeyProvider
{
    /**
     * Should return keystore_id => public key for the ones we want!
     */
    public static function getPublicKeysForTable(): array
    {
        return [0 => \storage_path().'/_certs/public.key'];
    }

    /**
     * Undocumented function.
     *
     * @return string
     */
    public static function getPrivateKeyForRecord(string $table, int $recordId): string
    {
        $rec = self::getKeyFromKeystore($table, $recordId, 0);

        if ($rec === null) {
            return false;
        }

        $privateKey = \storage_path().'/_certs/private.key';
        $password = config('app.key');
        $keystore = new Keypair(null, $privateKey, $password);

        return $keystore->decrypt($rec->key, true);
    }
}
