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
     */
    public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), 'rsa_key_id');
    }

    public function keystore()
    {
        return $this->belongsTo(Keystore::class);
    }
}
