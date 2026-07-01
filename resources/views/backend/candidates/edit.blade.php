@extends('backend.layouts.master')

@section('title', 'Edit Candidate')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Candidate</h4>
                            <a href="{{ route('admin.candidates.index') }}" class="btn btn-sm btn-light">Back</a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.candidates.update', $candidate->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('backend.candidates.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
