<?php
namespace CustomD\EloquentModelEncrypt\KeyProviders;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use CustomD\EloquentModelEncrypt\Facades\PemStore;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentModelEncrypt\Exceptions\PemFailureException;

/**
 * these methods all extend over the Eloquent methods.
 */
abstract class RoleUserKeyProvider extends KeyProvider
{

    public static function getRole(): string
    {
        throw_unless(isset(static::$role), 'Please set your role');
        return static::$role;
    }


    public static function getUsersWithRoles(): Collection
    {
        /** @var \Illuminate\Foundation\Auth\User $userClass */
        $userClass = resolve(config('auth.providers.users.model'));
        $filterByRole = static::getRole();
        return $userClass->whereHas(
            'roles',
            fn($query) => $query->whereIn('name', (array) $filterByRole)
        )->get();
    }

    public static function getPublicKeysForTable(Model $record, $extra = []): array
    {

        //get all users keys for the current User
        $users = static::getUsersWithRoles();

        $publicKeys = [];

        foreach ($users as $user) {
            if ($user->rsaKey === null) {
                Log::critical("User does not have a keypair", [
                    'user'   => $user->toArray(),
                    'record' => $record->toArray()
                ]);
            } else {
                $publicKeys[$user->rsaKey->id] = $user->rsaKey->public_key;
            }
        }

        return $publicKeys;
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        $user = auth()->user();

        if ($user === null || ! $user->hasRole(static::getRole())) {
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

        return EloquentAsyncKeys::reset()->decryptWithKey(PemStore::getPem(), $rec->key, $rec->Keystores->first()->key);
    }
}
