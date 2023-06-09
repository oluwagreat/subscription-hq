<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subscription::factory()->create([
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
        ]);
    }
}
