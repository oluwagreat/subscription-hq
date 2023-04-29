<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Str::random(10), //fake()->regexify('[a-z]{6}[0-9]{5}'),  
        'customer_email' => fake()->safeEmail(),
        'customer_phone'=>fake()->phoneNumber(),
        'amount' =>fake()->randomNumber(5,true),
        'user_id' => User::all()->random()->id

        ];
    }
}
