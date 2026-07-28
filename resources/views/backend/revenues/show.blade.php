@extends('backend.layouts.master')
@section('title', 'View Revenue Invoice')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('admin.revenues.edit', $revenue) }}" class="btn btn-info">Edit</a>
        <a href="{{ route('admin.revenues.download', $revenue) }}" class="btn btn-success">Download PDF</a>
    </div>
    <div class="card"><div class="card-body bg-light">@include('backend.revenues.invoice')</div></div>
</div></div></div>
@endsection
