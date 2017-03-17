<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_translation', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content')->nullable();
            $table->string('lang')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('brief')->nullable();
            $table->string('summary')->nullable();
            $table->integer('collection_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collection_translation');
    }
}
