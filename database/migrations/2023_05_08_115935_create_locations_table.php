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
        Schema::create('locations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('lat');
            $table->string('long');
            $table->string('location');
            $table->string('plot_numbers');
            $table->integer('no_of_lines');
            $table->string('no_of_plots_per_line');
            $table->string('rent_per_day');
            $table->string('rent_per_week');
            $table->string('rent_per_month');
            $table->string('rent_per_year');
            $table->string('photo');
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
        Schema::dropIfExists('locations');
    }
};
