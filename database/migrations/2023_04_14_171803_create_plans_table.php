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
        Schema::create('plans', function (Blueprint $table) {
            $table->id()->index();
            $table->string('plan_code')->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('amount');
            $table->enum('interval', ['daily', 'weekly', 'monthly','biannually', 'annually'])->default('monthly');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('plans');
    }
};
