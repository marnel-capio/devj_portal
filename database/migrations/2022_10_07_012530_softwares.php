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
        Schema::create('softwares', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedTinyInteger('approved_by')->nullable();
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->comment( '1: rejected, 2: approved, 3: pending for approval, 4: pending for update approval');
            $table->unsignedTinyInteger('type')->comment( '1: Productivity Tools, 2: Messaging/Collaboration, 3: Browser, 4: System Utilities, 5: Project Specific Softwares, 6: Phone Drivers');
            $table->string('software_name', 512);
            $table->string('remarks', 1024)->nullable();
            $table->string('reasons', 1024)->nullable();
            $table->json('update_data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('update_time')->nullable();
            $table->string('reject_code', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('softwares');
    }
};
