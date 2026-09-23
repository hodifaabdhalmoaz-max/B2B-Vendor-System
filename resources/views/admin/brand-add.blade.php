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
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <a href="{{route('admin.brands')}}">
                        <div class="text-tiny">العلامات التجارية</div>
                    </a>
                </li>
                <li>
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </li>
                <li>
                    <div class="text-tiny">علامة تجارية جديدة</div>
                </li>
            </ul>
        </div>
        <!-- new-category -->
        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.brand.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <fieldset class="name">
                    <div class="body-title">اسم العلامة التجارية <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="اسم العلامة التجارية" name="name" tabindex="0" value="{{old('name')}}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset class="name">
                    <div class="body-title">رابط العلامة التجارية</div>
                    <input class="flex-grow" type="text" placeholder="رابط العلامة التجارية" name="slug" tabindex="0" value="{{old('slug')}}" aria-required="false">
                </fieldset>
                @error('slug') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset>
                    <div class="body-title">رفع الصور <span class="tf-color-1">*</span>
                    </div>
                    <div class="upload-image flex-grow">
                        <div class="item" id="imgpreview" style="display:none">
                            <img src="upload-1.html" class="effect8" alt="">
                        </div>
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i data-lucide="upload-cloud" style="width: 24px; height: 24px;"></i>
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
                    <button class="tf-button w208" type="submit">حفظ</button>
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
