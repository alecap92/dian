<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class AddEquivalentDocumentsRecordsToTypeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('type_documents')){
            DB::table('type_documents')->updateOrInsert(['id' => '15', 'name' => 'Documento equivalente electrónico del tiquete de máquina registradora con sistema P.O.S.', 'code' => '20', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'pos', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '16', 'name' => 'Boleta de ingreso a cine', 'code' => '25', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'cin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '17', 'name' => 'Boleta de ingreso a espectáculos públicos', 'code' => '27', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'esp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '18', 'name' => 'Documento en juegos localizados y no localizados - relación diaria de control de ventas', 'code' => '30', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'jue', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '19', 'name' => 'Tiquete de transporte de pasajeros Terrestre', 'code' => '35', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'ttr', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '20', 'name' => 'Documento expedido para el cobro de peajes', 'code' => '40', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'pjs', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '21', 'name' => 'Extracto Expedido por Sociedades Financieras y Fondos', 'code' => '45', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'ext', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '22', 'name' => 'Tiquete de Billete de Transporte Aéreo de Pasajeros', 'code' => '50', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'tae', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '23', 'name' => 'Documento de Operación de Bolsa de Valores, Agropecuaria y de Otros Comodities', 'code' => '55', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'bls', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '24', 'name' => 'Documento Expedido para los Servicios Públicos y Domiciliarios', 'code' => '60', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'srv', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '25', 'name' => 'Nota de Ajuste de tipo debito al Documento Equivalente', 'code' => '93', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'ndq', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            DB::table('type_documents')->updateOrInsert(['id' => '26', 'name' => 'Nota de Ajuste de tipo crédito al Documento Equivalente', 'code' => '94', 'cufe_algorithm' => 'CUDE-SHA384', 'prefix' => 'ncq', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('type_documents')){
            DB::table('type_documents')->where('id', 15)->delete();
            DB::table('type_documents')->where('id', 16)->delete();
            DB::table('type_documents')->where('id', 17)->delete();
            DB::table('type_documents')->where('id', 18)->delete();
            DB::table('type_documents')->where('id', 19)->delete();
            DB::table('type_documents')->where('id', 20)->delete();
            DB::table('type_documents')->where('id', 21)->delete();
            DB::table('type_documents')->where('id', 22)->delete();
            DB::table('type_documents')->where('id', 23)->delete();
            DB::table('type_documents')->where('id', 24)->delete();
            DB::table('type_documents')->where('id', 25)->delete();
            DB::table('type_documents')->where('id', 26)->delete();
        }
    }
}
