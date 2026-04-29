<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_traditional_registration_is_unavailable(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(405);
    }

    public function test_traditional_login_is_unavailable(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertStatus(405);
    }

    /**
     * Test Case 3: test_transaction_can_be_created
     * Simulate a form post and assert the transaction table contains the data.
     */
    public function test_user_can_create_transaction(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/transactions', [
            'description' => 'Salary',
            'amount' => 15.50,
            'type' => 'income',
        ]);

        $this->assertDatabaseHas('transaction', [
            'description' => 'Salary',
            'amount' => 15.50,
            'type' => 'income',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('savings_entries', [
            'user_id' => $user->id,
            'description' => 'Salary',
        ]);

        $transaction = Transaction::where('user_id', $user->id)->first();
        $this->assertNull($transaction->savings_entry_id);
        $this->assertEquals(15.50, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(0.00, (float) $user->fresh()->total_saved);
        $this->assertDatabaseMissing('badges', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_create_expense_transaction_without_savings_history(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['wallet_balance' => 100.00, 'total_saved' => 0.00]);

        $this->actingAs($user)->post('/transactions', [
            'description' => 'Buying Coffee',
            'amount' => 15.50,
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('transaction', [
            'description' => 'Buying Coffee',
            'amount' => 15.50,
            'type' => 'expense',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('savings_entries', [
            'user_id' => $user->id,
            'description' => 'Buying Coffee',
        ]);

        $this->assertEquals(84.50, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(0.00, (float) $user->fresh()->total_saved);
    }

    public function test_expense_larger_than_wallet_is_rejected(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['wallet_balance' => 10.00]);

        $this->actingAs($user)->post('/transactions', [
            'description' => 'Too Expensive',
            'amount' => 15.50,
            'type' => 'expense',
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseMissing('transaction', [
            'description' => 'Too Expensive',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('savings_entries', [
            'description' => 'Too Expensive',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test Case 4: test_transaction_can_be_deleted
     * Assert that a record is removed after a delete request.
     */
    public function test_transaction_delete_request_is_rejected(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['wallet_balance' => 100.00, 'total_saved' => 0.00]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'savings_entry_id' => null,
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'income',
        ]);

        $response = $this->actingAs($user)->delete("/transactions/{$transaction->id}");

        $response->assertSessionHasErrors('transaction');

        $this->assertDatabaseHas('transaction', [
            'id' => $transaction->id,
        ]);

        $this->assertEquals(100.00, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(0.00, (float) $user->fresh()->total_saved);
    }
}
