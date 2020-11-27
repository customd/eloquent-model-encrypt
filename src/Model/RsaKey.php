<?php

namespace CustomD\EloquentModelEncrypt\Model;

use Illuminate\Database\Eloquent\Model;

class RsaKey extends Model
{
    //set our table name
    protected $table = 'rsa_keys';

    protected $fillable = [
        'public_key',
        'private_key',
        'version'
    ];

    /**
     * reference our User Model.
     * @deprecated 1.6.1
     */
    public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), 'rsa_key_id');
    }

    public function keystore()
    {
        return $this->belongsTo(config('eloquent-model-encrypt.models.keystore'));
    }
}
