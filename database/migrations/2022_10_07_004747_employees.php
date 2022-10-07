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
        Schema::create('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment( 'Auto increment');
            $table->unsignedTinyInteger('approved_status')->default(2)->nullable()->comment( '0: rejected, 1: approved, 2: pending for approval');
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('middle_name', 80);
            $table->string('birthdate', 80)->nullable();
            $table->string('gender', 80)->nullable();
            $table->string('cellphone_number', 20);
            $table->string('other_contact_number', 20)->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->unsignedTinyInteger('roles')->default(3)->comment( '1:Admin, 2:Manager, 3:Engineer');
            $table->string('email', 80)->comment( '*login, *awsys email');
            $table->string('password', 512);
            $table->string('current_address_street', 80)->nullable()->comment( '*current address');
            $table->string('current_address_city', 80)->nullable()->comment( '*current address');
            $table->string('current_address_province', 80)->nullable()->comment( '*current address');
            $table->string('current_address_postalcode', 80)->nullable()->comment( '*current address');
            $table->string('permanent_address_street', 80)->nullable();
            $table->string('permanent_address_city', 80)->nullable();
            $table->string('permanent_address_province', 80)->nullable();
            $table->string('permanent_address_postalcode', 80)->nullable();
            $table->unsignedTinyInteger('server_manage_flag')->nullable();
            $table->unsignedTinyInteger('active_status')->default(1)->nullable()->comment( '1: Active, 0: Inactive');
            $table->string('reasons', 1024)->nullable();
            $table->json('update_data')->nullable();
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
        Schema::dropIfExists('employees');
    }
};
