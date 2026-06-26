<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'successful', 'failed']);

        return [
            'order_id' => Order::factory(),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'status' => $status,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'gateway_transaction_id' => $status === 'successful' ? $this->faker->uuid() : null,
            'gateway_response' => $status === 'successful' ? ['success' => true] : null,
            'failure_reason' => $status === 'failed' ? 'Card declined' : null,
            'processed_at' => $status !== 'pending' ? now() : null,
        ];
    }

    /**
     * State for successful payments.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'successful',
            'gateway_transaction_id' => $this->faker->uuid(),
            'gateway_response' => ['success' => true],
            'processed_at' => now(),
        ]);
    }

    /**
     * State for failed payments.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failure_reason' => 'Card declined',
            'processed_at' => now(),
        ]);
    }

    /**
     * State for pending payments.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'gateway_transaction_id' => null,
            'processed_at' => null,
        ]);
    }
}
