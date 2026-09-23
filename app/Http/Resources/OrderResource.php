<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'shipping_address' => [
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'locality' => $this->locality,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'landmark' => $this->landmark,
                'zip' => $this->zip,
            ],
            'user' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'transaction' => new TransactionResource($this->whenLoaded('transaction')),
            'delivered_date' => $this->delivered_date?->toISOString(),
            'canceled_date' => $this->canceled_date?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'currency' => 'YER',
                'currency_symbol' => 'ر.ي',
            ],
        ];
    }
}
