@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>إضافة مقاس جديد</h3>
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
                    <a href="{{route('admin.sizes')}}">
                        <div class="text-tiny">المقاسات</div>
                    </a>
                </li>
                <li>
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <div class="text-tiny">مقاس جديد</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.size.store') }}" method="POST">
                @csrf
                <fieldset class="name">
                    <div class="body-title">اسم المقاس <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="مثال: وسط، كبير، 3-4 سنوات" name="name" tabindex="0" value="{{old('name')}}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                
                <fieldset class="name">
                    <div class="body-title">رمز المقاس (Code) <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="مثال: M, L, 3Y, 4Y" name="code" tabindex="0" value="{{old('code')}}" aria-required="true" required="">
                </fieldset>
                @error('code') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

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
