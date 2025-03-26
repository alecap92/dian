<?php

use App\PayrollTypeDocumentIdentification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateCodeColumnToPayrollTypeDocumentIdentificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        PayrollTypeDocumentIdentification::where('id', 1)->update(['name' => 'Registro civil']);
        PayrollTypeDocumentIdentification::where('id', 1)->update(['code' => '11']);
        PayrollTypeDocumentIdentification::where('id', 2)->update(['name' => 'Tarjeta de identidad']);
        PayrollTypeDocumentIdentification::where('id', 2)->update(['code' => '12']);
        PayrollTypeDocumentIdentification::where('id', 3)->update(['name' => 'Cédula de ciudadanía']);
        PayrollTypeDocumentIdentification::where('id', 3)->update(['code' => '13']);
        PayrollTypeDocumentIdentification::where('id', 4)->update(['name' => 'Tarjeta de extranjería']);
        PayrollTypeDocumentIdentification::where('id', 4)->update(['code' => '21']);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '5', 'name' => 'Cédula de extranjería', 'code' => '22', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '6', 'name' => 'NIT', 'code' => '31', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '7', 'name' => 'Pasaporte', 'code' => '41', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '8', 'name' => 'Documento de identificación extranjero', 'code' => '42', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '9', 'name' => 'PEP', 'code' => '47', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '10', 'name' => 'NIT de otro país', 'code' => '50', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('payroll_type_document_identifications')->updateOrInsert(['id' => '11', 'name' => 'NUIP *', 'code' => '91', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
}
