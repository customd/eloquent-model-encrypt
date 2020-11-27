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

    public function keystore()
    {
        return $this->belongsTo(Keystore::class);
    }
}
