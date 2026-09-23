@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>الكوبونات</h3>
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
                    <div class="text-tiny">الكوبونات</div>
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
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
                <a class="tf-button style-1 w208" href="{{ route('admin.coupon.add') }}"><i
                        class="icon-plus"></i>إضافة جديد</a>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    @if(Session::has('status'))
                    <p class="alert alert-success">{{Session::get('status')}}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">الرمز</th>
                                <th class="text-center">النوع</th>
                                <th class="text-center">القيمة</th>
                                <th class="text-center">قيمة السلة</th>
                                <th class="text-center">تاريخ الانتهاء</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                            <tr>
                                <td class="text-center">{{ $coupon->id }}</td>
                                <td class="text-center">{{ $coupon->code }}</td>
                                <td class="text-center">{{ $coupon->type}}</td>
                                <td class="text-center">{{ $coupon->value }}</td>
                                <td class="text-center">{{ $coupon->cart_value }}</td>
                                <td class="text-center">{{ $coupon->expiry_date }}</td>
                                <td class="text-center">
                                    <div class="list-icon-function justify-content-center">
                                        <a href="{{route('admin.coupon.edit',['id'=>$coupon->id])}}" title="تعديل">
                                            <div class="item edit">
                                                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </a>
                                        <form action="{{ route('admin.coupon.delete',['id'=>$coupon->id]) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <div class="item text-danger delete" title="حذف">
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
            </div>
            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{$coupons->links('pagination::bootstrap-5')}}
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
