<?php

namespace App\Models\KeyProviders\Keystore;

use Illuminate\Database\Eloquent\Model;
use App\KeyProviders\DeveloperUserKeyProvider;
use CustomD\EloquentModelEncrypt\Model\RsaKey;
use CustomD\EloquentModelEncrypt\ModelEncryption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomD\EloquentModelEncrypt\Contracts\Encryptable;

class DeveloperRole extends Model implements Encryptable
{
    use ModelEncryption;

    /**
     * @inheritDoc
     */
    protected $table = 'keystore_role_developer';

    /**
     * @inheritDoc
     */
    protected static $keyProviders = [
        DeveloperUserKeyProvider::class,
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'rsa_key_id',
        'key',
    ];

    /**
     * @inheritDoc
     */
    protected $encryptable = [
        'key',
    ];

    public function rsaKey(): BelongsTo
    {
        return $this->belongsTo(RsaKey::class, 'rsa_key_id');
    }
}
