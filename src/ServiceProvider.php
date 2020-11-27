<?php

namespace CustomD\EloquentModelEncrypt;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected const CONFIG_PATH = __DIR__ . '/../config/eloquent-model-encrypt.php';

    protected const MIGRATIONS_PATH = __DIR__ . '/../database/migrations/';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('eloquent-model-encrypt.php'),
        ], 'config');

        $this->publishes([
            self::MIGRATIONS_PATH => database_path('migrations')
        ], 'migrations');

        $this->loadMigrationsFrom(self::MIGRATIONS_PATH);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'eloquent-model-encrypt'
        );
    }
}
