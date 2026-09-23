<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->when($this->shouldShowSensitiveData($request), $this->mobile),
            'utype' => $this->when($this->shouldShowSensitiveData($request), $this->utype),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Determine if sensitive data should be shown
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldShowSensitiveData(Request $request): bool
    {
        // Show sensitive data if user is admin or viewing own profile
        return $request->user() && (
            $request->user()->utype === 'ADM' ||
            $request->user()->id === $this->id
        );
    }
}
