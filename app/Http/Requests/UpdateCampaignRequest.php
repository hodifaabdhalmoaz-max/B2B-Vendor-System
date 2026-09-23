<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends StoreCampaignRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $campaignId = (int) ($this->route('campaign')?->id ?? $this->input('id'));
        $rules['slug'] = ['nullable', 'string', 'max:255', Rule::unique('campaigns', 'slug')->ignore($campaignId)];
        $rules['existing_assets'] = ['nullable', 'array'];
        $rules['existing_assets.*.id'] = ['required_with:existing_assets', 'integer', 'exists:campaign_assets,id'];
        $rules['existing_assets.*.delete'] = ['nullable', 'boolean'];
        $rules['existing_assets.*.role'] = $rules['assets.*.role'];
        $rules['existing_assets.*.image'] = ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:4096'];
        $rules['existing_assets.*.title'] = $rules['assets.*.title'];
        $rules['existing_assets.*.subtitle'] = $rules['assets.*.subtitle'];
        $rules['existing_assets.*.price_text'] = $rules['assets.*.price_text'];
        $rules['existing_assets.*.link_url'] = $rules['assets.*.link_url'];
        $rules['existing_assets.*.product_id'] = $rules['assets.*.product_id'];
        $rules['existing_assets.*.category_id'] = $rules['assets.*.category_id'];
        $rules['existing_assets.*.sort_order'] = $rules['assets.*.sort_order'];
        $rules['existing_assets.*.is_active'] = ['nullable', 'boolean'];

        return $rules;
    }
}
