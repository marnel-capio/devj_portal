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
        // convert Column data type of mail_history:body due to long texts being stored
        Schema::table('mail_history', function(Blueprint $table){
            $table->text('body')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Last update from: 2023_01_25_155356_mail_history_table_increase_max_of_body.php
        $table->string('body', 2048)->change();
    }
};
