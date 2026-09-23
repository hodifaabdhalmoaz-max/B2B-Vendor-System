<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Handle a failed validation attempt.
     * Redirect to checkout page explicitly to avoid 404/419 errors.
     */
    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
            ->redirectTo(route('cart.checkout'))
            ->errorBag($this->errorBag);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'mode' => 'required|in:card,paypal,cod,bank_transfer,e_wallet,installments',
            'bank_account' => 'required_if:mode,bank_transfer|in:kuraimi_yer,kuraimi_sar,kuraimi_usd'
        ];

        // If user doesn't have a default address, require address fields
        if (!$this->hasDefaultAddress()) {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:100',
                'phone' => 'required|numeric|digits:9',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'locality' => 'required|string|max:100',
                'landmark' => 'required|string|max:100',
            ]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mode.required' => 'يرجى اختيار طريقة الدفع',
            'mode.in' => 'طريقة الدفع المختارة غير صحيحة',
            'bank_account.required_if' => 'يرجى اختيار الحساب البنكي للإيداع عند اختيار الدفع بالتحويل البنكي',
            'bank_account.in' => 'الحساب البنكي المختار غير صحيح',
            
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصاً',
            'name.max' => 'الاسم يجب ألا يزيد عن 100 حرف',
            
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.numeric' => 'رقم الهاتف يجب أن يكون رقماً',
            'phone.digits' => 'رقم الهاتف يجب أن يكون 9 أرقام',
            
            'zip.required' => 'الرمز البريدي مطلوب',
            'zip.numeric' => 'الرمز البريدي يجب أن يكون رقماً',
            'zip.digits' => 'الرمز البريدي يجب أن يكون 6 أرقام',
            
            'state.required' => 'المحافظة مطلوبة',
            'state.string' => 'المحافظة يجب أن تكون نصاً',
            'state.max' => 'المحافظة يجب ألا تزيد عن 100 حرف',
            
            'city.required' => 'المدينة مطلوبة',
            'city.string' => 'المدينة يجب أن تكون نصاً',
            'city.max' => 'المدينة يجب ألا تزيد عن 100 حرف',
            
            'address.required' => 'العنوان مطلوب',
            'address.string' => 'العنوان يجب أن يكون نصاً',
            'address.max' => 'العنوان يجب ألا يزيد عن 255 حرف',
            
            'locality.required' => 'المنطقة مطلوبة',
            'locality.string' => 'المنطقة يجب أن تكون نصاً',
            'locality.max' => 'المنطقة يجب ألا تزيد عن 100 حرف',
            
            'landmark.required' => 'المعلم المميز مطلوب',
            'landmark.string' => 'المعلم المميز يجب أن يكون نصاً',
            'landmark.max' => 'المعلم المميز يجب ألا يزيد عن 100 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mode' => 'طريقة الدفع',
            'bank_account' => 'الحساب البنكي',
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'zip' => 'الرمز البريدي',
            'state' => 'المحافظة',
            'city' => 'المدينة',
            'address' => 'العنوان',
            'locality' => 'المنطقة',
            'landmark' => 'المعلم المميز',
        ];
    }

    /**
     * Check if user has a default address
     */
    protected function hasDefaultAddress(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return \App\Models\Address::where('user_id', auth()->id())
            ->where('isdefault', true)
            ->exists();
    }

    /**
     * Get the validated data with additional processing
     */
    public function getOrderData(): array
    {
        $validated = $this->validated();
        
        // If user has default address, we only need payment info
        if ($this->hasDefaultAddress()) {
            return array_intersect_key($validated, array_flip(['mode', 'bank_account']));
        }

        // Return all validated data including address fields
        return $validated;
    }
}
