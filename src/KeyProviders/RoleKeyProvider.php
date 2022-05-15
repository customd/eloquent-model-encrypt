<?php
namespace CustomD\EloquentModelEncrypt\KeyProviders;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Model\RsaKey;
use CustomD\EloquentModelEncrypt\Model\Keystore;
use CustomD\EloquentModelEncrypt\Model\KeystoreKey;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

/**
 * these methods all extend over the Eloquent methods.
 */
abstract class RoleKeyProvider extends KeyProvider
{

    public static function getRoleModel(): Model
    {
        throw_unless(isset(static::$model), 'Please set your model for this role');
        return static::$model;
    }

    public static function getRole(): string
    {
        throw_unless(isset(static::$role), 'Please set your role');
        return static::$role;
    }

    public static function getPublicKeysForTable(Model $record, $extra = []): array
    {
        //get all users keys from role Develoepr
        $key = static::getRoleModel()::first();

        if (! $key) {
            return [];
        }

        return [$key->rsa_key_id => $key->rsaKey->public_key];
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        $user = auth()->user();

        if ($user === null || ! $user->hasRole(static::getRole())) {
            return null;
        }

        $roleStore = static::getRoleModel()::first();

        if (! $roleStore) {
            return null;
        }

        $roleSecrets = json_decode($roleStore->key, true);

        $encryptedKey = $roleStore->rsaKey->private_key;

        $roleKeystore = EloquentAsyncKeys::setKeys(null, $encryptedKey, $roleSecrets['password'], $roleSecrets['salt']);
        $privateKey = $roleKeystore->getDecryptedPrivateKey();

        $rsa_keystore_id = $roleStore->rsaKey->id;
        $rec = self::getKeyFromKeystore($table, $recordId, $rsa_keystore_id);

        return EloquentAsyncKeys::reset()->decryptWithKey($privateKey, $rec->key, $rec->Keystores->first()->key);
    }

    public static function setupRoleKey(): void
    {
        if (static::getRoleModel()::count() === 0) {
            $password = Str::random(16);

            $rsa = EloquentAsyncKeys::reset()
                ->setPassword($password)
                ->setSalt(true)
                ->create();

            $publicKey = $rsa->getPublicKey();
            $privateKey = $rsa->getPrivateKey();

            $keystore = RsaKey::create([
                'public_key'  => $publicKey,
                'private_key' => $privateKey,
            ]);

            $keyPassPhrase = json_encode([
                'password' => $password,
                'salt'     => $rsa->getSalt(),
            ]);

            static::getRoleModel()::create([
                'rsa_key_id' => $keystore->id,
                'key'        => $keyPassPhrase,
            ]);
        }
    }

    public static function addUserKey(User $user): void
    {
        $groupKey = static::getRoleModel()::firstOrFail();

        if ($groupKey->getPrivateKeyForRecord() === null) {
            throw new RuntimeException("Failed to Assign role to user");
        }

        $keystore = Keystore::where('ref', $groupKey->getKey())->where('table', $groupKey->getTable())->first();

        //exists only true if user saved at least once and therefore has a unique id.
        if ($user->exists) {
            KeystoreKey::where('keystore_id', $keystore->id)
            ->where('rsa_key_id', $user->rsa_key_id)
            ->delete();

            $groupKey->forceEncrypt();
        } else {
            $called = \get_called_class();

            User::saved(
                function ($object) use ($called, $user) {
                    if ($user->getKey() != $object->getKey()) {
                        return;
                    }

                    $called::addUserKey($user);
                }
            );
        }
    }
}
