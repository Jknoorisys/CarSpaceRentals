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
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('plot_id');
            $table->string('line_id');
            $table->string('location_id');
            $table->string('dealer_id');
            $table->string('car_id');
            $table->string('park_in_date');
            $table->string('park_out_date');
            $table->enum('duration_type',['day','week','month','year']);
            $table->string('duration');
            $table->string('rent');
            $table->enum('status',['active','expired','upcoming'])->default('upcoming');
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
        Schema::dropIfExists('bookings');
    }
};
