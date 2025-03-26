<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\RegularizeDataHelper;


class RegularizeDataToTypeWorkers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        $table_columns = [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ];

        RegularizeDataHelper::regularizeDataFromTable('type_workers', $table_columns);
        
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
