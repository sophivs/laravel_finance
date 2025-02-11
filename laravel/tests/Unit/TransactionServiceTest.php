<?php

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Mockery;

class TransactionServiceTest extends TestCase
{
    protected $transactionService;
    protected $transactionRepositoryMock;
    protected $accountServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Criação dos mocks
        $this->transactionRepositoryMock = Mockery::mock(TransactionRepository::class);
        $this->accountServiceMock = Mockery::mock(AccountService::class);

        // Instancia o serviço com os mocks
        $this->transactionService = new TransactionService(
            $this->transactionRepositoryMock,
            $this->accountServiceMock
        );
    }

    public function test_get_all_transactions()
    {
        $this->transactionRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn(collect(['transaction1', 'transaction2']));

        $transactions = $this->transactionService->getAllTransactions();

        $this->assertCount(2, $transactions);
    }

    public function test_deposit()
    {
        $accountId = 1;
        $amount = 100;

        $transactionMock = Mockery::mock(Transaction::class)->makePartial();

        // Mock de métodos chamados dentro de deposit
        $this->accountServiceMock
            ->shouldReceive('increaseBalance')
            ->with($accountId, $amount)
            ->once();

        $this->transactionRepositoryMock
            ->shouldReceive('createTransaction')
            ->with($accountId, $accountId, $amount, 'deposit')
            ->once()
            ->andReturn($transactionMock);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        // Simulando o valor de 'amount' ao chamar o método 'getAttribute'
        $transactionMock->shouldReceive('getAttribute')
            ->with('amount')
            ->andReturn($amount);

        // Executando o método deposit
        $transaction = $this->transactionService->deposit($accountId, $amount);

        // Verificando o retorno
        $this->assertNotNull($transaction);
        $this->assertEquals($transaction->amount, $amount);
    }

    public function test_transfer()
    {
        $transactionMock = Mockery::mock(Transaction::class);
        $this->transactionRepositoryMock->shouldReceive('createTransaction')
            ->once()
            ->andReturn($transactionMock);

        $this->accountServiceMock->shouldReceive('transferBalance')
            ->once()
            ->andReturn(true);

        $response = $this->transactionService->transfer(1, 2, 100);

        $this->assertInstanceOf(Transaction::class, $response);
    }

    public function test_reverse_transaction()
    {
        $transactionMock = Mockery::mock(Transaction::class)->makePartial();

        // Configurar a resposta do atributo 'reversed'
        $transactionMock->shouldReceive('getAttribute')
            ->with('reversed')
            ->andReturn(false);
        $transactionMock->shouldReceive('save')->once();

        // Configurar o repositório para retornar o mock de transação
        $this->transactionRepositoryMock->shouldReceive('findById')
            ->once()
            ->andReturn($transactionMock);

        // Mockando o comportamento do método reverseTransaction
        $this->accountServiceMock->shouldReceive('reverseTransaction')
            ->once()
            ->andReturn(true);

        $response = $this->transactionService->reverseTransaction(1);
        $this->assertInstanceOf(Transaction::class, $response);
    }

    public function tear_down(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
