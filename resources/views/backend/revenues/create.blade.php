@extends('backend.layouts.master')
@section('title', 'Generate Revenue Invoice')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="card"><div class="card-header"><h5 class="mb-0">Generate Revenue Invoice</h5></div>
        <div class="card-body">
            @if($candidates->isEmpty())<div class="alert alert-info">No uninvoiced candidates are currently at offer accepted level (ID 30).</div>@endif
            <form method="POST" action="{{ route('admin.revenues.store') }}">@csrf @include('backend.revenues.form')</form>
        </div>
    </div>
</div></div></div>
@endsection
