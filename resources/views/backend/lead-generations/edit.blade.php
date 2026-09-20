@extends('backend.layouts.master')
@section('title', 'Edit Lead Generations')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Edit Lead Generations</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.lead-generations.update', $model->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.lead-generations.form')
            <div class="mt-4"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection