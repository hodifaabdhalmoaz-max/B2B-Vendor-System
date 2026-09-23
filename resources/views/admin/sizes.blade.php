@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>المقاسات</h3>
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
                    <div class="text-tiny">المقاسات</div>
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
                <a class="tf-button style-1 w208" href="{{route('admin.size.add')}}"><i data-lucide="plus" style="width: 16px; height: 16px;"></i>إضافة جديد</a>
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
                                <th>الوصف</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>المنتجات المرتبطة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sizes as $size)
                            <tr>
                                <td>{{$size->id}}</td>
                                <td>{{$size->name}}</td>
                                <td><code>{{$size->code}}</code></td>
                                <td>{{$size->description ?? '-'}}</td>
                                <td>
                                    @if($size->is_active)
                                    <span class="badge bg-success">نشط</span>
                                    @else
                                    <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{$size->order}}</td>
                                <td>{{$size->products_count}}</td>
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{route('admin.size.edit',['id'=>$size->id])}}">
                                            <div class="item edit">
                                                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </a>
                                        <form action="{{route('admin.size.delete',['id'=>$size->id])}}" method="POST">
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
                    {{ $sizes->links('pagination::bootstrap-5') }}
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
