<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
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
    public function it_can_list_accounts()
    {
        Account::factory()->count(3);

        $response = $this->actingAs($this->user)->getJson('/api/accounts');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_show_an_account()
    {
        $account = Account::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $account->id]);
    }

    /** @test */
    public function it_returns_404_when_account_not_found()
    {
        $response = $this->actingAs($this->user)->getJson('/api/accounts/999');

        $response->assertStatus(404)
                 ->assertJson(['error' => 'Conta não encontrada']);
    }

    /** @test */
    public function it_can_create_an_account()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'balance' => 100.50,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/accounts', $data);

        $response->assertStatus(201)
                 ->assertJson($data);
    }

    /** @test */
    public function it_validates_account_creation()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accounts', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id', 'balance']);
    }

    /** @test */
    public function it_can_update_an_account()
    {
        $account = Account::factory()->create();

        $data = ['balance' => 200.75];

        $response = $this->actingAs($this->user)->putJson("/api/accounts/{$account->id}", $data);

        $response->assertStatus(200)
                 ->assertJson($data);
    }

    /** @test */
    public function it_returns_404_when_updating_non_existent_account()
    {
        $response = $this->actingAs($this->user)->putJson('/api/accounts/999', ['balance' => 200.75]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_an_account()
    {
        $account = Account::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_account()
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/accounts/999');

        $response->assertStatus(404);
    }
}
