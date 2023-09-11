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
            $table->string('passport_number', 80)->nullable()->after('reject_code');
            $table->string('date_of_issue', 80)->nullable()->after('passport_number');
            $table->string('issuing_authority', 80)->nullable()->after('date_of_issue');
            $table->unsignedTinyInteger('passport_type')->comment('1:Ordinary, 2:Official, 3:Diplomatic')->nullable()->after('issuing_authority');
            $table->string('passport_expiration_date', 80)->nullable()->after('passport_type');
            $table->string('place_of_issue', 80)->nullable()->after('passport_expiration_date');
            $table->string('date_of_appointment', 80)->nullable()->after('place_of_issue');
            $table->string('no_appointment_reason', 80)->nullable()->after('date_of_appointment');
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
            $table->dropColumn('passport_number');
            $table->dropColumn('date_of_issue');
            $table->dropColumn('issuing_authority');
            $table->dropColumn('passport_type');
            $table->dropColumn('passport_expiration_date');
            $table->dropColumn('place_of_issue');
            $table->dropColumn('date_of_appointment');
            $table->dropColumn('no_appointment_reason');
        });
    }
};
