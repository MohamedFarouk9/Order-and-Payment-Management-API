<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * OrderManagementTest
 * 
 * Tests order CRUD operations.
 * Covers creation, retrieval, updating, deletion, and status transitions.
 */
class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test creating an order with items.
     */
    public function test_user_can_create_order()
    {
        $response = $this->actingAs($this->user, 'web')->postJson('/api/orders', [
            'notes' => 'Test order',
            'items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => 2,
                    'price' => 10.00,
                ],
                [
                    'product_name' => 'Product B',
                    'quantity' => 1,
                    'price' => 20.00,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'total_amount',
                    'items_count',
                    'items' => [
                        '*' => ['id', 'product_name', 'quantity', 'price', 'subtotal'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 40.00,
            'status' => 'pending',
        ]);
    }

    /**
     * Test order creation fails without items.
     */
    public function test_order_creation_fails_without_items()
    {
        $response = $this->actingAs($this->user, 'web')->postJson('/api/orders', [
            'items' => [],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test retrieving orders.
     */
    public function test_user_can_list_orders()
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'total_amount'],
                ],
                'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);
    }

    /**
     * Test filtering orders by status.
     */
    public function test_user_can_filter_orders_by_status()
    {
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed']);
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'cancelled']);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/orders?status=confirmed');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    /**
     * Test user cannot see other user's orders.
     */
    public function test_user_cannot_see_other_users_orders()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'web')->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    /**
     * Test updating an order.
     */
    public function test_user_can_update_pending_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->actingAs($this->user, 'web')->putJson("/api/orders/{$order->id}", [
            'notes' => 'Updated notes',
            'items' => [
                [
                    'product_name' => 'New Product',
                    'quantity' => 5,
                    'price' => 15.00,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'notes' => 'Updated notes',
        ]);
    }

    /**
     * Test cannot update confirmed orders.
     */
    public function test_cannot_update_confirmed_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed']);

        $response = $this->actingAs($this->user, 'web')->putJson("/api/orders/{$order->id}", [
            'notes' => 'Updated notes',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test confirming an order.
     */
    public function test_user_can_confirm_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->actingAs($this->user, 'web')->postJson("/api/orders/{$order->id}/confirm");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test deleting an order without payments.
     */
    public function test_user_can_delete_order_without_payments()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /**
     * Test cannot delete orders with payments.
     */
    public function test_cannot_delete_order_with_payments()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        \App\Models\Payment::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($this->user, 'web')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(422);
    }
}
