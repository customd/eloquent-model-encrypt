<?php

namespace CustomD\EloquentModelEncrypt\KeyProviders\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Facades\PemStore;
use CustomD\EloquentModelEncrypt\Contracts\Encryptable;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentModelEncrypt\Exceptions\PemFailureException;

trait ProviderHasUser
{
    protected static function getRecordsUserIds(Model&Encryptable $record, array $extra = []): array
    {
        return match (true) {
            ! empty($extra['UserKeyProviderIds']) => (array) $extra['UserKeyProviderIds'],
            method_exists($record, 'getUserKeyProviderIds') => (array) $record->getUserKeyProviderIds(),
            default => (array) ($record->getAttribute('user_id') ?? auth()->user()?->id ?? [])
        };
    }

    protected static function mapUserKeys(Collection $users): array
    {
        return $users->filter(
            fn($user)=> $user->rsaKey ? true : static::logCriticalError($user)
        )
        ->mapWithKeys(
            fn($user) => [$user->rsaKey->id => $user->rsaKey->public_key]
        )
        ->toArray();
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

        // @phpstan-ignore-next-line - rsaKey->id is from the user model with the trait applied
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


    protected static function logCriticalError(User $user): bool
    {
        Log::critical("User does not have a keypair", [
            'user'        => ['id' => $user->id],
            'keyProvider' => get_called_class()
        ]);
        return false;
    }
}
