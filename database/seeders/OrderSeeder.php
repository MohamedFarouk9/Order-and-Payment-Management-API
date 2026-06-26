<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed orders and order items for testing.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $seededUsers = User::whereIn('email', ['test@example.com', 'alice@example.com', 'bob@example.com'])->get()->keyBy('email');

        $orders = [
            [
                'email' => 'test@example.com',
                'status' => 'pending',
                'notes' => 'Gift wrap and deliver after 5pm.',
                'items' => [
                    ['product_name' => 'Wireless Mouse', 'quantity' => 1, 'price' => 29.99],
                    ['product_name' => 'USB-C Cable', 'quantity' => 2, 'price' => 9.99],
                ],
            ],
            [
                'email' => 'test@example.com',
                'status' => 'confirmed',
                'notes' => 'Rush order for client presentation.',
                'items' => [
                    ['product_name' => 'Laptop Stand', 'quantity' => 1, 'price' => 49.99],
                    ['product_name' => 'Bluetooth Keyboard', 'quantity' => 1, 'price' => 59.99],
                ],
            ],
            [
                'email' => 'alice@example.com',
                'status' => 'confirmed',
                'notes' => 'Send to billing address.',
                'items' => [
                    ['product_name' => 'Desk Lamp', 'quantity' => 1, 'price' => 34.50],
                ],
            ],
            [
                'email' => 'bob@example.com',
                'status' => 'pending',
                'notes' => 'Order placed while user was on mobile.',
                'items' => [
                    ['product_name' => 'Noise Cancelling Headphones', 'quantity' => 1, 'price' => 199.99],
                    ['product_name' => 'Travel Bag', 'quantity' => 1, 'price' => 79.99],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $user = $seededUsers[$orderData['email']] ?? null;

            if (!$user) {
                continue;
            }

            $this->createOrder($user->id, $orderData['status'], $orderData['notes'], $orderData['items']);
        }

        $randomUsers = User::whereNotIn('email', ['test@example.com', 'alice@example.com', 'bob@example.com'])->get();

        foreach ($randomUsers as $user) {
            foreach (range(1, 2) as $_) {
                $status = $faker->randomElement(['pending', 'confirmed', 'cancelled']);
                $items = [];

                foreach (range(1, $faker->numberBetween(1, 4)) as $itemIndex) {
                    $items[] = [
                        'product_name' => ucfirst($faker->words($faker->numberBetween(1, 3), true)),
                        'quantity' => $faker->numberBetween(1, 3),
                        'price' => $faker->randomFloat(2, 5, 250),
                    ];
                }

                $this->createOrder($user->id, $status, $faker->sentence(6), $items);
            }
        }
    }

    private function createOrder(int $userId, string $status, string $notes, array $items): void
    {
        $order = Order::create([
            'user_id' => $userId,
            'status' => $status,
            'total_amount' => 0,
            'items_count' => 0,
            'notes' => $notes,
        ]);

        $totalAmount = 0;
        $itemsCount = 0;

        foreach ($items as $itemData) {
            $subtotal = $itemData['quantity'] * $itemData['price'];
            $totalAmount += $subtotal;
            $itemsCount += $itemData['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $itemData['product_name'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'subtotal' => $subtotal,
            ]);
        }

        $order->update([
            'total_amount' => $totalAmount,
            'items_count' => $itemsCount,
        ]);
    }
}
