<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed payments for confirmed orders.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $paymentMethods = ['credit_card', 'paypal', 'stripe'];

        $confirmedOrders = Order::where('status', 'confirmed')->get();

        foreach ($confirmedOrders as $order) {
            Payment::updateOrCreate(
                ['order_id' => $order->id, 'idempotency_key' => 'seeded-' . $order->id],
                [
                    'payment_method' => $faker->randomElement($paymentMethods),
                    'status' => 'successful',
                    'amount' => $order->total_amount,
                    'gateway_transaction_id' => $faker->uuid(),
                    'gateway_response' => [
                        'status' => 'approved',
                        'processor' => 'seeded_gateway',
                    ],
                    'processed_at' => now(),
                ]
            );
        }
    }
}
