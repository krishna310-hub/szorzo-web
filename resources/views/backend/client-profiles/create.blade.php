@extends('backend.layouts.master')
@section('title', 'Create Client Profiles')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Create Client Profiles</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.client-profiles.store') }}" method="POST">
            @csrf
            @include('backend.client-profiles.form')
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.client-profiles.index') }}" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    </div>
</div></div></div></div></div></div>
@endsection