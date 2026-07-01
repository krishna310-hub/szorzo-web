@extends('backend.layouts.master')

@section('title', 'Edit Mode')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Mode</h4>
                            <a href="{{ route('admin.modes.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.modes.update', $mode->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.modes.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
