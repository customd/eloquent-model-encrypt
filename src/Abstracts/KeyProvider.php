<?php

namespace CustomD\EloquentModelEncrypt\Abstracts;

use CustomD\EloquentModelEncrypt\Model\TableKeystore;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class KeyProvider
{
    abstract public static function getPublicKeysForTable(): array;

    abstract public static function getPrivateKeyForRecord(string $table, int $recordId): ?string;

    protected static function getKeyFromKeystore(string $table, int $id)
    {
        try {
            $rec = TableKeystore::where('table', $table)
                ->where('ref', $id)
                ->where('rsa_keystore_id', 0)->firstOrFail();
        } catch (ModelNotFoundException $execption) {
            return;
        }

        return $rec;
    }
}
