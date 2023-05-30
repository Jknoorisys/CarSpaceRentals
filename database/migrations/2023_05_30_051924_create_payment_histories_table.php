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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('payment_method',['stripe','orange','mtn','manual'])->default('manual');
            $table->enum('payment_for',['plot','car'])->default('plot');
            $table->string('dealer_id');
            $table->string('car_id');
            $table->string('location_id');
            $table->string('line_id');
            $table->longText('plot_ids');
            $table->integer('no_of_plots');
            $table->string('duration');
            $table->enum('duration_type',['day','week','month','year']);
            $table->string('park_in_date');
            $table->string('park_out_date');
            $table->float('rent');
            $table->string('session_id');
            $table->string('payment_id');
            $table->string('payer_email');
            $table->string('notification_token');
            $table->string('currency');
            $table->float('amount_paid');
            $table->string('invoice_url');
            $table->string('session_status');
            $table->string('payment_status');
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
        Schema::dropIfExists('payment_histories');
    }
};
