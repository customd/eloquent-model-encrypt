<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

use CustomD\EloquentModelEncrypt\Contracts\Encryptable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class KeyProvider
{
    abstract public static function getPublicKeysForTable(Model&Encryptable $record, array $extra = []): array;

    abstract public static function getPrivateKeyForRecord(string $table, int $recordId): ?string;

    protected static function getKeyFromKeystore(string $table, int $id, int $keystoreId)
    {
        $keystoreModel = resolve(config('eloquent-model-encrypt.models.keystore'));
        $keystoreKeyModel = resolve(config('eloquent-model-encrypt.models.keystore_key'));
        $keystoreTable = $keystoreKeyModel->getTable();

        try {
            $keystores = $keystoreModel::join($keystoreTable, function ($join) use ($keystoreId, $keystoreKeyModel) {
                    $join->on($keystoreKeyModel->qualifyColumn('keystore_id'), '=', 'keystores.id')
                        ->where($keystoreKeyModel->qualifyColumn('rsa_key_id'), '=', $keystoreId);
            })
                ->where('table', $table)
                ->where('ref', $id)
                ->select(
                    $keystoreModel->qualifyColumn('*'),
                    $keystoreKeyModel->qualifyColumn('key'). ' as keystore_key',
                    $keystoreKeyModel->qualifyColumn('id') . ' as keystore_key_id'
                )
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return;
        }

        //mimic eloquent model for a predictable structure
        //todo - revisit this for V3
        $keystoreKey = new $keystoreKeyModel();
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
