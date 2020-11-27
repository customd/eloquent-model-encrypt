# Eloquent Model Encrypt
[![For Laravel 5][badge_laravel]](https://github.com/customd/eloquent-model-encrypt)
[![Build Status](https://travis-ci.org/customd/eloquent-model-encrypt.svg?branch=master)](https://travis-ci.org/customd/eloquent-model-encrypt)
[![Coverage Status](https://coveralls.io/repos/github/customd/eloquent-model-encrypt/badge.svg?branch=master)](https://coveralls.io/github/customd/eloquent-model-encrypt?branch=master)
[![Packagist](https://img.shields.io/packagist/v/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://poser.pugx.org/custom-d/eloquent-model-encrypt/d/total.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://img.shields.io/packagist/l/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Github Issues][badge_issues]](https://github.com/customd/eloquent-model-encrypt/issue)

This package allows for encryption of data using public-private keypairs, it can be extended to a global or per user public private, or even any other combination based on your own keyprovider implementation

## Installation

Install via composer
```bash
composer require custom-d/eloquent-model-encrypt
```

### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
CustomD\EloquentModelEncrypt\ServiceProvider::class,
```

### Register Facade

Register package facade in `config/app.php` in `aliases` section
```php
CustomD\EloquentModelEncrypt\Facades\EloquentModelEncrypt::class,
```

### Publish Configuration File & run migration

```bash
php artisan vendor:publish --provider="CustomD\EloquentModelEncrypt\ServiceProvider" --tag="config"
php artisan migrate
```
Optionally if you have not already installed and run the `` package:
`php artisan asynckey` to generate a global public private keypair ( stored in your storage folder)

## Usage


For your migrations you can now replace the Illuminate versions of these with the CustomD versions:
 ```php
use CustomD\EloquentModelEncrypt\Migration\Blueprint;
use CustomD\EloquentModelEncrypt\Migration\Schema;
 ```
 you can then define
 ```php
 $table->encryptedString('colname', '154');
 $table->encryptedDate('datecolname');
 $table->encryptedTimestamp('timestampcolname');
 ```

On the models you wish to encrypt, you will need to add the following:

```php
use CustomD\EloquentModelEncrypt\ModelEncryption;

class YourModel extends Model {
	use ModelEncryption;

	protected $encryptable = [
        'encrypted_field 1',
        'encrypted_field 2',
    ];
}
```
The above will use the global public private keys (private key password is your laravel app key)

You can set which keys to encrypt and decrypt with by overwriting the following statc property
```php
protected static $keyProviders = [
        GlobalKeyProvider::class,
    ];
```
If an array is passed, it will get the public keys from each and add the encryption to each one,
for decryption will look for the first key available from there to decrypt for the current application / user / process.

### Extending Key Providers
Additional key providers can be added by extending the `CustomD\EloquentModelEncrypt\Abstracts\KeyProvider`

You will need to supply 2 methods:
* **`public static function getPublicKeysForTable($record, $extra = []): array`**
Use this method to return an array of keys from the `table_keystores` table in the format
```php
[
	'id' => key
]
```
if you want to use a file based key, pass the id as 0.

* **`public static function getPrivateKeyForRecord(string $table, int $recordId): string`** - Use this to return the private key to decrypt the record.

Example - to encrypt for only the logged in user:
```php
<?php

namespace App\KeyProviders;

use App\Model\User;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;

/**
 * these methods all extend over the Eloquent methods.
 */
class UserKeyProvider extends KeyProvider
{

    /**
     * Should return keystore_id => public key for the ones we want!
     */
    public static function getPublicKeysForTable($record, $extra = []): array
    {
        $user_ids = collect(self::getRecordsUserIds($record, $extra = []))->filter()->unique();

        if ($user_ids->count() === 0) {
            return [];
        }

        $users = User::whereIn('id', $user_ids)->get();

        $publicKeys = [];

        foreach ($users as $user) {
            $publicKeys[$user->rsaKey->id] = $user->rsaKey->public_key;
        }

        return $publicKeys;
    }

    protected static function getRecordsUserIds($record, $extra = [])
    {
        if (method_exists($record, 'getEncryptionUserIds')) {
            return $record->getEncryptionUserIds();
        }

        if (! empty($record->user_id)) {
            return $record->user_id;
        }

        if (! empty($extra['user_id'])) {
            return $extra['user_id'];
        }

        $user = auth()->user();

        if ($user) {
            return $user->id;
        }
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        //Is the current user currently a developer user?
        $user = \Auth::user();

        if ($user === null) {
            return false;
        }

        $rsa_keystore_id = $user->rsaKey->id;

        $rec = self::getKeyFromKeystore($table, $recordId, $rsa_keystore_id);

        if ($rec === null) {
            return false;
        }

        try {
            $pem = $user->getDecryptedPriveateKey(); //your own mapping on how you store the decryptd PVT keyes

            if ($pem === false) {
                throw new \Exception('User Should not be able to be logged in without a PEM');
            }
        } catch (\Exception $exception) {
            throw new UnauthorizedHttpException('Token', 'User Should not be able to be logged in without a Pvt key');
        }

        return EloquentAsyncKeys::reset()->decryptWithKey($pem, $rec->key, $rec->Keystores->first()->key);
    }
}
```


### Extending Engines

If for some reason you need a specific model to use a different encryption engine this can be done by adding it to the config file
```php

return [
    'engines' => [
		'default' => \CustomD\EloquentModelEncrypt\EncryptionEngine::class,
		'MyEngine' => PathToYourEngine::class
    ],
    'tables' => [
		'MyCustomTable' => 'MyEngine'
    ],
];

```
Engines are to extend the `CustomD\EloquentModelEncrypt\Abstracts\Engine` Class and should implement the following methods:

* **`public function encrypt(string $value): ?string`** - holds the encryption logic - value passed is the unencrypted database field value
* **`public function decrypt(string $value): ?string`** - holds the decryption logic - value passed is the encrypted database field value
* **`public function assignSynchronousKey([$synchronousKey = null]): void`** - allows you to set the synchronous key for encrytion - this is called when creating or retrieving a record.

## Important

If you are writing partial records to encrypted, make sure not to do it without beign able to access the records key, as if you do it will rewrite the key and break the rest of the fields.


From Laravel's Docs:

`When issuing a mass update via Eloquent, the saved and updated model events will not be fired for the updated models. This is because the models are never actually retrieved when issuing a mass update.`

For this reason we have blocked out the batch insert and mass update methods, they will throw an exception. this still does not block teh DB::insert etc from occuring so you can if needbe setup mass insert or update using the base DB class.


## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## Credits

- [Custom D](https://git.customd.com/composert)
- [All contributors](https://git.customd.com/composer/eloquent-model-encrypt/-/graphs/master)


[badge_laravel]: https://img.shields.io/badge/Laravel-5.8%20to%208-orange.svg?style=flat-square
[badge_issues]: https://img.shields.io/github/issues/ARCANEDEV/Support.svg?style=flat-square

