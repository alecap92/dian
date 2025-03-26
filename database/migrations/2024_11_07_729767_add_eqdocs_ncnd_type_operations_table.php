<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\TypeOperation;

class AddEqDocsNCNDTypeOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sdcn = TypeOperation::updateOrCreate(['id' => 30, 'name' => 'Nota de ajuste al documento Equivalente Con referencia a un Doc Equivalente Boleta de ingreso a cine', 'code' => 25]);
        $sdcn->id = 30;
        $sdcn->save();
        $sdcn = TypeOperation::updateOrCreate(['id' => 31, 'name' => 'Nota de ajuste al documento Equivalente Con referencia a un Doc Equivalente Tiquete de transporte de pasajeros Terrestre', 'code' => 35]);
        $sdcn->id = 31;
        $sdcn->save();
        $sdcn = TypeOperation::updateOrCreate(['id' => 32, 'name' => 'Nota de ajuste al documento Equivalente Con referencia a un Doc Equivalente Expedido para los Servicios Públicos y Domiciliarios ', 'code' => 60]);
        $sdcn->id = 32;
        $sdcn->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sdcn = TypeOperation::where('id', 30)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
        $sdcn = TypeOperation::where('id', 31)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
        $sdcn = TypeOperation::where('id', 32)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
    }
}
