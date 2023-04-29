<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,  
            'customer_email' => 'btobi@3wc4life.com', 
            'customer_phone' => '08183221437',
            'amount' => 10000,
            'plan_code' => 'PLN_gx2wn530m0i3w3m',
            'plan_name' => 'Game Portal Monthly',
            'status' => 'expired',
            'subscription_code' => 'SUB_vsyqdmlzble3uii',
            'authorization' => 'AUTH_6tmt288t0o',
            'starts_at' => date("Y-m-d H:i:s",time()),
            'ends_at' => null,
            'next_payment_date' => null
        ];
    }
}
