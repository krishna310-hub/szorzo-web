@extends('backend.layouts.master')
@section('title', 'View Revenue Invoice')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="d-flex justify-content-end gap-2 mb-3">
        @can('edit', \App\Models\Revenue::class)
            <a href="{{ route('admin.revenues.edit', $revenue) }}" class="btn btn-info">Edit</a>
        @endcan
        @can('download', \App\Models\Revenue::class)
            <a href="{{ route('admin.revenues.download', $revenue) }}" class="btn btn-success">Download PDF</a>
        @endcan
    </div>
    <div class="card"><div class="card-body bg-light overflow-auto invoice-preview-shell">@include('backend.revenues.invoice-content')</div></div>
</div></div></div>
@endsection
