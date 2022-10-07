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
        Schema::create('access_controls', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->string('it_tool_name', 80);
            $table->string('remarks', 1024)->nullable();
            $table->string('acl_image_url', 512)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->datetime('review_date')->nullable();
            $table->string('review_remarks', 1024)->nullable();
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
        Schema::dropIfExists('access_controls');
    }
};
