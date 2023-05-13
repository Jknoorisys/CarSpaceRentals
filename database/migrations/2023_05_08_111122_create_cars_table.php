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
        Schema::create('cars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dealer_id');
            $table->enum('condition',['New','Old']);
            $table->string('name');
            $table->string('brand');
            $table->year('year_of_registration');
            $table->string('milage');
            $table->year('year_of_manufacturing');
            $table->enum('type',['manual','automatic']);
            $table->enum('fuel_type',['diesel','petrol','gas']);
            $table->integer('no_of_seats');
            $table->string('ownership');
            $table->string('insurance_validity');
            $table->string('engin');
            $table->string('kms_driven');
            $table->string('top_speed');
            $table->string('color');
            $table->string('price');
            $table->string('description');
            $table->enum('is_featured',['no','yes'])->default('no');
            $table->enum('is_assgined',['no','yes'])->default('no');
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cars');
    }
};
