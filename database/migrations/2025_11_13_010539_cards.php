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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('card_number');
            $table->string('client_name', 255);
            $table->date('expire_date');
            $table->integer('cvc');
            $table->unsignedBigInteger('card_flag_id');
            $table->timestamps();

            $table->foreign('card_flag_id')->references('id')->on('card_flags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('cards');
    }
};
