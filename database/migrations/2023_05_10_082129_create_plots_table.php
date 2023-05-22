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
            $table->uuid('id')->primary();
            $table->string('line_id');
            $table->string('location_id');
            $table->string('plot_name');
            $table->string('plot_direction');
            $table->enum('plot_position',['left','right'])->default('left');
            $table->float('single_daily');
            $table->float('single_weekly');
            $table->float('single_monthly');
            $table->float('five_daily');
            $table->float('five_weekly');
            $table->float('five_monthly');
            $table->float('ten_daily');
            $table->float('ten_weekly');
            $table->float('ten_monthly');
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
        Schema::dropIfExists('plots');
    }
};
