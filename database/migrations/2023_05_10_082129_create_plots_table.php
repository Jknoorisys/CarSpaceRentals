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
        Schema::create('plots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('plot_number');
            $table->string('location_id');
            // $table->string('dealer_id');
            // $table->string('car_id');
            // $table->string('park_in_date');
            // $table->string('park_out_date');
            $table->enum('status',['available','booked'])->default('available');
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
        Schema::dropIfExists('plots');
    }
};
