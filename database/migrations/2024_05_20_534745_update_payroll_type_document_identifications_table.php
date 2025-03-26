<?php

use App\PayrollTypeDocumentIdentification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdatePayrollTypeDocumentIdentificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        PayrollTypeDocumentIdentification::destroy(9);
        PayrollTypeDocumentIdentification::create(['id' => 9, 'name' => 'PEP (Permiso Especial de Permanencia)', 'code' => '47']);
        PayrollTypeDocumentIdentification::where('code', '47')->update(['id' => 9, 'name' => 'PEP (Permiso Especial de Permanencia)', 'code' => '47']);
        PayrollTypeDocumentIdentification::create(['id' => 12, 'name' => 'PPT (Permiso Protección Temporal)', 'code' => '48']);
        PayrollTypeDocumentIdentification::where('code', '48')->update(['id' => 12, 'name' => 'PPT (Permiso Protección Temporal)', 'code' => '48']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        PayrollTypeDocumentIdentification::where('code', '47')->update(['id' => 9, 'name' => 'PEP', 'code' => '47']);
        PayrollTypeDocumentIdentification::destroy(12);
	}
}
