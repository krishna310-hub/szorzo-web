@extends('backend.layouts.master')
@section('title', 'Create Services Offered')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Create Services Offered</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.services-offered.store') }}" method="POST">
            @csrf
            @include('backend.services-offered.form')
            <div class="mt-4"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection