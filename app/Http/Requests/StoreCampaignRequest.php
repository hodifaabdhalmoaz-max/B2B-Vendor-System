<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use App\Models\CampaignAsset;
use App\Models\CampaignCreative;
use App\Models\CampaignRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:campaigns,slug'],
            'type' => ['required', Rule::in(Campaign::TYPES)],
            'status' => ['required', Rule::in(Campaign::STATUSES)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'priority' => ['required', 'integer', 'min:-100000', 'max:100000'],
            'is_active' => ['required', 'boolean'],
            'placements' => ['required', 'array', 'min:1'],
            'placements.*' => ['integer', 'exists:ad_placements,id'],
            'placement_sort_order' => ['nullable', 'array'],
            'placement_sort_order.*' => ['nullable', 'integer', 'min:0', 'max:65535'],

            'creative.title' => ['nullable', 'string', 'max:255'],
            'creative.subtitle' => ['nullable', 'string', 'max:255'],
            'creative.description' => ['nullable', 'string'],
            'creative.cta_text' => ['nullable', 'string', 'max:100'],
            'creative.link_type' => ['required', Rule::in(CampaignCreative::LINK_TYPES)],
            'creative.link_url' => ['nullable', 'required_if:creative.link_type,url', 'url'],
            'creative.product_id' => ['nullable', 'required_if:creative.link_type,product', 'integer', 'exists:products,id'],
            'creative.category_id' => ['nullable', 'required_if:creative.link_type,category', 'integer', 'exists:categories,id'],
            'creative.background_color' => ['nullable', 'string', 'max:20'],
            'creative.text_color' => ['nullable', 'string', 'max:20'],
            'creative.alt_text' => ['nullable', 'string', 'max:255'],

            'rule_target_type' => ['nullable', Rule::in(CampaignRule::TARGET_TYPES)],
            'rule_home_tab_slug' => ['nullable', 'string', 'max:255'],
            'rule_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'rule_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'rule_device' => ['nullable', 'string', 'max:50'],

            'assets' => ['nullable', 'array'],
            'assets.*.role' => ['nullable', Rule::in(CampaignAsset::ROLES)],
            'assets.*.image' => ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:4096'],
            'assets.*.title' => ['nullable', 'string', 'max:255'],
            'assets.*.subtitle' => ['nullable', 'string', 'max:255'],
            'assets.*.price_text' => ['nullable', 'string', 'max:100'],
            'assets.*.link_url' => ['nullable', 'url'],
            'assets.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'assets.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'assets.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'assets.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
