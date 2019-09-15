<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKeystoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('table_keystores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('table');
            $table->unsignedBigInteger('ref');
            $table->unsignedBigInteger('rsa_keystore_id');
            $table->string('key', 400);
            $table->unique(['ref', 'rsa_keystore_id', 'table'], 'ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('table_keystores');
    }
}
