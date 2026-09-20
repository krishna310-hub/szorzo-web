@extends('backend.layouts.master')
@section('title', 'Edit Services Offered')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Edit Services Offered</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.services-offered.update', $model->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.services-offered.form')
            <div class="mt-4"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection