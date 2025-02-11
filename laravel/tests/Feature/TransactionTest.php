<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $account1;
    protected $account2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->account1 = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 1000]);
        $this->account2 = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 500]);
    }

    /** @test */
    public function it_can_list_transactions()
    {
        Transaction::factory()->count(3);

        $response = $this->actingAs($this->user)->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'sender_id', 'receiver_id', 'amount', 'transaction_type']
            ]);
    }

    /** @test */
    public function it_can_deposit_money_into_an_account()
    {
        $data = [
            'account_id' => $this->account1->id,
            'amount' => 200.00
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions/deposit', $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'amount' => 200,
                'transaction_type' => 'deposit',
                'status' => 'completed',
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->account1->id,
            'balance' => 1200.00
        ]);
    }

    /** @test */
    public function it_prevents_deposit_with_invalid_account()
    {
        $data = [
            'account_id' => 9999, // Conta inexistente
            'amount' => 200.00
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions/deposit', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_id']);
    }

    /** @test */
    public function it_can_transfer_money_between_accounts()
    {
        $data = [
            'from_account_id' => $this->account1->id,
            'to_account_id' => $this->account2->id,
            'amount' => 100.00
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions/transfer', $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'amount' => 100,
                'transaction_type' => 'transfer'
            ]);

        $this->assertDatabaseHas('accounts', ['id' => $this->account1->id, 'balance' => 900.00]);
        $this->assertDatabaseHas('accounts', ['id' => $this->account2->id, 'balance' => 600.00]);
    }

    /** @test */
    public function it_prevents_transfer_if_insufficient_balance()
    {
        $data = [
            'from_account_id' => $this->account1->id,
            'to_account_id' => $this->account2->id,
            'amount' => 2000.00 // Saldo insuficiente
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions/transfer', $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Erro ao processar transferência: Erro ao transferir saldo: Saldo insuficiente.',
                'errors' => [
                    'error' => ['Erro ao processar transferência: Erro ao transferir saldo: Saldo insuficiente.']
                ]
            ]);
    }

    /** @test */
    public function it_can_reverse_a_transaction()
    {
        $transaction = Transaction::factory()->create([
            'sender_id' => $this->account1->id,
            'receiver_id' => $this->account2->id,
            'amount' => 100.00
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/transactions/reverse', [
            'transaction_id' => $transaction->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'reversed'
            ]);

        $this->assertDatabaseHas('accounts', ['id' => $this->account1->id, 'balance' => 1100]);
        $this->assertDatabaseHas('accounts', ['id' => $this->account2->id, 'balance' => 400]);
    }

    /** @test */
    public function it_prevents_reversing_a_nonexistent_transaction()
    {
        $response = $this->actingAs($this->user)->postJson('/api/transactions/reverse', [
            'transaction_id' => 9999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transaction_id']);
    }
}
