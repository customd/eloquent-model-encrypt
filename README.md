<a name="overview"></a>

# Eloquent Model Encrypt

[![For Laravel 5][badge_laravel]](https://github.com/customd/eloquent-model-encrypt)
[![Build Status](https://travis-ci.org/customd/eloquent-model-encrypt.svg?branch=master)](https://travis-ci.org/customd/eloquent-model-encrypt)
[![Coverage Status](https://coveralls.io/repos/github/customd/eloquent-model-encrypt/badge.svg?branch=master)](https://coveralls.io/github/customd/eloquent-model-encrypt?branch=master)
[![Packagist](https://img.shields.io/packagist/v/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://poser.pugx.org/custom-d/eloquent-model-encrypt/d/total.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://img.shields.io/packagist/l/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Github Issues][badge_issues]](https://github.com/customd/eloquent-model-encrypt/issue)

-   [Overview](#overview)
-   [Installation](#installation)
-   [Upgrade from 1.x](#upgrade)
-   [Usage](#usage)
    -   [Key Providers](#keyproviders)
    -   [Migrations](#migrations)
    -   [Models](#models)
    -   [Engines](#engines)
    -   [Artisan](#artisan)
-   [Suggested Packages](#suggestions)



This package allows for encryption of data using public-private keypairs, it can be extended to a global or per user public private, or even any other combination based on your own keyprovider implementation

## Important

If you're making partial updates to an existing encrypted record, ensure your user has access to the records key. If you not, the library will create a new key for that record for your partial update, and you won't be able to decrypt other encrypted fields that weren't written in that update.

From Laravel's Docs:

`When issuing a mass update via Eloquent, the saved and updated model events will not be fired for the updated models. This is because the models are never actually retrieved when issuing a mass update.`

For this reason we have blocked out the batch insert and mass update methods — they will throw an exception. This does not block direct queries, or `DB::insert`, etc, so you can set up mass insert or update using the base DB class if required. Just take care 😅

<a name="installation"></a>

## Installation

Install via composer

```bash
composer require custom-d/eloquent-model-encrypt
```

Publish the config & migration & run migration & generate Global Public/Private Keypair

```bash
php artisan vendor:publish --tag=eloquent-model-encrypt_config --tag=eloquent-model-encrypt_migration
php artisan migrate
php artisan asynckey
```

You may need to update the timestamp on the migration to run after your User Migration (if needed)

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

---

<a name="upgrade"></a>

## Upgrade from V1.x

In version 1 the migration was automatically run, in version 2 you need to publish the migration manually and trigger it to run when you want it to.
Publish the config & migration

```bash
php artisan vendor:publish --tag=eloquent-model-encrypt_config --tag=eloquent-model-encrypt_migration
```

---

<a name="usage"></a>

## Usage

<a name="keyproviders"></a>

### Key Providers

By default we ship a single Key Provider (GlobalKeyProvider) which makes use of the public private keypair generated, the private key is encrypted with the appkey.
You can add your own keypair providers by extending the `CustomD\EloquentModelEncrypt\Abstracts\KeyProvider` abstract.

```php
class MyKeyProvider extends KeyProvider {
    public static function getPublicKeysForTable($record, $extra = []): array
    {
        //return array of public keys to encrypt with / enpty array if not needed
        // eg [ rsaKeyID => publicKey]
        //if you want to use a file based key, pass the id as 0.
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {
        //return the first key that should have permission to decrypt or null if none.
    }
}
```

You will need to implement the logic to determine whether or not it should encrypt or decrypt based on the rules in the above methods.

<a name="migrations"></a>

### Migrations

For your migrations you can now replace the Illuminate versions of these with the CustomD versions:

```php
use CustomD\EloquentModelEncrypt\Migration\Blueprint;
use CustomD\EloquentModelEncrypt\Migration\Schema;
```

you can then define your columns as normal with the extra column types, these will calculate the required size to hold the encrypted string.

```php
$table->encryptedString('colname', '154');
$table->encryptedDate('datecolname');
$table->encryptedTimestamp('timestampcolname');
```

<a name="models"></a>

### Models

To enable encryption on a specific model you will need to add the following middleware and property to define which columns to encrypt

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

By default the GlobalKeyProvider is enabled. You can set which keys to encrypt and decrypt with by overwriting the following statc property

```php
protected static $keyProviders = [
        GlobalKeyProvider::class,
        YourKeyProvider::class
    ];
```

If an array is passed, it will get the public keys from each and add the encryption to each one,
for decryption will look for the first key available from there to decrypt for the current application / user / process.

<a name="engines"></a>

### Engines

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

-   **`public function encrypt(string $value): ?string`** - holds the encryption logic - value passed is the unencrypted database field value
-   **`public function decrypt(string $value): ?string`** - holds the decryption logic - value passed is the encrypted database field value
-   **`public function assignSynchronousKey([$synchronousKey = null]): void`** - allows you to set the synchronous key for encrytion - this is called when creating or retrieving a record.

<a name="artisan"></a>

### Artisan

The artisan command is usefull if you are wanting to encrypt an existing model. once you have configured your model run the following command:

```php
php artisan eme:encrypt:model "\App\Models\MyModel"
```

This will select all the records from that table and encrypt them.

<a name="suggestions"></a>
## Suggested Packages / Recipes

Here are a few packages that extend the usability of the encryption package

### [User Keystore](docs/UserKeyExample.md)
A basic user keystore example implementation

### [Hashed Search](https://github.com/customd/hashed-search)
This pacakge works to allow you to do a Blind Index search on your encrypted data, it works by using a one way hash to encrypt the data and does the same to search it.
the hash is configurable and can be set to either a double hash (hash1 ^ hashh2) or a iteration_count.

### [User Security Recovery](https://github.com/customd/user-security-recovery)
This package allows you to setup a secret question / anser or any such pattern to create an encrypted copy of the private key that a user can use to restore should they forget their password.

## Credits

-   [Custom D](https://git.customd.com/composert)
-   [All contributors](https://git.customd.com/composer/eloquent-model-encrypt/-/graphs/master)

[badge_laravel]: https://img.shields.io/badge/Laravel-5.8%20to%208-orange.svg?style=flat-square
[badge_issues]: https://img.shields.io/github/issues/ARCANEDEV/Support.svg?style=flat-square
