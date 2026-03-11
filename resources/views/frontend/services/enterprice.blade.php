@extends('frontend.includes.master')
@section('content')
<!-- Page Header Start -->
    <div class="page-header-ef-bg">
        <div class="container">
            <div class="col-lg-12" style="margin-left: 750px; margin-top: 10px;">
                <div class="white-card">
                    <div class="section-title">
                        <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                            Enterprise <span> Formation</span>
                        </h1>
                        <div style="color: white;" class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                            <p style="font-weight: bold; font-size: x-large;">Establishing a Global Capability Center (GCC) in India enables organizations to harness a highly skilled talent pool, cost efficiencies, and a strong innovation ecosystem—driving digital transformation, AI adoption, and operational excellence.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- How It Work Section Start -->
    <div class="how-it-work bg-section mt-5 mb-5">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                     <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp" style="font-size: x-large;">Enabling Business Foundations Globally</h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">WE HELP IN SETTING UP YOUR <span style="font-weight: bold;">GCC</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-1.svg')}}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Setting Up Your Business</h3>
                            <p>Formation of Company as per the Business Needs i.e., LLP/Pvt Ltd Company/Ltd Company</p>
                            <p>Company Law Advisory</p>
                            <p>Secretarial Services & Compliance</p>
                            <p>Cross Border Taxation Advisory</p>
                            <p>FDI Investments with FEMA & RBI Regulation</p>
                            <p>Factory Set-Up Support</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-2.svg')}}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Societies and Trust formation </h3>
                            <p>Financial Accounting</p>
                            <p>Regulatory Compliance</p>
                            <p>Internal Audit</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-3.svg')}}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Accounting & Business Support Services</h3>
                            <p>Financial Accounting</p>
                            <p>Payroll Administration</p>
                            <p>Regulatory Compliance</p>
                            <p>Virtual CFO</p>
                            <p>Internal Control</p>
                            <p>Internal Audit</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-4.svg')}}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Appointment of Statutory Auditors & Tax Advisory </h3>
                            <p>Income Tax</p>
                            <p>Transfer Pricing</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- How It Work Section End -->
@endsection
