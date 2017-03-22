<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMostUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('most_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_login')->nullable();
            $table->string('user_pass')->nullable();
            $table->string('user_nicename')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_url')->nullable();
            $table->dateTime('user_registered')->nullable();
            $table->string('user_activation_key')->nullable();
            $table->boolean('user_status')->nullable();
            $table->string('display_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('most_users');
    }
}
