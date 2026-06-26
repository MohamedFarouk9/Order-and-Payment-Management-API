<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderItem Model
 *
 * Represents a single line item in an order.
 * Each order can have multiple items with different products and quantities.
 */
class OrderItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];


    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    public function updateDetails(int $quantity, float $price): bool
    {
        $subtotal = $quantity * $price;

        $updated = $this->update([
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);

        // Recalculate parent order total
        if ($updated && $this->order) {
            $this->order->recalculateTotal();
        }

        return $updated;
    }
}
