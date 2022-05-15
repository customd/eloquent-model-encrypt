<?php

namespace CustomD\EloquentModelEncrypt\KeyProviders\Traits;

use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Facades\PemStore;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentModelEncrypt\Exceptions\PemFailureException;

trait HasUser
{
    protected static function getRecordsUserIds(Model $record, array $extra = []): array
    {
        return match (true) {
            ! empty($extra['UserKeyProviderIds']) => (array) $extra['UserKeyProviderIds'],
            method_exists($record, 'getUserKeyProviderIds') => (array) $record->getUserKeyProviderIds(),
            default => (array) ($record->getAttribute('user_id') ?? auth()->user()?->id ?? [])
        };
    }

    /**
     * @return class-string<\Illuminate\Foundation\Auth\User>
     */
    protected static function getUserModel()
    {
        return config('auth.providers.users.model');
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        $rsa_keystore_id = $user->rsaKey->id;

        $rec = self::getKeyFromKeystore($table, $recordId, $rsa_keystore_id);

        if ($rec === null) {
            return null;
        }

        if (! PemStore::hasPem()) {
            throw new PemFailureException('User Should not be able to be logged in without a PEM');
        }

        return EloquentAsyncKeys::reset()
            ->decryptWithKey(PemStore::getPem(), $rec->key, $rec->Keystores->first()->key);
    }
}
