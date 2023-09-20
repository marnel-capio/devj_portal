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
            $table->unsignedTinyInteger('passport_status')->comment('1:With Passport, 2:With Scheduled Appointment, 3:No Passport No Appointment, 4:Waiting for Delivery')->nullable()->after('reject_code');
            $table->string('date_of_delivery', 80)->nullable()->after('no_appointment_reason');

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
            $table->dropColumn('passport_status');
            $table->dropColumn('date_of_delivery');
            //
        });
    }
};
