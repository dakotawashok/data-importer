<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->dateTime('last_login_date')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_sys_admin')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('phone')->nullable();
            $table->string('security_question')->nullable();
            $table->text('security_answer')->nullable();
            $table->string('confirm_code')->nullable();
            $table->integer('default_app_id')->nullable();
            $table->text('remember_token')->nullable();
            $table->string('oauth_provider')->nullable();
            $table->string('created_date')->nullable();
            $table->string('last_modified_date')->nullable();
            $table->string('created_by_id')->nullable();
            $table->string('last_modified_by_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
