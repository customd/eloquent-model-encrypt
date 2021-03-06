<?php

namespace CustomD\EloquentModelEncrypt;

use CustomD\EloquentModelEncrypt\Console\Commands\EncryptModel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected const CONFIG_PATH = __DIR__ . '/../config/eloquent-model-encrypt.php';

    protected const MIGRATIONS_PATH = __DIR__ . '/../database/migrations/';

    public function boot()
    {
          $this->publishes([
            self::CONFIG_PATH => config_path('eloquent-model-encrypt.php'),
        ], 'eloquent-model-encrypt_config');

         $this->publishes([
            self::MIGRATIONS_PATH => base_path('database/migrations/'),
        ], 'eloquent-model-encrypt_migration');

        if ($this->app->runningInConsole()) {
            $this->commands(EncryptModel::class);
        }

    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'eloquent-model-encrypt'
        );
    }
}
