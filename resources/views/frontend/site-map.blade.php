@extends('frontend.includes.master')
@section('title', 'Sitemap | SZORZO')
@section('meta_description', 'Browse SZORZO company information, enterprise solutions, and IT services.')
@section('content')
<section class="page-header-about-us-bg">
    <div class="container py-5">
        <div class="white-card p-4 p-lg-5">
            <div class="section-title mb-4">
                <h1>Website <span>Sitemap</span></h1>
                <p>Explore SZORZO company information, solutions, and services.</p>
            </div>
            <div class="row g-4">
                @foreach($siteMap as $section => $links)
                    <div class="col-md-6 col-lg-3">
                        <h2 class="h5 mb-3">{{ $section }}</h2>
                        <ul class="list-unstyled">
                            @foreach($links as $link)
                                <li class="mb-2"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
