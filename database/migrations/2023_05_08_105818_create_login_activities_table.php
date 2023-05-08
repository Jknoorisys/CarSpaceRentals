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
        Schema::create('login_activities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->enum('user_type',['user','dealer','admin']);
            $table->string('device_id');
            $table->string('ip_address');
            $table->string('login_date');
            $table->string('login_time');
            $table->string('logout_time');
            $table->string('duration');
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
        Schema::dropIfExists('login_activities');
    }
};
