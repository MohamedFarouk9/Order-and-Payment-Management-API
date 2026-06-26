<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'idempotency_key' => $this->idempotency_key,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'failure_reason' => $this->failure_reason,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
        ];
    }
}
