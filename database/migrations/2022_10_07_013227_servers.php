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
            $table->string('os', 1024);
            $table->string('cpu', 1024);
            $table->string('motherboard', 1024)->nullable();
            $table->string('memory', 1024);
            $table->string('hdd', 1024);
            $table->decimal('memory_used_size');
            $table->unsignedTinyInteger('memory_used_size_type')->comment( '1:B,2:KB,3:MB,4:GB,5:TB');
            $table->decimal('memory_used_percentage')->comment( 'Memory Used Size/Memory Total');
            $table->decimal('memory_free_size');
            $table->unsignedTinyInteger('memory_free_size_type')->comment( '1:B,2:KB,3:MB,4:GB,5:TB');
            $table->decimal('memory_free_percentage')->comment( 'Memory Free Size/Memory Total');
            $table->decimal('memory_total');
            $table->unsignedTinyInteger('memory_total_size_type')->comment( '1:B,2:KB,3:MB,4:GB,5:TB');
            $table->string('os_type', 80)->comment( '1:Linux, 2:Others');
            $table->decimal('other_os_percentage')->default(NULL)->nullable();
            $table->decimal('linux_us_percentage')->default(NULL)->nullable();
            $table->decimal('linux_ny_percentage')->default(NULL)->nullable();
            $table->decimal('linux_sy_percentage')->default(NULL)->nullable();
            $table->string('hdd_status', 20)->comment('1:Normal, 2:Stable, 3:Critical');
            $table->string('ram_status', 20)->comment('1:Normal, 2:Stable, 3:Critical');
            $table->string('cpu_status', 20)->comment('1:Normal, 2:Stable, 3:Critical');
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
