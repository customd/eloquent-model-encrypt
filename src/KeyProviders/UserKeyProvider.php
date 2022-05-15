<?php
namespace CustomD\EloquentModelEncrypt\KeyProviders;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentModelEncrypt\KeyProviders\Traits\HasUser;

class UserKeyProvider extends KeyProvider
{
    use HasUser;

    /**
     * Should return keystore_id => public key for the ones we want!
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @param array $extra
     */
    public static function getPublicKeysForTable(Model $record, $extra = []): array
    {
        $user_ids = collect(self::getRecordsUserIds($record, $extra))->filter()->unique();

        if ($user_ids->isEmpty()) {
            return [];
        }

        $users = self::getUserModel()::whereIn('id', $user_ids)->get();

        return $users->filter(
            fn ($user) => $user->rsaKey  ? true  : self::logCriticalError($user, $record)
        )->mapWithKeys(fn($user) => [$user->rsaKey->id => $user->rsaKey->public_key])->toArray();
    }

    protected static function logCriticalError(User $user, Model $record): bool
    {
        Log::critical("User does not have a keypair", [
            'user'   => $user->id,
            'record' => ['type' => get_class($record), 'id' => $record->getKey()]
        ]);
        return false;
    }
}
