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
        Schema::create('realizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('plan_detail_id')->nullable();
            $table->text('description');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('plan_detail_id')->references('id')->on('plan_details');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realizations');
    }
};
