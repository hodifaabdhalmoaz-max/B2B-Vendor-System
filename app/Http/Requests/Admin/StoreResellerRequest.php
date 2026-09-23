<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StoreResellerRequest extends FormRequest
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
            'mobile' => filled($this->mobile) ? trim((string) $this->mobile) : null,
            'whatsapp' => filled($this->whatsapp) ? trim((string) $this->whatsapp) : null,
            'reservation_enabled' => $this->boolean('reservation_enabled'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[+0-9\s().-]{7,20}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'business_name' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9\s().-]{7,20}$/'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reservation_enabled' => ['boolean'],
            'reservation_timeout_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ];
    }
}
