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
        Schema::create('laptops', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->comment( '1: rejected, 2: approved, 3: pending for approval');
            $table->string('peza_form_number', 20)->nullable();
            $table->string('peza_permit_number', 20)->nullable();
            $table->string('tag_number', 80)->nullable();
            $table->string('laptop_make', 80)->nullable();
            $table->string('laptop_model', 80)->nullable();
            $table->string('laptop_cpu', 80)->nullable();
            $table->string('laptop_clock_speed', 80)->nullable();
            $table->unsignedTinyInteger('laptop_ram')->nullable();
            $table->string('remarks', 1024)->nullable();
            $table->json('update_data')->nullable();
            $table->string('reasons', 1024)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment( '1: active , 0: inactive');
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
        Schema::dropIfExists('laptops');
    }
};
