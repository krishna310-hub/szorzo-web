@extends('backend.layouts.master')
@section('title', 'Edit Business Intelligences')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Edit Business Intelligences</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.business-intelligences.update', $model->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.business-intelligences.form')
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.business-intelligences.index') }}" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection