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
        Schema::table('projects_softwares', function (Blueprint $table) {
            $table->renameColumn('reasons', 'remarks');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_status');
            $table->dropColumn('delete_flag');
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
            $table->renameColumn('remarks', 'reasons');
            $table->unsignedBigInteger('approved_by')->nullable()->after('id');
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->after('approved_by')->comment( '1: rejected, 2: approved, 3: pending for approval, 4: pending for update approval');
            $table->unsignedTinyInteger('delete_flag')->default(0)->nullable()->after('remarks')->comment( '1: deleted , 0: not deleted');
        });  
    }
};
