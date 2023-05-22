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
        Schema::create('plot_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('location_id');
            $table->string('line_name');
            $table->string('no_of_plots_in_left');
            $table->string('no_of_plots_in_right');
            $table->string('default_single_daily');
            $table->string('default_single_weekly');
            $table->string('default_single_monthly');
            $table->string('default_five_daily');
            $table->string('default_five_weekly');
            $table->string('default_five_monthly');
            $table->string('default_ten_daily');
            $table->string('default_ten_weekly');
            $table->string('default_ten_monthly');
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
        Schema::dropIfExists('location_lines');
    }
};
