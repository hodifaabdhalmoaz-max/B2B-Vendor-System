@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>الألوان</h3>
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
                    <div class="text-tiny">الألوان</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search">
                        <fieldset class="name">
                            <input type="text" placeholder="البحث هنا..." class="" name="name"
                                tabindex="2" value="" aria-required="true" required="">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i data-lucide="search" style="width: 16px; height: 16px;"></i></button>
                        </div>
                    </form>
                </div>
                <a class="tf-button style-1 w208" href="{{route('admin.color.add')}}"><i data-lucide="plus" style="width: 16px; height: 16px;"></i>إضافة جديد</a>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    @if(Session::has('status'))
                    <p class="alert alert-success">{{Session::get('status')}}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الرمز</th>
                                <th>اللون المرئي</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>المنتجات المرتبطة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($colors as $color)
                            <tr>
                                <td>{{$color->id}}</td>
                                <td>{{$color->name}}</td>
                                <td><code>{{$color->code}}</code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="display: inline-block; width: 25px; height: 25px; border-radius: 50%; background-color: {{$color->hex_code ?? '#ccc'}}; border: 1px solid #aaa;"></span>
                                        <span>{{$color->hex_code}}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($color->is_active)
                                    <span class="badge bg-success">نشط</span>
                                    @else
                                    <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{$color->order}}</td>
                                <td>{{$color->products_count}}</td>
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{route('admin.color.edit',['id'=>$color->id])}}">
                                            <div class="item edit">
                                                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </a>
                                        <form action="{{route('admin.color.delete',['id'=>$color->id])}}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="item text-danger delete">
                                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $colors->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(function(){
            $('.delete').on('click',function(e){
                e.preventDefault();
                var form = $(this).closest('form');
                swal({
                    title: "هل أنت متأكد؟",
                    text: "هل تريد حذف هذا السجل؟",
                    type: "warning",
                    buttons: ["لا", "نعم"],
                    confirmButtonColor: "#dc3545"
                }).then(function(result){
                    if(result){
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
