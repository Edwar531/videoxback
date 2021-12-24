<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_banks', function (Blueprint $table) {
            $table->id();
            $table->string("country");
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->nullable();
            $table->string("name_bank");
            $table->string("number");
            $table->enum("type",["Ahorro","Corriente"]);
            $table->string("owner");
            $table->string("identification_owner");
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_banks');
    }
}
