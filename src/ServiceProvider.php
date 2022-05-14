<?php

namespace CustomD\EloquentModelEncrypt;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;
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

        Grammar::macro('typeEncrypted', function (Fluent $column) {
            $className = (new \ReflectionClass($this))->getShortName();

            if ($className === "MySqlGrammar") {
                return 'blob';
            }

            if ($className === "PostgresGrammar") {
                return 'bytea';
            }

            if ($className === "SQLiteGrammar") {
                return 'blob';
            }

            if ($className === "SqlServerGrammar") {
                return 'varbinary(max)';
            }

            throw new UnknownGrammerException();
        });

        Blueprint::macro('encrypted', function ($column): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->addColumn('encrypted', $column);
        });
    }
}
