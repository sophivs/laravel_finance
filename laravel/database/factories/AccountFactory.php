<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), // Relacionado a um usuário fake
            'balance' => $this->faker->randomFloat(2, 100, 10000), // Saldo aleatório entre 100 e 10.000
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
