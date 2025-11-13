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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('subscription_id');
            $table->string('email', 255);
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('transactions');
    }
};
