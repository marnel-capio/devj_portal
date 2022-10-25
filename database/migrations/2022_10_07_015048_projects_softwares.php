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
        Schema::create('projects_softwares', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->comment( '1: rejected, 2: approved, 3: pending for approval, 4: pending for update approval');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('software_id');
            $table->string('reasons', 1024)->nullable();
            $table->unsignedTinyInteger('delete_flag')->default(0)->nullable()->comment( '1: deleted , 0: not deleted');
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
        Schema::dropIfExists('projects_softwares');
    }
};
