@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'عناوين التوصيل', 'description' => 'إدارة عناوين الشحن الخاصة بك لتجربة تسوق أسرع'])
    <section class="my-account container">

        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle me-3 fs-4"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">عناوين الشحن الخاصة بك</h5>
                        <a href="{{ route('user.address.add') }}" class="btn btn-golden rounded-pill px-4 py-2 shadow-sm">
                            <i class="bi bi-plus me-1"></i> إضافة عنوان جديد
                        </a>
                    </div>

                    @if($addresses->count() > 0)
                        <div class="row g-4">
                            @foreach($addresses as $address)
                                <div class="col-md-6">
                                    <div class="card address-card h-100 border-0 shadow-sm rounded-4 bg-white transition {{ $address->isdefault ? 'border-primary-left' : '' }}">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="p-2 me-3 d-flex align-items-center justify-content-center icon-golden-box" style="width: 45px; height: 45px; border-radius: 8px;">
                                                        @if($address->type == 'home')
                                                            <i class="bi bi-house fs-5"></i>
                                                        @elseif($address->type == 'office')
                                                            <i class="bi bi-briefcase fs-5"></i>
                                                        @else
                                                            <i class="bi bi-geo-alt fs-5"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-capitalize">{{ $address->type == 'home' ? 'المنزل' : ($address->type == 'office' ? 'العمل' : 'أخرى') }}</h6>
                                                        @if($address->isdefault)
                                                            <span class="badge rounded-pill small px-2 py-1 mt-1" style="font-size: 10px; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">افتراضي</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-icon dropdown-trigger-btn" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                                        <li><a class="dropdown-item py-2" href="{{ route('user.address.edit', $address->id) }}"><i class="bi bi-pencil me-2"></i> تعديل</a></li>
                                                        @if(!$address->isdefault)
                                                            <li>
                                                                <form action="{{ route('user.address.default', $address->id) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item py-2"><i class="bi bi-check-circle me-2"></i> تعيين كافتراضي</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider opacity-50"></li>
                                                        <li>
                                                            <form action="{{ route('user.address.delete', $address->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العنوان؟')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> حذف</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="address-details">
                                                <p class="fw-bold text-dark mb-1">{{ $address->name }}</p>
                                                <p class="text-muted small mb-3"><i class="bi bi-telephone me-1"></i> {{ $address->phone }}</p>
                                                
                                                <div class="bg-light rounded-3 p-3 mb-0 address-details-box">
                                                    <p class="text-dark small mb-1">{{ $address->address }}</p>
                                                    <p class="text-dark small mb-1">{{ $address->locality }}, {{ $address->landmark }}</p>
                                                    <p class="text-dark small mb-0 fw-bold">{{ $address->city }}, {{ $address->state }}, {{ $address->country }} - {{ $address->zip }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border-dashed">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                <i class="bi bi-geo-alt display-4 text-muted"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">ليس لديك أي عناوين مسجلة</h4>
                            <p class="text-muted mb-4 px-3">قم بإضافة عناوين الشحن الخاصة بك لتسهيل عملية الشراء مستقبلاً.</p>
                            <a href="{{ route('user.address.add') }}" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">إضافة عنوانك الأول</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .address-card {
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9 !important;
    }
    .address-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
        border-color: #f0c14b !important;
    }
    .border-primary-left {
        border-right: 4px solid #f0c14b !important; /* Right side for RTL */
    }
    [dir="ltr"] .border-primary-left {
        border-left: 4px solid #f0c14b !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    
    .btn-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-title {
        font-weight: 800;
        position: relative;
        padding-bottom: 10px;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 4px;
        background: #f0c14b;
        border-radius: 2px;
    }
    
    .border-dashed {
        border: 2px dashed #e2e8f0 !important;
    }
    
    .btn-golden {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%) !important;
        border: none !important;
        color: white !important;
        transition: all 0.3s ease;
    }
    .btn-golden:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(240, 193, 75, 0.3) !important;
        color: white !important;
    }
    .icon-golden-box {
        background: rgba(240, 193, 75, 0.1);
        color: #f0c14b;
        transition: all 0.3s ease;
    }
    .address-card:hover .icon-golden-box {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(240, 193, 75, 0.3);
    }
    .dropdown-trigger-btn {
        background-color: #f8fafc;
        color: #475569;
        transition: all 0.3s ease;
    }
    .dropdown-trigger-btn:hover, .dropdown-trigger-btn:focus {
        background-color: rgba(240, 193, 75, 0.1);
        color: #f0c14b;
        transform: rotate(90deg);
    }
    .address-details-box {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .address-card:hover .address-details-box {
        background-color: rgba(240, 193, 75, 0.03) !important;
        border-color: rgba(240, 193, 75, 0.2);
    }
    
    .dropdown-item:active {
        background-color: #f0c14b;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        float: right;
        padding-left: 0.5rem;
    }
    
    .transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
@endsection
