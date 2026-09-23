@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>إضافة شريحة جديدة</h3>
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
                    <a href="{{route('admin.slides')}}">
                        <div class="text-tiny">الشرائح المتحركة</div>
                    </a>
                </li>
                <li>
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <div class="text-tiny">شريحة جديدة</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.slide.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <fieldset class="name">
                    <div class="body-title">العنوان الترويجي (Tagline)</div>
                    <input class="flex-grow" type="text" placeholder="مثال: وصل حديثاً، خصومات الصيف" name="tagline" value="{{old('tagline')}}">
                </fieldset>
                @error('tagline') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                
                <fieldset class="name">
                    <div class="body-title">العنوان الرئيسي (Title)</div>
                    <input class="flex-grow" type="text" placeholder="مثال: طفل سعيد، ملابس مريحة" name="title" value="{{old('title')}}">
                </fieldset>
                @error('title') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">العنوان المساعد (Subtitle)</div>
                    <input class="flex-grow" type="text" placeholder="مثال: مع متجرنا، لطفلك المميز" name="subtitle" value="{{old('subtitle')}}">
                </fieldset>
                @error('subtitle') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">رابط الشريحة (Link URL)</div>
                    <input class="flex-grow" type="text" placeholder="مثال: /shop, https://example.com" name="link" value="{{old('link')}}">
                </fieldset>
                @error('link') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset>
                    <div class="body-title">رفع الصورة <span class="tf-color-1">*</span></div>
                    <div class="upload-image flex-grow">
                        <div class="item" id="imgpreview" style="display:none">
                            <img src="" class="effect8" alt="" style="max-height: 200px; width: auto;">
                        </div>
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i data-lucide="upload-cloud" style="width: 24px; height: 24px;"></i>
                                </span>
                                <span class="body-text">اسحب الصورة هنا أو <span class="tf-color">انقر للتصفح</span></span>
                                <input type="file" id="myFile" name="image" accept="image/*" required="">
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('image') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الترتيب</div>
                    <input class="flex-grow" type="number" placeholder="ترتيب العرض (الافتراضي 0)" name="order" value="{{old('order', 0)}}">
                </fieldset>
                @error('order') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">الحالة <span class="tf-color-1">*</span></div>
                    <div class="select flex-grow">
                        <select name="status" required="">
                            <option value="1" {{old('status') === '1' || old('status') === null ? 'selected':''}}>نشط</option>
                            <option value="0" {{old('status') === '0' ? 'selected':''}}>غير نشط</option>
                        </select>
                    </div>
                </fieldset>
                @error('status') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">حفظ الشريحة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(function(){
            $("#myFile").on("change", function(e){
                const [file] = this.files;
                if(file)
                {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                }
            });
        });
    </script>
@endpush
