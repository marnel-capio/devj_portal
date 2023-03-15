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
         //add approve_time in the softwares table
        Schema::table('softwares', function (Blueprint $table) {
            $table->dateTime('approve_time')->nullable();
            $table->renameColumn('type', 'software_type_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('softwares', function (Blueprint $table) {
            $table->dropColumn('approve_time');
            $table->renameColumn('software_type_id', 'type');
        }); 

    }
};
