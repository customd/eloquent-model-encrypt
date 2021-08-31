<?php

namespace CustomD\EloquentModelEncrypt;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use CustomD\EloquentModelEncrypt\Console\Commands\EncryptModel;
use CustomD\EloquentModelEncrypt\Exceptions\UnknownGrammerException;

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

        $this->registerMigrationMacros();
    }

    protected function registerMigrationMacros()
    {

        Grammar::macro('typeEncrypted', function (Fluent $column) {
            $className = (new \ReflectionClass($this))->getShortName();

            // TODO - V3 upgrade to match statment php 8
            switch ($classname) {
                case "SQLiteGrammar":
                case "MySqlGrammar":
                    return 'blob';
                case "PostgresGrammar":
                    return 'bytea';
                case "SqlServerGrammar":
                    return 'varbinary(max)';
                default:
                    throw new UnknownGrammerException();
            }
        });

        Blueprint::macro('encrypted', function ($column): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->addColumn('encrypted', $column);
        });
    }
}
