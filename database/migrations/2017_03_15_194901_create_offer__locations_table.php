<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_location', function (Blueprint $table) {
            $table->increments('id');
            $table->text('notes')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->integer('offer_id');
            $table->double('lat',11,8)->nullable();
            $table->double('lng',11,8)->nullable();
            $table->string('type')->nullable();
            $table->boolean('countrywide')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_location');
    }
}
