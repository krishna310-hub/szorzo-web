@extends('backend.layouts.master')
@section('title', 'Edit Revenue Invoice')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="card"><div class="card-header"><h5 class="mb-0">Edit Revenue Invoice</h5></div>
        <div class="card-body"><form method="POST" action="{{ route('admin.revenues.update', $revenue) }}">@csrf @method('PUT') @include('backend.revenues.form')</form></div>
    </div>
</div></div></div>
@endsection
