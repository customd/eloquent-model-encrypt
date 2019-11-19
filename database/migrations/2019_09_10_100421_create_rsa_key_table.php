<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration auto-generated by Sequel Pro Laravel Export (1.5.0).
 * @see https://github.com/cviebrock/sequel-pro-laravel-export
 */
class CreateRsaKeyTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $usersModel = '\\' . config('auth.providers.users.model');
        $tableUsers = (new $usersModel())->getTable();

        Schema::create('rsa_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::table($tableUsers, function (Blueprint $table) {
            $table->unsignedBigInteger('rsa_key_id')->nullable();
            $table->foreign('rsa_key_id')->references('id')->on('rsa_keys')->onDelete('CASCADE')->onUpdate('RESTRICT');
        });

        Schema::create('keystores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('table');
            $table->unsignedBigInteger('ref');
            $table->text('key');
            $table->unique(['ref', 'table'], 'ref');
        });

        Schema::create('keystore_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('keystore_id');
            $table->unsignedBigInteger('rsa_key_id');
            $table->text('key');
            $table->unique(['keystore_id', 'rsa_key_id'], 'keystore_ref');

            $table->foreign('keystore_id')->references('id')->on('keystores')->onDelete('CASCADE')->onUpdate('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $usersModel = config('auth.providers.users.model');
        $tableUsers = (new $usersModel())->getTable();

        Schema::table($tableUsers, function (Blueprint $table) {
            $table->dropColumn(['rsa_key_id']);
        });

        Schema::dropIfExists('rsa_keys');
        Schema::dropIfExists('keystores');
    }
}