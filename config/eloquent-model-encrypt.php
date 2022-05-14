<?php



return [
    'engines' => [
        'default' => \CustomD\EloquentModelEncrypt\EncryptionEngine::class,
    ],
    'tables' => [
    ],
    'publickey'     => env('ELOQUENT_MODEL_GLOBAL_PUBLIC_KEY', \storage_path() . '/_certs/public.key'),
    'privatekey'    => env('ELOQUENT_MODEL_GLOBAL_PRIVATE_KEY', \storage_path() . '/_certs/private.key'),
    'models' => [
        'keystore'      => \CustomD\EloquentModelEncrypt\Model\Keystore::class,
        'keystore_key'  => \CustomD\EloquentModelEncrypt\Model\KeystoreKey::class,
        'rsa_key'       => \CustomD\EloquentModelEncrypt\Model\RsaKey::class,
    ],
    'throw_on_missing_key' => env('ELOQUENT_MODEL_THROW_ON_MISSING_KEY', false),
    'encrypt_empty_string' => env('ELOQUENT_MODEL_ENCRYPT_EMPTY_STRING', false),
    'encrypt_null_value' => env('ELOQUENT_MODEL_ENCRYPT_NULL_VALUE', false),
    'pem' => [
        'class' => \CustomD\EloquentModelEncrypt\Store\SessionPem::class,
        'cache' => null, // set to a specific cache if using a different cache from normal
        'session' => '_cdpem_', //what key should be used in your session?
    ]

];
