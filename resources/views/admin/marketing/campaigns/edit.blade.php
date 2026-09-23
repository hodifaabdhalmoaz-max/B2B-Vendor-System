@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>تعديل حملة إعلانية</h3>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.campaigns.index') }}">كل الحملات</a>
        </div>

        @if(Session::has('status'))
            <p class="alert alert-success">{{ Session::get('status') }}</p>
        @endif

        @include('admin.marketing.campaigns.partials.form', [
            'campaign' => $campaign,
            'action' => route('admin.marketing.campaigns.update', $campaign),
            'method' => 'PUT',
        ])
    </div>
</div>
@endsection
