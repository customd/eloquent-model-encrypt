<?php

namespace CustomD\EloquentModelEncrypt\Model;

use Illuminate\Database\Eloquent\Model;

class Keystore extends Model
{
    protected $fillable = [
        'table',
        'ref',
        'key',
    ];

    public $timestamps = false;

    public function keyStores()
    {
        return $this->hasMany(config('eloquent-model-encrypt.models.keystore_key')::class);
    }
}
