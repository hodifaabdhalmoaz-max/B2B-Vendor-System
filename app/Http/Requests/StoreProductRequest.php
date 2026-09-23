<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->utype === 'ADM';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'SKU' => 'required|string|unique:products,SKU',
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'images.*' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'brand_id' => 'required|integer|exists:brands,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'name.string' => 'اسم المنتج يجب أن يكون نصاً',
            'name.max' => 'اسم المنتج يجب ألا يزيد عن 255 حرفاً',
            
            'slug.required' => 'رابط المنتج مطلوب',
            'slug.string' => 'رابط المنتج يجب أن يكون نصاً',
            'slug.unique' => 'رابط المنتج موجود مسبقاً',
            
            'short_description.required' => 'الوصف المختصر مطلوب',
            'short_description.string' => 'الوصف المختصر يجب أن يكون نصاً',
            
            'description.required' => 'الوصف التفصيلي مطلوب',
            'description.string' => 'الوصف التفصيلي يجب أن يكون نصاً',
            
            'regular_price.required' => 'السعر العادي مطلوب',
            'regular_price.numeric' => 'السعر العادي يجب أن يكون رقماً',
            'regular_price.min' => 'السعر العادي يجب أن يكون أكبر من أو يساوي صفر',
            
            'sale_price.required' => 'سعر التخفيض مطلوب',
            'sale_price.numeric' => 'سعر التخفيض يجب أن يكون رقماً',
            'sale_price.min' => 'سعر التخفيض يجب أن يكون أكبر من أو يساوي صفر',
            
            'SKU.required' => 'رمز المنتج مطلوب',
            'SKU.string' => 'رمز المنتج يجب أن يكون نصاً',
            'SKU.unique' => 'رمز المنتج موجود مسبقاً',
            
            'stock_status.required' => 'حالة المخزون مطلوبة',
            'stock_status.in' => 'حالة المخزون يجب أن تكون متوفر أو غير متوفر',
            
            'featured.required' => 'تحديد ما إذا كان المنتج مميزاً مطلوب',
            'featured.boolean' => 'قيمة المنتج المميز يجب أن تكون صحيحة أو خاطئة',
            
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.integer' => 'الكمية يجب أن تكون رقماً صحيحاً',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من أو تساوي صفر',
            
            'image.required' => 'صورة المنتج مطلوبة',
            'image.mimes' => 'صورة المنتج يجب أن تكون من نوع: png, jpg, jpeg',
            'image.max' => 'حجم صورة المنتج يجب ألا يزيد عن 2 ميجابايت',
            
            'images.*.mimes' => 'صور المعرض يجب أن تكون من نوع: png, jpg, jpeg',
            'images.*.max' => 'حجم كل صورة في المعرض يجب ألا يزيد عن 2 ميجابايت',
            
            'category_id.required' => 'يجب اختيار فئة للمنتج',
            'category_id.integer' => 'يجب اختيار فئة صحيحة',
            'category_id.exists' => 'الفئة المختارة غير موجودة',
            
            'brand_id.required' => 'يجب اختيار علامة تجارية للمنتج',
            'brand_id.integer' => 'يجب اختيار علامة تجارية صحيحة',
            'brand_id.exists' => 'العلامة التجارية المختارة غير موجودة',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'slug' => 'رابط المنتج',
            'short_description' => 'الوصف المختصر',
            'description' => 'الوصف التفصيلي',
            'regular_price' => 'السعر العادي',
            'sale_price' => 'سعر التخفيض',
            'SKU' => 'رمز المنتج',
            'stock_status' => 'حالة المخزون',
            'featured' => 'منتج مميز',
            'quantity' => 'الكمية',
            'image' => 'صورة المنتج',
            'images' => 'صور المعرض',
            'category_id' => 'الفئة',
            'brand_id' => 'العلامة التجارية',
        ];
    }
}
