<?php

namespace Tests\Feature\Transactions;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Controller tests for Transaction CRUD
 *
 * Test types covered: Controller, Integration, Sanity, Regression
 */
class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    /** @test */
    public function index_returns_200(): void
    {
        $this->asAdmin()->get(route('admin.transactions.index'))->assertOk();
    }

    /** @test */
    public function index_shows_transaction_ids(): void
    {
        $txn = Transaction::factory()->create(['transaction_id' => 'TXN-TEST001']);

        $this->asAdmin()
             ->get(route('admin.transactions.index'))
             ->assertSee('TXN-TEST001');
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    /** @test */
    public function create_page_returns_200(): void
    {
        $this->asAdmin()->get(route('admin.transactions.create'))->assertOk();
    }

    /** @test */
    public function store_creates_transaction_in_database(): void
    {
        $payload = [
            'type'           => 'credit',
            'category'       => 'payment',
            'amount'         => 5000,
            'fee'            => 0,
            'currency'       => 'INR',
            'status'         => 'pending',
            'payment_method' => 'bank_transfer',
            'sender_name'    => 'Test Sender',
            'receiver_name'  => 'AS Dairy Farm',
        ];

        $this->asAdmin()
             ->post(route('admin.transactions.store'), $payload)
             ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'category' => 'payment',
            'amount'   => 5000,
            'type'     => 'credit',
        ]);
    }

    /** @test */
    public function store_calculates_net_amount_correctly(): void
    {
        $this->asAdmin()->post(route('admin.transactions.store'), [
            'type'           => 'credit',
            'category'       => 'payment',
            'amount'         => 5000,
            'fee'            => 100,
            'currency'       => 'INR',
            'status'         => 'pending',
            'payment_method' => 'bank_transfer',
            'sender_name'    => 'Test',
            'receiver_name'  => 'Farm',
        ]);

        $this->assertDatabaseHas('transactions', ['net_amount' => 4900]);
    }

    // ── Show / Edit ───────────────────────────────────────────────────────────

    /** @test */
    public function show_returns_200_with_transaction_data(): void
    {
        $txn = Transaction::factory()->create();

        $this->asAdmin()
             ->get(route('admin.transactions.show', $txn))
             ->assertOk()
             ->assertSee($txn->transaction_id);
    }

    /** @test */
    public function edit_page_returns_200(): void
    {
        $txn = Transaction::factory()->create();

        $this->asAdmin()->get(route('admin.transactions.edit', $txn))->assertOk();
    }

    // ── Update Status ─────────────────────────────────────────────────────────

    /** @test */
    public function update_status_endpoint_changes_status(): void
    {
        $txn = Transaction::factory()->pending()->create();

        $this->asAdmin()
             ->postJson(route('admin.transactions.status', $txn), ['status' => 'success', 'notes' => ''])
             ->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', ['id' => $txn->id, 'status' => 'success']);
    }

    // ── Soft Delete / Trash / Restore ─────────────────────────────────────────

    /** @test */
    public function delete_soft_deletes_transaction(): void
    {
        $txn = Transaction::factory()->create();

        $this->asAdmin()
             ->delete(route('admin.transactions.destroy', $txn))
             ->assertRedirect();

        $this->assertSoftDeleted('transactions', ['id' => $txn->id]);
    }

    /** @test */
    public function trash_page_returns_200(): void
    {
        $this->asAdmin()->get(route('admin.transactions.trash'))->assertOk();
    }

    /** @test */
    public function restore_recovers_soft_deleted_transaction(): void
    {
        $txn = Transaction::factory()->create();
        $txn->delete();

        $this->asAdmin()
             ->post(route('admin.transactions.restore', $txn->id))
             ->assertRedirect();

        $this->assertDatabaseHas('transactions', ['id' => $txn->id, 'deleted_at' => null]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    /** @test */
    public function store_fails_when_amount_is_missing(): void
    {
        $this->asAdmin()
             ->post(route('admin.transactions.store'), [
                 'type'           => 'credit',
                 'category'       => 'payment',
                 'payment_method' => 'cash',
                 'status'         => 'pending',
                 'sender_name'    => 'Test',
                 'receiver_name'  => 'Farm',
             ])
             ->assertSessionHasErrors('amount');
    }

    /** @test */
    public function store_fails_when_type_is_invalid(): void
    {
        $this->asAdmin()
             ->post(route('admin.transactions.store'), [
                 'type'           => 'invalid_type',
                 'category'       => 'payment',
                 'amount'         => 100,
                 'payment_method' => 'cash',
                 'status'         => 'pending',
                 'sender_name'    => 'Test',
                 'receiver_name'  => 'Farm',
             ])
             ->assertSessionHasErrors('type');
    }
}
