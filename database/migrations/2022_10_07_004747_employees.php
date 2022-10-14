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
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedTinyInteger('approved_status')->default(3)->nullable()->comment( '1: rejected, 2: approved, 3: pending for approval');
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('middle_name', 80);
            $table->string('birthdate', 80)->nullable();
            $table->string('gender', 80)->nullable();
            $table->string('cellphone_number', 20);
            $table->string('other_contact_number', 20)->nullable();
            $table->unsignedTinyInteger('position')->comment( '1:Junior Research and Development, 2:Assistant Reasearch and Development, 3:Senior Assistant Research and Development, 4:Assiociate Reasearch and Development, 5:Senior Associate Research and Development, 6:Supervisor, 7:Adviser, 8:Assistant Manager, 9:Manager');
            $table->unsignedTinyInteger('roles')->default(3)->comment( '1:Admin, 2:Manager, 3:Engineer');
            $table->string('email', 80);
            $table->string('password', 512);
            $table->string('current_address_street', 80)->nullable();
            $table->string('current_address_city', 80)->nullable();
            $table->string('current_address_province', 80)->nullable();
            $table->string('current_address_postalcode', 80)->nullable();
            $table->string('permanent_address_street', 80);
            $table->string('permanent_address_city', 80)->nullable();
            $table->string('permanent_address_province', 80)->nullable();
            $table->string('permanent_address_postalcode', 80);
            $table->unsignedTinyInteger('server_manage_flag')->default(0)->nullable();
            $table->unsignedTinyInteger('active_status')->default(1)->nullable()->comment( '1: Active, 0: Inactive');
            $table->string('reasons', 1024)->nullable();
            $table->json('update_data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->rememberToken();
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
