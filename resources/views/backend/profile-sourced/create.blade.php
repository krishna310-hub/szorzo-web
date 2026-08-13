@extends('backend.layouts.master')
@section('title', 'Add Sourced Profile')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="card"><div class="card-header"><h5 class="mb-0">Add Sourced Profile</h5></div>
        <div class="card-body"><form method="POST" action="{{ route('admin.profile-sourced.store') }}" enctype="multipart/form-data">@csrf @include('backend.profile-sourced.form')</form></div>
    </div>
</div></div></div>
@endsection
