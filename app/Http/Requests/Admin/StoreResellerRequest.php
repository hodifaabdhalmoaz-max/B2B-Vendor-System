<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
            'mobile' => User::normalizeMobile($this->mobile),
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->username && $this->hasMobileIdentifierCollision($this->username)) {
                    $validator->errors()->add('username', __('validation.unique', ['attribute' => 'username']));
                }

                if (! $this->mobile) {
                    return;
                }

                if (User::query()->whereLoginIdentifier($this->mobile)->exists()) {
                    $validator->errors()->add('mobile', __('validation.unique', ['attribute' => 'mobile']));
                }
            },
        ];
    }

    private function hasMobileIdentifierCollision(string $identifier): bool
    {
        $normalizedMobile = User::normalizeMobile($identifier);

        if (! $normalizedMobile || ! preg_match('/^\d{7,20}$/', $normalizedMobile)) {
            return false;
        }

        return User::query()
            ->where(function ($query) use ($normalizedMobile): void {
                $query
                    ->where('mobile', $normalizedMobile)
                    ->orWhereRaw(
                        "replace(replace(replace(replace(replace(mobile, ' ', ''), '-', ''), '(', ''), ')', ''), '.', '') = ?",
                        [$normalizedMobile]
                    );
            })
            ->exists();
    }
}
