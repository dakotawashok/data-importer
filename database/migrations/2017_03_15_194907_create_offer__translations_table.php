<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_translation', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content')->nullable();
            $table->text('location_notes')->nullable();
            $table->string('lang')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('brief')->nullable();
            $table->text('summary')->nullable();
            $table->integer('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_translation');
    }
}
