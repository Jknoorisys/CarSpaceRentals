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
        Schema::create('location_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('location_id');
            $table->string('line_name');
            $table->string('no_of_plots_in_left');
            $table->string('no_of_plots_in_right');
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
