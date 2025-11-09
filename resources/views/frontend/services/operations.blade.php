@extends('frontend.includes.master')
@section('content')
<!-- Page Header Start -->
    <div class="page-header-oi-bg">
        <div class="container">
            <div class="col-lg-12" style="margin-top: -120px; margin-left: -110px;">
                <div class="white-card">
                    <div class="section-title">
                        <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                            Operation & <br>Infrastructure<span> Offerings</span>
                        </h1>
                        <p style="font-weight: bold; font-size: x-large; text-align: left;" class="wow fadeInUp">SZORZO Assesses organizational
                            structure, talent, and capabilities <br> to
                            optimize performance, scalability,
                            and readiness for growth,
                            transformation, or market
                            expansion.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- How It Work Section Start -->
    <div class="how-it-work bg-section mt-3 mb-5">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp" style="font-size: x-large;">Driving Efficiency Through Intelligent Infrastructure</h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">OPERATION & INFRASTRUCTURE <span style="font-weight: bold;">OFFERINGS</span></h2>
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
                        <div class="work-step-content">
                            <h3>OFFICE LOCATION & SETUP SUPPORT</h3>
                            <p>Assistance in identifying strategic office locations, negotiating leases, and
                                managing physical infrastructure setup.</p>
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
                        <div class="work-step-content">
                            <h3>IT INFRASTRUCTURE & SUPPORT</h3>
                            <p>Planning and deploying scalable IT systems, network infrastructure, software
                                tools, and ongoing technical support tailored to your business needs.</p>
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
                        <div class="work-step-content">
                            <h3>VENDOR & PARTNER MANAGEMENT</h3>
                            <p>Identification, evaluation, and onboarding of local vendors, suppliers, and
                                service providers to streamline your supply chain and operations.</p>
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
                        <div class="work-step-content">
                            <h3>FACILITY MANAGEMENT</h3>
                            <p>Coordination of office services including utilities, security, maintenance, and housekeeping to ensure a smooth workplace experience.</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-5.svg')}}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>COMPLIANCE WITH LOCAL REGULATIONS</h3>
                            <p>Ensuring operational activities comply with local zoning laws, safety
                                regulations, and environmental norms.</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-6.svg')}}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>LOGISTICS & SUPPLY CHAIN COORDINATION</h3>
                            <p>Managing import/export procedures, customs clearance, and warehousing
                                support relevant to your engineering or technology operations.</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-7.svg')}}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>BUSINESS CONTINUITY & RISK MANAGEMENT</h3>
                            <p>Establishing contingency plans, risk assessments, and disaster recovery
                                protocols to safeguard operations.</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- How It Work Section End -->
@endsection