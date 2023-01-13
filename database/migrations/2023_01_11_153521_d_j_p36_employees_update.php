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
        Schema::table('employees', function(Blueprint $table){
            $table->string('other_contact_number', 80)->nullable()->change();
        });
        Schema::table('employees', function(Blueprint $table){
            $table->renameColumn('other_contact_number', 'other_contact_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function(Blueprint $table){
            $table->string('other_contact_info', 20)->nullable()->change();
        });
        Schema::table('employees', function(Blueprint $table){
            $table->renameColumn('other_contact_info', 'other_contact_number');
        });
    }
};
