@extends('backend.layouts.master')
@section('title', 'Create Business Intelligences')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Create Business Intelligences</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.business-intelligences.store') }}" method="POST">
            @csrf
            @include('backend.business-intelligences.form')
            <div class="mt-4"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection