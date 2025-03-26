<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentPayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_payrolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('identification_number')->nullable();
            $table->unsignedInteger('state_document_id')->default(1);
            $table->unsignedBigInteger('type_document_id');
            $table->foreign('type_document_id')->references('id')->on('type_documents');
            $table->char('prefix')->nullable();
            $table->string('consecutive');
            $table->string('xml')->nullable();
            $table->string('pdf')->nullable();
            $table->string('cune')->nullable();
            $table->string('employee_id')->nullable();
            $table->dateTime('date_issue');
            $table->decimal('accrued_total', 18, 2);
            $table->decimal('deductions_total', 18, 2);
            $table->decimal('total_payroll', 18, 2);
            $table->json('request_api')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('document_payrolls');
    }
}
