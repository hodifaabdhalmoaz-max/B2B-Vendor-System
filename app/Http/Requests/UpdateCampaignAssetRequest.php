<?php

namespace App\Http\Requests;

class UpdateCampaignAssetRequest extends StoreCampaignAssetRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['image'] = ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:4096'];

        return $rules;
    }
}
