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
        Schema::create('featured_cars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dealer_id');
            $table->string('car_id');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('featured_days');
            $table->enum('status',['upcoming','unfeatured','featured'])->default('upcoming');
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
        Schema::dropIfExists('featured_cars');
    }
};
