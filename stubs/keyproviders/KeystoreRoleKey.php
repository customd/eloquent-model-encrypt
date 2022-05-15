<?php

namespace CustomD\EloquentModelEncrypt\Model;

use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Model\RsaKey;
use CustomD\EloquentModelEncrypt\ModelEncryption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomD\EloquentModelEncrypt\Contracts\Encryptable;

class KeystoreRoleKey extends Model implements Encryptable
{

    use ModelEncryption;

    //set our table name
    protected $table = 'keystore_role_keys';

    protected $fillable = [
        'role',
        'rsa_key_id',
        'key'
    ];

    protected $encryptable = [
        'key',
    ];

    public function rsaKey(): BelongsTo
    {
        return $this->belongsTo(RsaKey::class, 'rsa_key_id');
    }


    // /**
    //  * reference our User Model.
    //  * @deprecated 1.6.1
    //  */
    // public function user()
    // {
    //     return $this->hasOne(config('auth.providers.users.model'), 'rsa_key_id');
    // }

    // public function keystore()
    // {
    //     return $this->belongsTo(config('eloquent-model-encrypt.models.keystore'));
    // }
}
