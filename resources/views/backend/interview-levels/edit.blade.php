@extends('backend.layouts.master')

@section('title', 'Edit Interview Level')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Interview Level</h4>
                            <a href="{{ route('admin.interview-levels.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.interview-levels.update', $interviewLevel->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.interview-levels.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
