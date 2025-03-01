<?php

namespace CustomD\EloquentModelEncrypt\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * CustomD\EloquentModelEncrypt\Model\Keystore
 *
 * @property integer $id
 * @property string $table
 * @property integer $ref
 * @property string $key
 * @property-read \CustomD\EloquentModelEncrypt\Model\Keystore[] $keystore
 */
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
        return $this->hasMany(config('eloquent-model-encrypt.models.keystore_key'));
    }
}
