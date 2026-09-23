<?php

namespace App\Http\Requests\Admin;

use App\Services\ProductVariantService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkStoreProductVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['prohibited'],
            'variant_key' => ['prohibited'],
            'combinations' => ['required', 'array', 'min:1'],
            'combinations.*' => ['required', 'string', 'regex:/^C:\d+\|S:\d+$/'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $product = $this->route('product');

                if (! $product) {
                    return;
                }

                $service = app(ProductVariantService::class);
                $seen = [];
                $candidates = $service->candidateCombinations($product)->pluck('key')->all();

                $combinations = $this->input('combinations', []);
                if (! is_array($combinations)) {
                    return;
                }

                foreach ($combinations as $combination) {
                    if (! is_string($combination) || ! preg_match('/^C:\d+\|S:\d+$/', $combination)) {
                        continue;
                    }

                    [$colorId, $sizeId] = $service->parseCombinationKey($combination);
                    $canonicalKey = \App\Models\ProductVariant::buildVariantKey($colorId, $sizeId);

                    if (isset($seen[$canonicalKey])) {
                        $validator->errors()->add('combinations', __('Duplicate variant combinations were selected.'));
                    }

                    $seen[$canonicalKey] = true;

                    if (! in_array($canonicalKey, $candidates, true)) {
                        $validator->errors()->add('combinations', __('A selected combination is not in the current product candidates.'));
                    }

                    if ($colorId && ! $product->colors()->whereKey($colorId)->exists()) {
                        $validator->errors()->add('combinations', __('A selected color is not attached to this product.'));
                    }

                    if ($sizeId && ! $product->sizes()->whereKey($sizeId)->exists()) {
                        $validator->errors()->add('combinations', __('A selected size is not attached to this product.'));
                    }

                    if ($product->variants()->where('variant_key', $canonicalKey)->exists()) {
                        $validator->errors()->add('combinations', __('One selected variant already exists.'));
                    }
                }
            },
        ];
    }
}
