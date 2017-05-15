<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOldOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_offers', function (Blueprint $table) {
            $table->increments('coupon_id');
            $table->string('coupon_title')->nullable();
            $table->string('coupon_slug')->nullable();
            $table->string('post_modified')->nullable();

            //offer_settings
            $table->text('offer_highlight')->nullable();
            $table->text('coupon_offer')->nullable();
            $table->string('company_name')->nullable();
            //dates
            $table->string('date_options')->nullable(); //unnecessary
            $table->string('valid_start')->nullable();
            $table->string('valid_end')->nullable();
            //-dates
            $table->string('company_logo')->nullable();
            $table->string('featured_image')->nullable();
            $table->text('coupon_images')->nullable();
            $table->text('marks')->nullable();
            //-offer_settings

            $table->text('terms')->nullable();
            $table->text('locations')->nullable();
            $table->string('company_email')->nullable();
            $table->string('merchant_phone_prefix')->nullable();
            $table->string('merchant_phone_number')->nullable();
            $table->string('company_website')->nullable();
            $table->text('offer_destination')->nullable();
            $table->text('offer_tags')->nullable();

            //geo_location
            $table->string('address')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            //-geo_location

            $table->text('geolocation_v2')->nullable();
            $table->string('price_range')->nullable();
            $table->string('small_business')->nullable();
            $table->string('cuisine_type')->nullable();

            $table->dateTime('new_offer_created_at')->nullable();
            $table->dateTime('new_offer_published_at')->nullable();
            $table->string('new_offer_created_by')->nullable();
            $table->string('new_offer_updated_by')->nullable();
            $table->string('new_offer_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('old_offers');
    }
}
