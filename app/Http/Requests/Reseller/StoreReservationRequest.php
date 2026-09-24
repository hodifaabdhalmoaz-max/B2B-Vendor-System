<?php

namespace App\Http\Requests\Reseller;

use App\Services\InventoryService;
use App\Services\ResellerReservationCartService;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isReseller() ?? false;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:'.ResellerReservationCartService::MAX_LINES],
            'items.*' => ['required', 'array:product_variant_id,quantity'],
            // Do not require live existence here: historical POST retries must reach the domain.
            'items.*.product_variant_id' => ['required', 'integer', 'min:1', 'max:'.PHP_INT_MAX, 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:'.InventoryService::MAX_QUANTITY],
            'notes' => ['nullable', 'string', 'max:65535'],
            'unit_price' => ['prohibited'], 'total' => ['prohibited'], 'status' => ['prohibited'],
            'reseller_profile_id' => ['prohibited'], 'expires_at' => ['prohibited'],
            'snapshots' => ['prohibited'], 'reservation_number' => ['prohibited'],
        ];
    }

    public function items(): array
    {
        return array_map(fn ($line) => ['product_variant_id' => (int) $line['product_variant_id'], 'quantity' => (int) $line['quantity']], $this->validated('items'));
    }
}
