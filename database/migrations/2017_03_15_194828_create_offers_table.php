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
            $table->string('acceptance_level')->nullable();
            $table->string('status')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category_id')->nullable();
            $table->string('merchant_logo')->nullable();
            $table->string('partner_id')->nullable();
            $table->string('sma_id')->nullable();
            $table->string('qrcode')->nullable();
            $table->integer('price_range')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('terms_accepted')->nullable();
            $table->boolean('small_business')->nullable();
            $table->boolean('active')->nullable();
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
