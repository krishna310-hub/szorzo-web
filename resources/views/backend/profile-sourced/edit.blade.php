@extends('backend.layouts.master')
@section('title', 'Edit Sourced Profile')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="card"><div class="card-header"><h5 class="mb-0">Edit Sourced Profile</h5></div>
        <div class="card-body"><form method="POST" action="{{ route('admin.profile-sourced.update', $profileSourced) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('backend.profile-sourced.form')</form></div>
    </div>
</div></div></div>
@endsection
