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
        Schema::create('servers_partitions', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->decimal('hdd_partition');
            $table->decimal('hdd_used_size');
            $table->decimal('hdd_used_percentage');
            $table->decimal('hdd_free_size');
            $table->decimal('hdd_free_percentage');
            $table->decimal('hdd_total');
            $table->unsignedBigInteger('server_id');
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
        Schema::dropIfExists('servers_partitions');
    }
};
