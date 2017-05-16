<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_website')->nullable();
            $table->string('merchant_ids')->nullable();
            $table->string('mcc_ids')->nullable();
            $table->string('merchant_phone_prefix')->nullable();
            $table->string('merchant_phone_number')->nullable();
            $table->string('merchant_email')->nullable();
            $table->string('merchant_name')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('created_by')->nullable();
            $table->string('cuisine_type')->nullable();
            $table->integer('acceptance_level_id')->nullable();
            $table->string('status')->nullable();
            $table->string('barcode' , 2000)->nullable();
            $table->string('category_id')->nullable();
            $table->string('merchant_logo' , 2000)->nullable();
            $table->string('partner_id')->nullable();
            $table->boolean('sma')->nullable();
            $table->boolean('privileges_only')->nullable()->default(1);
            $table->string('qrcode' , 2000)->nullable();
            $table->integer('price_range')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('terms_accepted')->nullable();
            $table->boolean('small_business')->nullable();
            $table->boolean('active')->nullable();
            $table->string('primary_image' , 2000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer');
    }
}
