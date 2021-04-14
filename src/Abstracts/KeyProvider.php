<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class KeyProvider
{
    abstract public static function getPublicKeysForTable($record, $extra = []): array;

    abstract public static function getPrivateKeyForRecord(string $table, int $recordId): ?string;

    protected static function getKeyFromKeystore(string $table, int $id, int $keystoreId)
    {
        $keystoreKey = app(config('eloquent-model-encrypt.models.keystore_key'));
        $table = $keystoreKey->getTable();

        try {
            $keystores = config('eloquent-model-encrypt.models.keystore')::join($table, function ($join) use ($keystoreId) {
                    $join->on($table->qualifyColumn('keystore_id'), '=', 'keystores.id')
                        ->where($table->qualifyColumn('rsa_key_id'), '=', $keystoreId);
            })
                ->where('table', $table)
                ->where('ref', $id)
                ->select(
                    'keystores.*',
                    $table->qualifyColumn('key'). ' as keystore_key',
                    $table->qualifyColumn('id') . ' as keystore_key_id'
                )
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return;
        }

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
