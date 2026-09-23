@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>أماكن ظهور الإعلانات</h3>
            <a class="tf-button style-1 w208" href="{{ route('admin.marketing.placements.create') }}">إضافة مكان ظهور</a>
        </div>

        <div class="wg-box">
            @if(Session::has('status'))
                <p class="alert alert-success">{{ Session::get('status') }}</p>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Key</th>
                            <th>الاسم</th>
                            <th>المقاس المقترح</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($placements as $placement)
                            <tr>
                                <td>{{ $placement->id }}</td>
                                <td><code>{{ $placement->key }}</code></td>
                                <td>{{ $placement->name }}</td>
                                <td>{{ $placement->recommended_size ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $placement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $placement->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="list-icon-function">
                                        <a href="{{ route('admin.marketing.placements.edit', $placement) }}">
                                            <div class="item edit"><i class="icon-edit-3"></i></div>
                                        </a>
                                        <form action="{{ route('admin.marketing.placements.destroy', $placement) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="item text-danger delete" type="submit" onclick="return confirm('حذف مكان الظهور؟')">
                                                <i class="icon-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">لا توجد أماكن ظهور بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $placements->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
