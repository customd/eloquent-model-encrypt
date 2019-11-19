<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

use CustomD\EloquentModelEncrypt\Model\Keystore;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class KeyProvider
{
    abstract public static function getPublicKeysForTable($record, $extra = []): array;

    abstract public static function getPrivateKeyForRecord(string $table, int $recordId): ?string;

    protected static function getKeyFromKeystore(string $table, int $id, int $keystoreId)
    {
        try {
            $rec = Keystore::where('table', $table)
                ->where('ref', $id)
                ->whereHas('Keystores', static function ($query) use ($keystoreId) {
                    $query->where('rsa_key_id', $keystoreId);
                })
                ->with(
                    [
                        'Keystores' => static function ($query) use ($keystoreId) {
                            $query->where('rsa_key_id', $keystoreId);
                        },
                    ]
                )
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return;
        }

        return $rec;
    }
}
