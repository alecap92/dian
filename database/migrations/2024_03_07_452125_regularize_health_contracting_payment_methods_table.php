<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class RegularizeHealthContractingPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('health_contracting_payment_methods')){
            DB::table('health_contracting_payment_methods')->where('id', 1)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 2)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 3)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 4)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 5)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 6)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 7)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 8)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 9)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 10)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 11)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 12)->delete();
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '1', 'name' => 'Pago individual por caso / Conjunto integral de atenciones / Paquete / Canasta.', 'code' => '01']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '2', 'name' => 'Pago global prospectivo.', 'code' => '02']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '3', 'name' => 'Pago por capitación.', 'code' => '03']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '4', 'name' => 'Pago por evento.', 'code' => '04']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '5', 'name' => 'Otra modalidad (específica)', 'code' => '05']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('health_contracting_payment_methods')){
            DB::table('health_contracting_payment_methods')->where('id', 1)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 2)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 3)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 4)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 5)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 6)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 7)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 8)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 9)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 10)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 11)->delete();
            DB::table('health_contracting_payment_methods')->where('id', 12)->delete();
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '1', 'name' => 'Paquete/Canasta/Conjunto Integral en Salud', 'code' => '01']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '2', 'name' => 'Grupos Relacionados por Diagnóstico', 'code' => '02']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '3', 'name' => 'Integral por grupo de riesgo', 'code' => '03']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '4', 'name' => 'Pago por contacto de especialidad', 'code' => '04']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '5', 'name' => 'Pago por escenario de atención', 'code' => '05']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '6', 'name' => 'Pago por tipo de servicio', 'code' => '06']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '7', 'name' => 'Pago global prospectivo por episodio', 'code' => '07']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '8', 'name' => 'Pago global prospectivo por grupo de riesgo', 'code' => '08']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '9', 'name' => 'Pago global prospectivo por especialidad', 'code' => '09']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '10', 'name' => 'Pago global prospectivo por nivel de complejidad', 'code' => '10']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '11', 'name' => 'Capacitación', 'code' => '11']);
            DB::table('health_contracting_payment_methods')->updateOrInsert(['id' => '12', 'name' => 'Por servicio', 'code' => '12']);
        }
    }
}
