@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>إضافة حملة إعلانية</h3>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.campaigns.index') }}">كل الحملات</a>
        </div>

        @include('admin.marketing.campaigns.partials.form', [
            'campaign' => null,
            'action' => route('admin.marketing.campaigns.store'),
            'method' => 'POST',
        ])
    </div>
</div>
@endsection
