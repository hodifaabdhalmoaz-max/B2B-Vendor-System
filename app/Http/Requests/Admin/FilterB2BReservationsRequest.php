<?php

namespace App\Http\Requests\Admin;

use App\Services\AdminReservationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterB2BReservationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['all', ...array_keys(AdminReservationService::statuses())])],
            'search' => ['nullable', 'string', 'max:150'],
            'reseller_profile_id' => ['nullable', 'integer', 'exists:reseller_profiles,id'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', ...($this->filled('start_date') ? ['after_or_equal:start_date'] : [])],
            'overdue' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
