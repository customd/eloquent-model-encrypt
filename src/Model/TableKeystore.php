<?php

namespace CustomD\EloquentModelEncrypt\Model;

use Illuminate\Database\Eloquent\Model;

class TableKeystore extends Model
{
    protected $fillable = [
        'table',
        'ref',
        'key',
        'rsa_keystore_id',
    ];

    public $timestamps = false;
}
