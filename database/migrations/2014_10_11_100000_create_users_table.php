<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code')->nullable();
            $table->string('email',70)->unique();
            $table->string('password');
            $table->string('alias')->unique();
            $table->string('name',30);
            $table->string('last_name',30);
            $table->string('token_email')->nullable();
            $table->timestamp('date_email_verified')->nullable();
            $table->string('email_token_validation')->nullable();;
            $table->enum('role',['Administrador','Cliente']);

            $table->enum('document_type',['D.N.I','Licencia de conducir','Pasaporte','Visa o residencia permanente'])->nullable();
            $table->enum('nationality',['Extranjera','Natural'])->nullable();
            $table->string('document_number')->nullable();

            $table->enum('data_complete',[1,0])->default(0);
            $table->string('subscription_date')->nullable();
            $table->string('departure_date')->nullable();
            // Preguntar por esto luego
            $table->date('date_of_birth')->nullable();
            $table->string('age')->nullable();
            // aditional
            $table->string('whatsapp')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('country')->nullable();

            $table->foreign('state_id')->references('id')->on('states');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('state')->nullable();

            $table->foreign('city_id')->references('id')->on('cities');
            $table->string('city')->nullable();

            $table->string('municipality')->nullable();
            $table->string('address')->nullable();

            // Metodos de pago
            $table->string('paypal')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // $table->string('departamento')->nullable();
            // $table->string('calificacion_1')->nullable();
            // $table->string('calificacion_2')->nullable();
            // $table->string('calificacion_3')->nullable();
            // $table->string('activo_o_propiedad')->nullable();
            // $table->string('nro_de_cuenta')->nullable();
            // $table->string('tipo_de_cuenta')->nullable();
            // $table->string('nombre_banco')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
