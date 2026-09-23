<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateResellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => filled($this->username) ? Str::lower(trim((string) $this->username)) : null,
            'email' => filled($this->email) ? Str::lower(trim((string) $this->email)) : null,
            'mobile' => User::normalizeMobile($this->mobile),
            'whatsapp' => filled($this->whatsapp) ? trim((string) $this->whatsapp) : null,
            'reservation_enabled' => $this->boolean('reservation_enabled'),
        ]);
    }

    public function rules(): array
    {
        $resellerId = (int) $this->route('reseller')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($resellerId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($resellerId)],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[+0-9\s().-]{7,20}$/', Rule::unique('users', 'mobile')->ignore($resellerId)],
            'business_name' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9\s().-]{7,20}$/'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reservation_enabled' => ['boolean'],
            'reservation_timeout_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->mobile) {
                    return;
                }

                $resellerId = (int) $this->route('reseller')?->id;

                if (User::query()->whereLoginIdentifier($this->mobile)->whereKeyNot($resellerId)->exists()) {
                    $validator->errors()->add('mobile', __('validation.unique', ['attribute' => 'mobile']));
                }
            },
        ];
    }
}
