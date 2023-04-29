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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('plan_code')->nullable();
            $table->string('plan_name')->nullable();
            $table->string('subscription_code')->nullable();
            $table->string('authorization')->nullable();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('email_token')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('next_payment_date')->nullable();
            $table->integer('amount')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_code')->references('plan_code')->on('plans')->onDelete('cascade');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('plan_code');
        });
        Schema::dropIfExists('subscriptions');
    }
};
