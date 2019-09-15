# Eloquent Model Encrypt

https://git.customd.com/composer/eloquent-model-encrypt/badges/master/pipeline.svg

[![Build Status](https://travis-ci.org/custom-d/eloquent-model-encrypt.svg?branch=master)](https://travis-ci.org/custom-d/eloquent-model-encrypt)
[![styleci](https://styleci.io/repos/CHANGEME/shield)](https://styleci.io/repos/CHANGEME)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/custom-d/eloquent-model-encrypt/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/custom-d/eloquent-model-encrypt/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/CHANGEME/mini.png)](https://insight.sensiolabs.com/projects/CHANGEME)
[![Coverage Status](https://coveralls.io/repos/github/custom-d/eloquent-model-encrypt/badge.svg?branch=master)](https://coveralls.io/github/custom-d/eloquent-model-encrypt?branch=master)

[![Packagist](https://img.shields.io/packagist/v/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://poser.pugx.org/custom-d/eloquent-model-encrypt/d/total.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)
[![Packagist](https://img.shields.io/packagist/l/custom-d/eloquent-model-encrypt.svg)](https://packagist.org/packages/custom-d/eloquent-model-encrypt)

Package description: CHANGE ME

@customD version


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

### Publish Configuration File

```bash
php artisan vendor:publish --provider="CustomD\EloquentModelEncrypt\ServiceProvider" --tag="config"
```



## Usage
In your migrations you can now replace the Illuminate versions of these with the CustomD versions:
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

## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## Credits

- [](https://github.com/custom-d/eloquent-model-encrypt)
- [All contributors](https://github.com/custom-d/eloquent-model-encrypt/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).
