<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('expiration_days')->nullable();
            $table->integer('amount_of_uses')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->float('discount_percent')->nullable();
            $table->float('discount_amount')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('coupons');
    }
};
