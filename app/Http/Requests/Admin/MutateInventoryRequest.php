<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MutateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'operation' => ['required', Rule::in(['stock_in', 'stock_out', 'adjustment'])],
            'quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'idempotency_key' => ['required', 'string', 'max:255'],
            'variant_form_id' => ['nullable', 'integer'],
        ];
    }
}
