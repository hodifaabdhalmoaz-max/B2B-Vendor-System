@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <!-- main-content-wrap -->
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>تعديل المنتج</h3>
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
                    <a href="{{route('admin.products')}}">
                        <div class="text-tiny">المنتجات</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">تعديل المنتج</div>
                </li>
            </ul>
        </div>
        <!-- form-add-product -->
        <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data" action="{{route('admin.product.update')}}">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{$product->id}}" />
            <div class="wg-box">
                <fieldset class="name">
                    <div class="body-title mb-10">اسم المنتج <span class="tf-color-1">*</span>
                    </div>
                    <input class="mb-10" type="text" placeholder="أدخل اسم المنتج" name="name" tabindex="0" value="{{ $product->name }}" aria-required="true" required="">
                    <div class="text-tiny">لا تتجاوز 100 حرف عند إدخال اسم المنتج.</div>
                </fieldset>
                @error('name') <span class="alret alert-denger text-center">{{$message}} @enderror

                <fieldset class="name">
                    <div class="body-title mb-10">الرابط</div>
                    <input class="mb-10" type="text" placeholder="أدخل رابط المنتج" name="slug" tabindex="0" value="{{$product->slug}}" aria-required="false">
                    <div class="text-tiny">لا تتجاوز 100 حرف عند إدخال رابط المنتج.</div>
                </fieldset>
                @error('slug') <span class="alret alert-denger text-center">{{$message}} @enderror

                <div class="gap22 cols">
                    <fieldset class="category">
                        <div class="body-title mb-10">الفئة <span class="tf-color-1">*</span>
                        </div>
                        <div class="select">
                            <select class="" name="category_id" required>
                                <option value="">اختر الفئة</option>
                                @foreach($categories as $category)
                                <option value="{{$category->id}}" {{$product->category_id == $category->id ? "selected":""}} >{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('category_id') <span class="alret alert-denger text-center">{{$message}} @enderror

                    <fieldset class="brand">
                        <div class="body-title mb-10">العلامة التجارية <span class="tf-color-1">*</span>
                        </div>
                        <div class="select">
                            <select class="" name="brand_id">
                                <option>اختر العلامة التجارية</option>
                                @foreach($brands as $brand)
                                <option value="{{$brand->id}}" {{$product->brand_id == $brand->id ? "selected":""}} >{{$brand->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('brand_id') <span class="alret alert-denger text-center">{{$message}} @enderror
                </div>

                <fieldset class="name">
                    <div class="body-title mb-10">فئات إضافية</div>
                    <div class="d-flex flex-wrap gap-3" style="gap: 12px; margin-top: 10px; flex-wrap: wrap;">
                        @foreach($categories as $category)
                            <label class="d-inline-flex align-items-center gap-2 px-3 py-2 border rounded cursor-pointer" style="cursor: pointer; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 6px 12px;">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @if(in_array($category->id, old('category_ids', $product->categories->pluck('id')->toArray()))) checked @endif style="width: 16px; height: 16px; margin: 0 5px;">
                                <span class="text-sm font-medium">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="text-tiny">اختياري. سيتم حفظ الفئة الأساسية تلقائيا ضمن فئات المنتج.</div>
                </fieldset>
                @error('category_ids') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                @error('category_ids.*') <span class="alert alert-danger text-center">{{$message}}</span> @enderror

                <fieldset class="shortdescription">
                    <div class="body-title mb-10">الوصف المختصر <span class="tf-color-1">*</span></div>
                    <textarea class="mb-10 ht-150" name="short_description" placeholder="الوصف المختصر" tabindex="0" aria-required="true" required="">{{$product->short_description}}</textarea>
                    <div class="text-tiny">لا تتجاوز 100 حرف عند إدخال الوصف المختصر.</div>
                </fieldset>
                @error('short_description') <span class="alret alert-denger text-center">{{$message}} @enderror

                <fieldset class="description">
                    <div class="body-title mb-10">الوصف <span class="tf-color-1">*</span>
                    </div>
                    <textarea class="mb-10" name="description" placeholder="الوصف" tabindex="0" aria-required="true" required="">{{$product->description}}</textarea>
                    <div class="text-tiny">لا تتجاوز 500 حرف عند إدخال الوصف.</div>
                </fieldset>
                @error('description') <span class="alret alert-denger text-center">{{$message}} @enderror
            </div>
            <div class="wg-box">
                <fieldset>
                    <div class="body-title">رفع الصور <span class="tf-color-1">*</span>
                    </div>
                    <div class="upload-image flex-grow">
                        @if($product->image)
                        <div class="item" id="imgpreview">
                            <img src="{{asset('uploads/products')}}/{{$product->image}}" class="effect8" alt="{{$product->name}}">
                        </div>
                        @endif
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="body-text">اسحب الصور هنا أو <span class="tf-color">انقر للتصفح</span></span>
                                <input type="file" id="myFile" name="image" accept="image/*">
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('image') <span class="alret alert-denger text-center">{{$message}} @enderror

                <fieldset>
                    <div class="body-title mb-10">رفع صور المعرض</div>
                    <div class="upload-image mb-16">
                        @if($product->images)
                            @foreach(explode(',',$product->images) as $img)
                            <div class="item gitems">
                                <img src="{{asset('uploads/products')}}/{{trim($img)}}" alt="">
                            </div>
                            @endforeach
                        @endif
                        <div id="galUpload" class="item up-load">
                            <label class="uploadfile" for="gFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="text-tiny">اسحب الصور هنا أو <span
                                        class="tf-color">انقر للتصفح</span></span>
                                <input type="file" id="gFile" name="images[]" accept="image/*" multiple="">
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('images') <span class="alret alert-denger text-center">{{$message}} @enderror

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">السعر العادي <span
                                class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="أدخل السعر العادي" name="regular_price" tabindex="0" value="{{$product->regular_price}}" aria-required="true" required="">
                    </fieldset>
                    @error('regular_price') <span class="alret alert-denger text-center">{{$message}} @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">سعر التخفيض <span
                                class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="أدخل سعر التخفيض" name="sale_price" tabindex="0" value="{{$product->sale_price}}" aria-required="true" required="">
                    </fieldset>
                    @error('sale_price') <span class="alret alert-denger text-center">{{$message}} @enderror

                </div>


                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">رمز المنتج
                        </div>
                        <input class="mb-10" type="text" placeholder="أدخل رمز المنتج" name="SKU" tabindex="0" value="{{$product->SKU}}" aria-required="false">
                    </fieldset>
                    @error('SKU') <span class="alret alert-denger text-center">{{$message}} @enderror
                    <fieldset class="name">
                        <div class="body-title mb-10">الكمية <span class="tf-color-1">*</span>
                        </div>
                        <input class="mb-10" type="text" placeholder="أدخل الكمية" name="quantity" tabindex="0" value="{{$product->quantity}}" aria-required="true"
                            required="">
                    </fieldset>
                    @error('quantity') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">الألوان المتاحة للمنتج</div>
                        <div class="d-flex flex-wrap gap-3" style="gap: 12px; margin-top: 10px; flex-wrap: wrap;">
                            @foreach($colors as $color)
                            @php($colorImages = $product->colorImages->where('color_id', $color->id))
                            <div style="width: 240px; background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 10px;">
                                <label class="d-inline-flex align-items-center gap-2 cursor-pointer" style="cursor: pointer;">
                                    <input type="checkbox" name="colors[]" value="{{$color->id}}" data-product-color-checkbox @if(in_array($color->id, old('colors', $product->colors->pluck('id')->toArray()))) checked @endif style="width: 16px; height: 16px; margin: 0 5px;">
                                    <span style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: {{$color->hex_code ?? '#ccc'}}; border: 1px solid #bbb; margin: 0 5px;"></span>
                                    <span class="text-sm font-medium">{{$color->name}}</span>
                                </label>
                                @if($colorImages->isNotEmpty())
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                                        @foreach($colorImages as $image)
                                            <label style="position: relative; display: inline-block;">
                                                <img src="{{ asset($image->image_path) }}" alt="{{$color->name}}" style="width: 48px; height: 58px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                                <input type="checkbox" name="remove_color_images[]" value="{{$image->id}}" title="حذف" style="position: absolute; top: 2px; right: 2px;">
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                                <div style="margin-top: 8px;">
                                    <span class="text-tiny d-block mb-1">إضافة صور لهذا اللون</span>
                                    <input type="file" name="color_images[{{$color->id}}][]" accept="image/*" multiple data-product-color-images style="width: 100%; font-size: 12px;">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('colors') <span class="alert alert-danger text-center mt-2 d-block">{{$message}}</span> @enderror
                        @error('color_images.*.*') <span class="alert alert-danger text-center mt-2 d-block">{{$message}}</span> @enderror
                    </fieldset>

                    <fieldset class="name">
                        <div class="body-title mb-10">المقاسات المتاحة للمنتج</div>
                        <div class="d-flex flex-wrap gap-3" style="gap: 12px; margin-top: 10px; flex-wrap: wrap;">
                            @foreach($sizes as $size)
                            <label class="d-inline-flex align-items-center gap-2 px-3 py-2 border rounded cursor-pointer" style="cursor: pointer; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 6px 12px;">
                                <input type="checkbox" name="sizes[]" value="{{$size->id}}" @if(in_array($size->id, old('sizes', $product->sizes->pluck('id')->toArray()))) checked @endif style="width: 16px; height: 16px; margin: 0 5px;">
                                <span class="text-sm font-medium">{{$size->name}} ({{$size->code}})</span>
                            </label>
                            @endforeach
                        </div>
                        @error('sizes') <span class="alert alert-danger text-center mt-2 d-block">{{$message}}</span> @enderror
                    </fieldset>
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">الأبعاد</div>
                        <input class="mb-10" type="text" placeholder="أدخل أبعاد المنتج (اختياري)" name="dimensions" tabindex="0" value="{{$product->dimensions}}" aria-required="false">
                    </fieldset>
                    @error('dimensions') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                    <fieldset class="name">
                        <div class="body-title mb-10">الوزن</div>
                        <input class="mb-10" type="text" placeholder="أدخل وزن المنتج (اختياري)" name="weight" tabindex="0" value="{{$product->weight}}" aria-required="false">
                    </fieldset>
                    @error('weight') <span class="alert alert-danger text-center">{{$message}}</span> @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">المخزون</div>
                        <div class="select mb-10">
                            <select class="" name="stock_status">
                                <option value="instock" {{$product->stock_status == "instock" ? "selected":""}} >متوفر</option>
                                <option value="outofstock" {{$product->stock_status == "outofstock" ? "selected":""}} >غير متوفر</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('stock_status') <span class="alret alert-denger text-center">{{$message}} @enderror
                    <fieldset class="name">
                        <div class="body-title mb-10">مميز</div>
                        <div class="select mb-10">
                            <select class="" name="featured">
                                <option value="0" {{$product->featured == "0" ? "selected":""}} >لا</option>
                                <option value="1" {{$product->featured == "1" ? "selected":""}} >نعم</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('featured') <span class="alret alert-denger text-center">{{$message}} @enderror
                </div>
                
                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">إضافة للعروض</div>
                        <div class="select mb-10">
                            <select class="" name="is_offer">
                                <option value="0" {{$product->is_offer == "0" ? "selected":""}} >لا</option>
                                <option value="1" {{$product->is_offer == "1" ? "selected":""}} >نعم</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('is_offer') <span class="alret alert-denger text-center">{{$message}} @enderror
                </div>
                @include('admin.partials.storefront-product-fields', ['product' => $product])
                <div class="cols gap10">
                    <button class="tf-button w-full" type="submit">تحديث المنتج</button>
                </div>
            </div>
        </form>
        <!-- /form-add-product -->
    </div>
    <!-- /main-content-wrap -->
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

            $("#gFile").on("change", function(e){
                const photoInp = $("#gFile");
                const gphotos = this.files;
                $.each(gphotos, function(key,val){
                    $("#galUpload").prepend('<div class="item gitems"><img src="' + URL.createObjectURL(val) + '" /></div>');
                });
            });

            $("[data-product-color-images]").on("change", function(){
                if (this.files && this.files.length > 0) {
                    $(this).closest("div").parent().find("[data-product-color-checkbox]").prop("checked", true);
                }
            });

            const slugInput = $("input[name='slug']");
            const skuInput = $("input[name='SKU']");
            let slugTouched = false;
            let skuTouched = false;

            slugInput.on("input", function(){
                slugTouched = true;
            });

            skuInput.on("input", function(){
                skuTouched = true;
            });

            $("input[name='name']").on("input change", function(){
                if (!slugTouched || !slugInput.val()) {
                    slugInput.val(StringToSlug($(this).val()));
                }

                if (!skuTouched || !skuInput.val()) {
                    skuInput.val(StringToSku($(this).val()));
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

        function StringToSku(Text)
        {
            return Text.toUpperCase()
            .replace(/[^\p{L}\p{N}]+/gu, "-")
            .replace(/^-+|-+$/g, "")
            .slice(0, 64);
        }
    </script>
@endpush
