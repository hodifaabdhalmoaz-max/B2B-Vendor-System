@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>معلومات العلامة التجارية</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny">لوحة التحكم</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="{{route('admin.brands')}}">
                        <div class="text-tiny">العلامات التجارية</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">تعديل العلامة التجارية</div>
                </li>
            </ul>
        </div>
        <!-- new-category -->
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.brand.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{$brand->id}}"/>
                <fieldset class="name">
                    <div class="body-title">اسم العلامة التجارية <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="اسم العلامة التجارية" name="name" tabindex="0" value="{{$brand->name}}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset class="name">
                    <div class="body-title">رابط العلامة التجارية</div>
                    <input class="flex-grow" type="text" placeholder="رابط العلامة التجارية" name="slug" tabindex="0" value="{{$brand->slug}}" aria-required="false">
                </fieldset>
                @error('slug') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset>
                    <div class="body-title">رفع الصور <span class="tf-color-1">*</span>
                    </div>
                    <div class="upload-image flex-grow">
                        @if($brand->image)
                        <div class="item" id="imgpreview">
                            <img src="{{asset('uploads/brands')}}/{{$brand->image}}" class="effect8" alt="">
                        </div>
                        @endif
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="body-text">اسحب الصور هنا أو <span
                                        class="tf-color">انقر للتصفح</span></span>
                                <input type="file" id="myFile" name="image" accept="image/*">
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('image') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">حفظ التغييرات</button>
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
                const photoInp = $("#myFile");
                const [file] = this.files;
                if(file)
                {
                    $("#imgpreview img").attr('src',URL.createObjectURL(file));
                    $("#imgpreview").show();
                }
            });

            const slugInput = $("input[name='slug']");
            let slugTouched = false;

            slugInput.on("input", function(){
                slugTouched = true;
            });

            $("input[name='name']").on("input change", function(){
                if (!slugTouched || !slugInput.val()) {
                    slugInput.val(StringToSlug($(this).val()));
                }
            });

        });

        function StringToSlug(Text)
        {
            return Text.toLowerCase()
            .replace(/[^\p{L}\p{N}\s_-]+/gu, "")
            .replace(/[\s_]+/g, "-")
            .replace(/^-+|-+$/g, "");
        }
    </script>
@endpush
