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
            $table->decimal('memory_used_percentage')->comment( 'Memory Used Size/Memory Total');
            $table->decimal('memory_free_size');
            $table->decimal('memory_free_percentage')->comment( 'Memory Free Size/Memory Total');
            $table->decimal('memory_used_total');
            $table->string('cpu_windows', 80)->default(NULL)->nullable();
            $table->decimal('cpu_linux_us_percentage')->default(NULL)->nullable();
            $table->decimal('cpu_linux_ni_percentage')->default(NULL)->nullable();
            $table->decimal('cpu_linux_sy_percentage')->default(NULL)->nullable();
            $table->string('hdd_status', 20)->nullable()->comment( '※1');
            $table->string('ram_status', 20)->nullable()->comment( '※1');
            $table->string('cpu_status', 20)->nullable()->comment( '※1');
            $table->string('remarks', 1024)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('reviewer_comment', 1024)->nullable();
            $table->unsignedTinyInteger('status')->nullable()->comment( '1: ACTIVE , 0: INACTIVE');
            $table->unsignedTinyInteger('delete_flag')->default(0)->nullable()->comment( '1: deleted, 0: not deleted');
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
