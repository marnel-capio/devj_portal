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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedTinyInteger('bu_transfer_flag')->default(0)->nullable()->after('update_data')->comment( '1: transferred, 0: not transferred');
            $table->string('bu_transfer_assignment', 80)->nullable()->after('bu_transfer_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('bu_transfer_flag');
            $table->dropColumn('bu_transfer_assignment');
        }); 
    }
};
