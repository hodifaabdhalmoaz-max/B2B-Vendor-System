@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>معلومات الفئة</h3>
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
                    <a href="{{route('admin.categories')}}">
                        <div class="text-tiny">الفئات</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">تعديل الفئة</div>
                </li>
            </ul>
        </div>
        <!-- new-category -->
        <div class="wg-box">
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <strong>تعذر حفظ الفئة.</strong>
                    <ul style="margin: 8px 0 0; padding-inline-start: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="form-new-product form-style-1" action="{{ route('admin.category.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{$category->id}}"/>
                <fieldset class="name">
                    <div class="body-title">اسم الفئة <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="اسم الفئة" name="name" tabindex="0" value="{{ old('name', $category->name) }}" aria-required="true" required="">
                </fieldset>
                @error('name') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset class="name">
                    <div class="body-title">رابط الفئة</div>
                    <input class="flex-grow" type="text" placeholder="يتم توليده تلقائيا من الاسم عند تركه فارغا" name="slug" tabindex="0" value="{{ old('slug', $category->slug) }}">
                </fieldset>
                @error('slug') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset class="name">
                    <div class="body-title">الفئة الأب</div>
                    <div class="select flex-grow">
                        <select name="parent_id">
                            <option value="">بدون فئة أب</option>
                            @foreach($parentCategories as $parentCategory)
                                <option value="{{ $parentCategory->id }}" {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                                    {{ $parentCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>
                @error('parent_id') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                <fieldset>
                    <div class="body-title">رفع الصورة
                    </div>
                    <div class="upload-image flex-grow">
                        @if($category->image)
                        <div class="item" id="imgpreview">
                            <img src="{{asset('uploads/categories')}}/{{$category->image}}" class="effect8" alt="">
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

                @include('admin.partials.storefront-category-fields', ['category' => $category])

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

            $("input[name='name']").on("change", function(){
                $("input[name='slug']").val(StringToSlug($(this).val()));
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
