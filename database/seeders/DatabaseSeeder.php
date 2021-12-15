<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        DB::table('users')->insert([
            'alias'=>'Admin',
            'nombres'=>'Edwar',
            'apellidos'=>'Villavicencio',
            'nombre_completo'=>'Edwar Villavicencio',
            'correo'=>'admin@gmail.com',
            'correo_verificado_en'=>'2021-12-01 19:29:59',
            'clave'=>bcrypt('12345678'),
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Lesbianas',
            'slug'=>'lesbianas',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Tetas grandes',
            'slug'=>'tetas-grandes',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Milfs',
            'slug'=>'milfs',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Casero',
            'slug'=>'casero',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Profesional',
            'slug'=>'profesional',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Culonas',
            'slug'=>'culonas',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Morenas',
            'slug'=>'morenas',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Rubias',
            'slug'=>'rubias',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Sola',
            'slug'=>'sola',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Grupal',
            'slug'=>'grupal',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Madura',
            'slug'=>'madura',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Colegiala',
            'slug'=>'colegiala',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Jovencita',
            'slug'=>'jovencita',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Hace paja',
            'slug'=>'hace-paja',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'masaje',
            'slug'=>'masaje',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Gordita',
            'slug'=>'gordita',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Enfermera',
            'slug'=>'enfermera',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'En el trabajo',
            'slug'=>'en-el-trabajo',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'Ama de casa',
            'slug'=>'ama-de-casa',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'En la cocina',
            'slug'=>'en-la-cocina',
        ]);

        DB::table('tags')->insert([
            'nombre'=>'En el baño',
            'slug'=>'en-el-baño',
        ]);

    }
}
