@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>إضافة لون جديد</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny">لوحة التحكم</div>
                    </a>
                </li>
                <li>
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <a href="{{route('admin.colors')}}">
                        <div class="text-tiny">الألوان</div>
                    </a>
                </li>
                <li>
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <div class="text-tiny">لون جديد</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.color.store') }}" method="POST">
                @csrf
                <fieldset class="name">
                    <div class="body-title">اسم اللون <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="مثال: أحمر، أزرق، وردي" name="name" tabindex="0" value="{{old('name')}}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                
                <fieldset class="name">
                    <div class="body-title">رمز اللون (Code) <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="مثال: red, blue, pink" name="code" tabindex="0" value="{{old('code')}}" aria-required="true" required="">
                </fieldset>
                @error('code') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">كود اللون المرئي (Hex Code) <span class="tf-color-1">*</span></div>
                    <div class="d-flex align-items-center gap-3 flex-grow" style="gap: 15px;">
                        <input class="flex-grow" type="text" id="hex_code" placeholder="#ff0000" name="hex_code" value="{{old('hex_code', '#ff0000')}}" required="" style="max-width: 200px;">
                        <input type="color" id="color_picker" value="{{old('hex_code', '#ff0000')}}" style="width: 50px; height: 45px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; padding: 2px;">
                    </div>
                </fieldset>
                @error('hex_code') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الوصف (اختياري)</div>
                    <textarea class="flex-grow" placeholder="الوصف" name="description" tabindex="0">{{old('description')}}</textarea>
                </fieldset>
                @error('description') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الترتيب</div>
                    <input class="flex-grow" type="number" placeholder="ترتيب العرض (الافتراضي 0)" name="order" tabindex="0" value="{{old('order', 0)}}">
                </fieldset>
                @error('order') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">نشط <span class="tf-color-1">*</span></div>
                    <div class="select flex-grow">
                        <select name="is_active" required="">
                            <option value="1" {{old('is_active') == '1' ? 'selected':''}}>نعم</option>
                            <option value="0" {{old('is_active') == '0' ? 'selected':''}}>لا</option>
                        </select>
                    </div>
                </fieldset>
                @error('is_active') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hexInput = document.getElementById('hex_code');
        const colorPicker = document.getElementById('color_picker');

        // Sync color picker with hex input
        hexInput.addEventListener('input', function() {
            let val = hexInput.value;
            if (val.match(/^#[0-9a-fA-F]{6}$/)) {
                colorPicker.value = val;
            }
        });

        // Sync hex input with color picker
        colorPicker.addEventListener('input', function() {
            hexInput.value = colorPicker.value;
        });

        // Auto-slugify code name from English input if possible
        const nameInput = document.querySelector("input[name='name']");
        const codeInput = document.querySelector("input[name='code']");
        
        nameInput.addEventListener('input', function() {
            // Only suggest if code input is empty
            if (codeInput.value === '') {
                let suggested = nameInput.value.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .trim()
                    .replace(/[-\s]+/g, '-');
                codeInput.value = suggested;
            }
        });
    });
</script>
@endpush
