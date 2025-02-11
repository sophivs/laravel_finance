<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Interfaces\AccountRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;

class AccountServiceTest extends TestCase
{
    protected $accountService;
    protected $accountRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockando o repositório
        $this->accountRepositoryMock = Mockery::mock(AccountRepositoryInterface::class);

        // Criando o serviço com o repositório mockado
        $this->accountService = new AccountService($this->accountRepositoryMock);
    }

    /** @test */
    public function it_can_get_all_accounts()
    {
        // Mockando a resposta do repositório
        $accounts = Mockery::mock(Collection::class);
        $this->accountRepositoryMock->shouldReceive('getAll')->once()->andReturn($accounts);

        $result = $this->accountService->getAllAccounts();

        $this->assertSame($accounts, $result);
    }

    /** @test */
    public function it_can_get_account_by_id()
    {
        $accountId = 1;
        $account = Mockery::mock(Account::class);

        // Mockando a busca da conta
        $this->accountRepositoryMock->shouldReceive('findById')->with($accountId)->once()->andReturn($account);

        $result = $this->accountService->getAccountById($accountId);

        $this->assertSame($account, $result);
    }

    /** @test */
    public function it_throws_not_found_exception_when_account_not_found()
    {
        $accountId = 999;

        // Mockando a resposta do repositório para retorno null
        $this->accountRepositoryMock->shouldReceive('findById')->with($accountId)->once()->andReturnNull();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Conta não encontrada');

        $this->accountService->getAccountById($accountId);
    }

    /** @test */
    public function it_can_create_account()
    {
        $data = ['user_id' => 1, 'balance' => 100];

        $account = Mockery::mock(Account::class);

        // Mockando a criação de conta
        $this->accountRepositoryMock->shouldReceive('create')->with($data)->once()->andReturn($account);

        $result = $this->accountService->createAccount($data);

        $this->assertSame($account, $result);
    }

    /** @test */
    public function it_can_update_account()
    {
        $accountId = 1;
        $data = ['balance' => 500];
        $account = Mockery::mock(Account::class);

        // Mockando a conta que será retornada
        $this->accountRepositoryMock->shouldReceive('findById')->with($accountId)->once()->andReturn($account);
        $this->accountRepositoryMock->shouldReceive('update')->with($account, $data)->once()->andReturn($account);

        $result = $this->accountService->updateAccount($accountId, $data);

        $this->assertSame($account, $result);
    }

    /** @test */
    public function it_can_delete_account()
    {
        $accountId = 1;
        $account = Mockery::mock(Account::class);

        // Mockando a conta e a exclusão
        $this->accountRepositoryMock->shouldReceive('findById')->with($accountId)->once()->andReturn($account);
        $this->accountRepositoryMock->shouldReceive('delete')->with($account)->once();

        $this->accountService->deleteAccount($accountId);
    }

    /** @test */
    public function it_can_increase_balance()
    {
        $accountId = 1;
        $amount = 500;
        $account = Mockery::mock(Account::class);

        $account->shouldReceive('getAttribute')->with('balance')->andReturn(1000);
        $account->shouldReceive('setAttribute')
            ->with('balance', 1500)
            ->once();

        $this->accountRepositoryMock
            ->shouldReceive('findById')
            ->with($accountId)
            ->once()
            ->andReturn($account);

        $account->shouldReceive('save')->once();

        $this->accountService->increaseBalance($accountId, $amount);
    }

    /** @test */
    public function it_can_decrease_balance()
    {
        $accountId = 1;
        $amount = 100;
        $account = Mockery::mock(Account::class);

        $account->shouldReceive('getAttribute')->with('balance')->andReturn(1000);
        $account->shouldReceive('setAttribute')
            ->with('balance', 900)
            ->once();

        // Mockando a conta que será retornada
        $this->accountRepositoryMock->shouldReceive('findById')->with($accountId)->once()->andReturn($account);
        $account->shouldReceive('save')->once();

        $this->accountService->decreaseBalance($accountId, $amount);
    }

    /** @test */
    public function it_can_transfer_balance()
    {
        $fromAccountId = 1;
        $toAccountId = 2;
        $amount = 100;

        // Mockando as transferências de saldo
        $this->accountService = Mockery::mock(AccountService::class)->makePartial();
        $this->accountService->shouldReceive('decreaseBalance')->once();
        $this->accountService->shouldReceive('increaseBalance')->once();

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->accountService->transferBalance($fromAccountId, $toAccountId, $amount);
    }

    /** @test */
    public function it_can_reverse_transaction()
    {
        $transaction = Mockery::mock(Transaction::class);

        $transaction->shouldReceive('getAttribute')->with('sender_id')->andReturn(1);
        $transaction->shouldReceive('setAttribute')
            ->with('sender_id', 1)
            ->once();

        $transaction->shouldReceive('getAttribute')->with('receiver_id')->andReturn(2);
        $transaction->shouldReceive('setAttribute')
            ->with('receiver_id', 2)
            ->once();

        $transaction->shouldReceive('getAttribute')->with('amount')->andReturn(100);
        $transaction->shouldReceive('setAttribute')
            ->with('amount', 100)
            ->once();

        $transaction->shouldReceive('getAttribute')->with('transaction_type')->andReturn('transfer');
        $transaction->shouldReceive('setAttribute')
            ->with('transaction_type', 'transfer')
            ->once();

        $transaction->shouldReceive('getAttribute')->with('reversed')->andReturn(FALSE);
        $transaction->shouldReceive('setAttribute')
            ->with('reversed', FALSE)
            ->once();

        $this->accountService = Mockery::mock(AccountService::class)->makePartial();
        $this->accountService->shouldReceive('increaseBalance')->once();
        $this->accountService->shouldReceive('decreaseBalance')->once();

        $this->accountService->reverseTransaction($transaction);
    }
}
