<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class KeyProvider
{
    abstract public static function getPublicKeysForTable($record, $extra = []): array;

    abstract public static function getPrivateKeyForRecord(string $table, int $recordId): ?string;

    protected static function getKeyFromKeystore(string $table, int $id, int $keystoreId)
    {
        try {
            $keystores = config('eloquent-model-encrypt.models.keystore')::join('keystore_keys', function ($join) use ($keystoreId) {
                    $join->on('keystore_keys.keystore_id', '=', 'keystores.id')
                        ->where('keystore_keys.rsa_key_id', '=', $keystoreId);
            })
                ->where('table', $table)
                ->where('ref', $id)
                ->select(
                    'keystores.*',
                    'keystore_keys.key as keystore_key',
                    'keystore_keys.id as keystore_key_id'
                )
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return;
        }

        $keystoreKey = app(config('eloquent-model-encrypt.models.keystore_key'));
        $keystoreKey->id = $keystores->keystore_key_id;
        $keystoreKey->fill([
            'key'         => $keystores->keystore_key,
            'keystore_id' => $keystores->id,
            'rsa_key_id'  => $keystoreId
        ]);

        unset($keystores->keystore_key_id);
        unset($keystores->keystore_key);

        $keystores->Keystores = collect([$keystoreKey]);

        return $keystores;
    }
}
