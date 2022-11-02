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
        Schema::create('mail_history', function (Blueprint $table) {
            $table->id();
            $table->string('module', 80);
            $table->string('subject', 80);
            $table->string('to', 80);
            $table->string('to_name', 80);
            $table->string('from', 80);
            $table->string('from_name', 80);
            $table->string('body', 1024);
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
        Schema::dropIfExists('mail_history');
        
    }
};
