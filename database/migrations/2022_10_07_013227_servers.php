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
        Schema::create('servers', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->string('server_name', 80);
            $table->string('server_ip', 80);
            $table->string('function_role', 1024);
            $table->string('specification', 1024);
            $table->decimal('memory_used_size');
            $table->unsignedTinyInteger('memory_used_size_type')->comment( '1:B,2:KB,3:MB,4:GB,5:TB');
            $table->decimal('memory_used_percentage')->comment( 'Memory Used Size/Memory Total');
            $table->decimal('memory_free_size');
            $table->unsignedTinyInteger('memory_free_size_type')->comment( '1:B,2:KB,3:MB,4:GB,5:TB');
            $table->decimal('memory_free_percentage')->comment( 'Memory Free Size/Memory Total');
            $table->decimal('memory_used_total');
            $table->string('cpu_type', 80)->comment( '1:WINDOWS, 2:LINUX, 3:SYNOLOGY');
            $table->decimal('cpu_percentage')->default(NULL)->nullable();
            $table->string('hdd_status', 20);
            $table->string('ram_status', 20);
            $table->string('cpu_status', 20);
            $table->string('remarks', 1024)->nullable();
            $table->unsignedTinyInteger('status')->nullable()->comment( '1: ACTIVE , 0: INACTIVE');
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
        Schema::dropIfExists('servers');
    }
};
