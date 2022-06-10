<?php
namespace CustomD\EloquentModelEncrypt\KeyProviders;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use CustomD\EloquentModelEncrypt\Facades\PemStore;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use CustomD\EloquentModelEncrypt\Contracts\Encryptable;
use CustomD\EloquentModelEncrypt\Exceptions\PemFailureException;
use CustomD\EloquentModelEncrypt\KeyProviders\Traits\ProviderHasUser;

/**
 * these methods all extend over the Eloquent methods.
 */
abstract class RoleUserKeyProvider extends KeyProvider
{
    use ProviderHasUser;

    public static function getRole(): string
    {
        throw_unless(isset(static::$role), 'Please set your role');
        return static::$role;
    }


    public static function getUsersWithRoles(): Collection
    {
        $userClass = resolve(static::getUserModel());
        $filterByRole = static::getRole();
        return $userClass->whereHas(
            'roles',
            fn($query) => $query->whereIn('name', (array) $filterByRole)
        )->get();
    }

    public static function getPublicKeysForTable(Model&Encryptable $record, array $extra = []): array
    {

        return static::mapUserKeys(
            static::getUsersWithRoles()
        );
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        $user = auth()->user();

        // @phpstan-ignore-next-line hasRole is a method injected by Role Provider
        if ($user === null || ! $user->hasRole(static::getRole())) {
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

        return EloquentAsyncKeys::reset()->decryptWithKey(PemStore::getPem(), $rec->key, $rec->Keystores->first()->key);
    }
}
