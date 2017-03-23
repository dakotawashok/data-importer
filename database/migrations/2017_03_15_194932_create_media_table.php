<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('updated_at')->nullable();
            $table->string('content_type')->nullable();
            $table->string('tags')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_source')->nullable();
            $table->string('name')->nullable();
            $table->string('content_length');
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->datetime('created_at')->nullable();
            $table->boolean('master_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
