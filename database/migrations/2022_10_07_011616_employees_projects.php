<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees_projects', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->comment( '1: rejected, 2: approved, 3: pending for approval');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->unsignedTinyInteger('project_role_type')->comment( '1: Team Lead, 2: Programmer, 3:QA');
            $table->string('remarks', 1024)->nullable();
            $table->unsignedTinyInteger('onsite_flag')->default(0)->nullable()->comment( '1: onsite , 0: office');
            $table->json('update_data')->nullable();
            $table->string('reasons', 1024)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('update_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees_projects');
    }
};
