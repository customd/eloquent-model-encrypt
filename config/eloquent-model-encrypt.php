<?php



return [
    'engines' => [
        'default' => \CustomD\EloquentModelEncrypt\EncryptionEngine::class,
    ],
    'tables' => [
    ],
    'publickey' => env('ELOQUENT_MODEL_GLOBAL_PUBLIC_KEY', \storage_path() . '/_certs/public.key'),
    'privatekey' => env('ELOQUENT_MODEL_GLOBAL_PRIVATE_KEY', \storage_path() . '/_certs/private.key'),
    'models' => [
        'keystore', \CustomD\EloquentModelEncrypt\Model\Keystore::class,
        'keystore_key', \CustomD\EloquentModelEncrypt\Model\KeystoreKey::class,
        'rsa_key', \CustomD\EloquentModelEncrypt\Model\RsaKey::class,
    ]
];
