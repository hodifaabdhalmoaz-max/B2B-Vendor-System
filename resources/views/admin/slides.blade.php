@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>الشرائح المتحركة</h3>
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
                    <div class="text-tiny">الشرائح المتحركة</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                </div>
                <a class="tf-button style-1 w208" href="{{route('admin.slide.add')}}"><i data-lucide="plus" style="width: 16px; height: 16px;"></i>إضافة شريحة جديدة</a>
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
                                <th>الصورة</th>
                                <th>العنوان الترويجي</th>
                                <th>العنوان الرئيسي</th>
                                <th>العنوان المساعد</th>
                                <th>الرابط</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($slides as $slide)
                            <tr>
                                <td>{{$slide->id}}</td>
                                <td>
                                    @if($slide->image)
                                    <img src="{{ asset('uploads/slides/' . $slide->image) }}" alt="" style="width: 80px; height: auto; border-radius: 8px; border: 1px solid #ddd;">
                                    @else
                                    <span class="text-muted">لا توجد صورة</span>
                                    @endif
                                </td>
                                <td>{{$slide->tagline}}</td>
                                <td>{{$slide->title}}</td>
                                <td>{{$slide->subtitle}}</td>
                                <td><code>{{$slide->link}}</code></td>
                                <td>
                                    @if($slide->status)
                                    <span class="badge bg-success">نشط</span>
                                    @else
                                    <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{$slide->order}}</td>
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{route('admin.slide.edit',['id'=>$slide->id])}}">
                                            <div class="item edit">
                                                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </a>
                                        <form action="{{route('admin.slide.delete',['id'=>$slide->id])}}" method="POST">
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
                    {{ $slides->links('pagination::bootstrap-5') }}
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
                    text: "هل تريد حذف هذه الشريحة؟",
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
