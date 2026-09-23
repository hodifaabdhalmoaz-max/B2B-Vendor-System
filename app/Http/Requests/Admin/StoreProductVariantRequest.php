<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductVariantRequest extends FormRequest
{
    private bool $invalidManualSku = false;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $rawSku = $this->input('sku');
        $normalizedSku = is_string($rawSku) ? ProductVariantService::normalizeSku($rawSku) : null;
        $this->invalidManualSku = filled($rawSku) && $normalizedSku === null;
        $this->merge([
            'color_id' => filled($this->color_id) ? $this->color_id : null,
            'size_id' => filled($this->size_id) ? $this->size_id : null,
            'sku' => $normalizedSku,
            'price_adjustment' => $this->price_adjustment ?? '0.00',
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['prohibited'],
            'variant_key' => ['prohibited'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'size_id' => ['nullable', 'integer', 'exists:sizes,id'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')],
            'price_adjustment' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'is_active' => ['boolean'],
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

                $colorId = $this->input('color_id');
                $sizeId = $this->input('size_id');

                if ($validator->errors()->has('color_id') || $validator->errors()->has('size_id') || $validator->errors()->has('price_adjustment')) {
                    return;
                }

                if ($this->invalidManualSku) {
                    $validator->errors()->add('sku', __('The variant SKU is invalid.'));
                }

                if ($colorId && ! $product->colors()->whereKey($colorId)->exists()) {
                    $validator->errors()->add('color_id', __('The selected color is not attached to this product.'));
                }

                if ($sizeId && ! $product->sizes()->whereKey($sizeId)->exists()) {
                    $validator->errors()->add('size_id', __('The selected size is not attached to this product.'));
                }

                $variantKey = ProductVariant::buildVariantKey($colorId, $sizeId);
                if ($product->variants()->where('variant_key', $variantKey)->exists()) {
                    $validator->errors()->add('variant', __('This exact product variant already exists.'));
                }

                if (((float) $product->current_price + (float) $this->input('price_adjustment', 0)) < 0) {
                    $validator->errors()->add('price_adjustment', __('The variant price adjustment cannot make the effective price negative.'));
                }
            },
        ];
    }
}
