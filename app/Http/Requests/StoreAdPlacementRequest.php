<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdPlacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $placementId = (int) ($this->route('placement')?->id ?? $this->input('id'));

        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('ad_placements', 'key')->ignore($placementId)],
            'name' => ['required', 'string', 'max:255'],
            'recommended_size' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
