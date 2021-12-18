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
            $table->string('codigo_unico')->nullable();
            $table->string('correo',70)->unique();
            $table->string('clave');
            $table->string('alias')->unique();
            $table->string('nombres',30);
            $table->string('apellidos',30);
            $table->string('nombre_completo',60);
            $table->string('token_correo')->nullable();
            $table->timestamp('correo_verificado_en')->nullable();
            $table->string('correo_token_validacion')->nullable();;
            $table->enum('role',['Administrador','Cliente']);
            $table->enum('estatus',['natural','juridico','extranjero','placa','sin documento'])->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('tipo_de_dni')->nullable();
            $table->string('numero_de_dni')->nullable();
            $table->string('telefono')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('pais')->nullable();
            $table->string('estado_provincia')->nullable();

            $table->string('ciudad')->nullable();
            $table->enum('datos_completados',[1,0])->default(0);
            $table->string('direccion')->nullable();
            $table->string('fecha_de_alta')->nullable();
            $table->string('fecha_de_baja')->nullable();
            // Preguntar por esto luego
            $table->date('fecha_de_nacimiento')->nullable();
            $table->string('edad')->nullable();
            $table->string('calificacion_1')->nullable();
            $table->string('calificacion_2')->nullable();
            $table->string('calificacion_3')->nullable();
            $table->string('activo_o_propiedad')->nullable();
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            // aditional
            $table->string('cuenta_paypal')->nullable();
            $table->string('nro_de_cuenta')->nullable();
            $table->string('tipo_de_cuenta')->nullable();
            $table->string('nombre_banco')->nullable();
            // Metodos de pago
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
