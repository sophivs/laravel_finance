<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Account;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'sender_id' => User::factory(), // Criando um usuário fictício como remetente
            'receiver_id' => User::factory(), // Criando um usuário fictício como destinatário
            'amount' => $this->faker->randomFloat(2, 1, 5000), // Valores aleatórios entre 1 e 5000
            'transaction_type' => 'transfer', // Pode ser 'deposit' ou 'transfer'
            'status' => 'completed',
            'reversed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
