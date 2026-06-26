<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Order extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'items_count',
        'notes',
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if order can be deleted.
     * Orders with payments cannot be deleted.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return $this->payments()->count() === 0;
    }


    public function canBeConfirmed(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Confirm the order (change status from pending to confirmed).
     *
     * @return bool
     */
    public function confirm(): bool
    {
        if (!$this->canBeConfirmed()) {
            return false;
        }

        return $this->update(['status' => 'confirmed']);
    }

    /**
     * Calculate and update the total amount based on order items.
     * This is typically called after items are added or modified.
     *
     * @return void
     */
    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');
        $itemsCount = $this->items()->sum('quantity');

        $this->update([
            'total_amount' => $total,
            'items_count' => $itemsCount,
        ]);
    }

 
    public function addItem(string $productName, int $quantity, float $price): OrderItem
    {
        $subtotal = $quantity * $price;

        $item = $this->items()->create([
            'product_name' => $productName,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);

        // Recalculate order total after adding item
        $this->recalculateTotal();

        return $item;
    }

    /**
     * Get successful payments for this order.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function successfulPayments()
    {
        return $this->payments()->where('status', 'successful');
    }


}
