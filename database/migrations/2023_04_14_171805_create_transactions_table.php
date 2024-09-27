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
            $table->string('reference')->unique();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('authorization_code')->nullable();
            $table->string('authorization_url')->nullable();
            $table->string('access_code')->nullable()->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('gateway_response')->nullable();
            $table->string('callback_url');
            $table->string('plan_code')->nullable();
            $table->integer('amount');
            $table->string('paid_at')->nullable();
            $table->string('plan')->nullable();
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('transactions');
    }
};
