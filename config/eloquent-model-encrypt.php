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

    /**
     * Added in V3 - defaulting off and making opt-in
     * pem - Manage storage and retrieval of your Private Encrypted Key automatically or manually
     *  -- class options SessionPem || CachePem
     *  -- cache - optional - if setting to a specific cache instance -- only if using CachePem
     *  -- session - optional - use a specific key for the session key for your users. (Watch session regenerate as can loose it due to that)
     * listener - Listen to login events automatically and set user pem
     */
    'pem' => [
        'class' => null, // \CustomD\EloquentModelEncrypt\Store\SessionPem::class, // null to disable
        'cache' => null, // set to a specific cache if using a different cache from normal
        'session' => '_cdpem_', //what key should be used in your session?
    ],
    'listener' => false,

];
