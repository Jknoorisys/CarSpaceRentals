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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->longText('plots_id');
            $table->longText('cars_id');
            $table->string('location_id');
            $table->string('dealer_id');
            $table->string('notif_token');
            $table->string('pay_token');
            $table->string('payment_id');
            $table->string('payment_method');
            // $table->string('parkIn_date');
            // $table->string('parkOut_date');
            $table->string('amount');
            $table->enum('payment_for',['plot','car'])->default('plot');
            $table->enum('status',['paid','unpaid'])->default('unpaid');
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
        Schema::dropIfExists('payment_transaction');
    }
};
