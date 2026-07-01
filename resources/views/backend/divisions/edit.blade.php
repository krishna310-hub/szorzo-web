@extends('backend.layouts.master')
@section('title', 'Edit Division')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card"><div class="card-header d-flex align-items-center"><h5 class="card-title mb-0 flex-grow-1">Edit Division</h5><a href="{{ route('admin.divisions.index') }}" class="btn btn-sm btn-light">Back</a></div><div class="card-body"><form action="{{ route('admin.divisions.update', $division->id) }}" method="POST">@csrf @method('PUT') @include('backend.divisions.form')</form></div></div></div></div></div></div></div>
@endsection
