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
        //
        Schema::table('projects_softwares', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_deleted')->default(0)->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_softwares', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });   
    }
};
