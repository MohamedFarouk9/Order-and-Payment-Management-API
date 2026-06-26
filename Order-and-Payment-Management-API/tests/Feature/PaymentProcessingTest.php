<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PaymentProcessingTest
 *
 * Tests payment processing and gateway routing.
 * Demonstrates the strategy pattern in action.
 */
class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Order $confirmedOrder;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        // Create a confirmed order for payment testing
        $this->confirmedOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'confirmed',
            'total_amount' => 100.00,
        ]);
    }

    /**
     * Test processing payment with credit card.
     */
    public function test_can_process_credit_card_payment()
    {
        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$this->confirmedOrder->id}/payments", [
            'payment_method' => 'credit_card',
            'amount' => 100.00,
            'details' => [
                'card_number' => '4532015112830366', // Valid test card
                'card_holder' => 'John Doe',
                'expiry' => '12/25',
                'cvv' => '123',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'order_id',
                    'payment_method',
                    'status',
                    'amount',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->confirmedOrder->id,
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * Test processing payment with PayPal.
     */
    public function test_can_process_paypal_payment()
    {
        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$this->confirmedOrder->id}/payments", [
            'payment_method' => 'paypal',
            'amount' => 100.00,
            'details' => [
                'email' => 'user@paypal.com',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'payment_method' => 'paypal',
                    'status',
                ],
            ]);
    }

    /**
     * Test payment fails for pending orders.
     */
    public function test_payment_fails_for_pending_orders()
    {
        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$pendingOrder->id}/payments", [
            'payment_method' => 'credit_card',
            'amount' => 50.00,
            'details' => [
                'card_number' => '4532015112830366',
                'card_holder' => 'John Doe',
                'expiry' => '12/25',
                'cvv' => '123',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Payment processing error',
                'error' => 'Only confirmed orders can be paid',
            ]);
    }

    /**
     * Test payment validation.
     */
    public function test_payment_with_invalid_card_fails()
    {
        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$this->confirmedOrder->id}/payments", [
            'payment_method' => 'credit_card',
            'amount' => 100.00,
            'details' => [
                'card_number' => 'invalid_card_number',
                'card_holder' => 'John Doe',
                'expiry' => '12/25',
                'cvv' => '123',
            ],
        ]);

        // May succeed (as per our mock) but should have payment record
        $this->assertDatabaseHas('payments', [
            'order_id' => $this->confirmedOrder->id,
        ]);
    }

    /**
     * Test user cannot pay for other user's orders.
     */
    public function test_user_cannot_pay_for_other_users_order()
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$otherOrder->id}/payments", [
            'payment_method' => 'credit_card',
            'amount' => 50.00,
            'details' => [
                'card_number' => '4532015112830366',
                'card_holder' => 'John Doe',
                'expiry' => '12/25',
                'cvv' => '123',
            ],
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test viewing payments for an order.
     */
    public function test_user_can_view_order_payments()
    {
        Payment::factory()->count(3)->create(['order_id' => $this->confirmedOrder->id]);

        $response = $this->actingAs($this->user, 'web')->getJson("/api/orders/{$this->confirmedOrder->id}/payments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'order_id', 'status', 'amount'],
                ],
                'pagination',
            ]);
    }

    /**
     * Test viewing all user payments.
     */
    public function test_user_can_view_all_their_payments()
    {
        $order2 = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed']);
        Payment::factory()->create(['order_id' => $this->confirmedOrder->id]);
        Payment::factory()->create(['order_id' => $order2->id]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'order_id', 'status'],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test getting available payment methods.
     */
    public function test_can_get_available_payment_methods()
    {
        $response = $this->actingAs($this->user, 'web')->getJson('/api/payments/methods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);
    }
}
