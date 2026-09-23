@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>تعديل اللون</h3>
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
                    <div class="text-tiny">تعديل اللون</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.color.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{$color->id}}">
                
                <fieldset class="name">
                    <div class="body-title">اسم اللون <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="اسم اللون" name="name" tabindex="0" value="{{old('name', $color->name)}}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                
                <fieldset class="name">
                    <div class="body-title">رمز اللون (Code) <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="رمز اللون" name="code" tabindex="0" value="{{old('code', $color->code)}}" aria-required="true" required="">
                </fieldset>
                @error('code') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">كود اللون المرئي (Hex Code) <span class="tf-color-1">*</span></div>
                    <div class="d-flex align-items-center gap-3 flex-grow" style="gap: 15px;">
                        <input class="flex-grow" type="text" id="hex_code" placeholder="#ff0000" name="hex_code" value="{{old('hex_code', $color->hex_code)}}" required="" style="max-width: 200px;">
                        <input type="color" id="color_picker" value="{{old('hex_code', $color->hex_code)}}" style="width: 50px; height: 45px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; padding: 2px;">
                    </div>
                </fieldset>
                @error('hex_code') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الوصف (اختياري)</div>
                    <textarea class="flex-grow" placeholder="الوصف" name="description" tabindex="0">{{old('description', $color->description)}}</textarea>
                </fieldset>
                @error('description') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الترتيب</div>
                    <input class="flex-grow" type="number" placeholder="الترتيب" name="order" tabindex="0" value="{{old('order', $color->order)}}">
                </fieldset>
                @error('order') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">نشط <span class="tf-color-1">*</span></div>
                    <div class="select flex-grow">
                        <select name="is_active" required="">
                            <option value="1" {{old('is_active', $color->is_active) ? 'selected':''}}>نعم</option>
                            <option value="0" {{!old('is_active', $color->is_active) ? 'selected':''}}>لا</option>
                        </select>
                    </div>
                </fieldset>
                @error('is_active') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">تحديث</button>
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
    });
</script>
@endpush
