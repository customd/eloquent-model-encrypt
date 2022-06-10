<?php
namespace CustomD\EloquentModelEncrypt\KeyProviders;

use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentModelEncrypt\Contracts\Encryptable;
use CustomD\EloquentModelEncrypt\KeyProviders\Traits\ProviderHasUser;

class UserKeyProvider extends KeyProvider
{
    use ProviderHasUser;

    public static function getPublicKeysForTable(Model&Encryptable $record, array $extra = []): array
    {
        $userIds = collect(self::getRecordsUserIds($record, $extra))->filter()->unique();

        return static::mapUserKeys(
            self::getUserModel()::whereIn('id', $userIds)->get()
        );
    }
}
