@extends('frontend.includes.master')
@section('content')
    <!-- Page Header Start -->
    <div class="page-header-data-center-design-bg">
        <div class="container">
            <div class="col-lg-12" style="">
                <div class="white-card">
                    <div class="section-title">
                        <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                            <span>Data Center </span>- Design, Build & Maintain
                        </h1>
                        <div style="color: white;" class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                            <p style="font-weight: bold; font-size: x-large; text-align: justify;">Build the Foundation of
                                Digital Resilience <br>
                                At Szorzo, we design, build, and maintain high-performance data centers engineered for
                                scalability, efficiency, and uninterrupted operations. From greenfield development to
                                modernization of legacy facilities, we deliver secure, compliant, and energy-optimized data
                                center environments tailored to your business growth.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <div class="how-it-work bg-section mt-5 mb-5">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        {{-- <h3 class="wow fadeInUp" style="font-size: x-large;">Enabling Business Foundations Globally</h3> --}}
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">Data Center <span
                                style="font-weight: bold;">- Design, Build & Maintain</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>
            <div class="service-single-content">
                <div class="service-entry">
                    <h2 class="text-anime-style-2">Build the Foundation of <span>Digital Resilience</span></h2>
                    <p class="wow fadeInUp">At Szorzo, we design, build, and maintain high-performance data centers
                        engineered for scalability, efficiency, and uninterrupted operations. From greenfield development to
                        modernization of legacy facilities, we deliver secure, compliant, and energy-optimized data center
                        environments tailored to your business growth.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-1.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Strategic Planning & Design</h3>
                            <p>Tier-based architecture (Tier I–IV aligned design)</p>
                            <p>Capacity planning & scalability modeling</p>
                            <p>Energy-efficient & sustainable infrastructure design</p>
                            <p>High-density rack and hybrid-cloud ready architecture</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-2.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Build & Deployment </h3>
                            <p>Power & cooling systems implementation</p>
                            <p>Structured cabling & network backbone setup</p>
                            <p>Physical security systems integration</p>
                            <p>Modular & prefabricated data center solutions</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-3.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Operations & Maintenance</h3>
                            <p>24/7 monitoring & predictive maintenance</p>
                            <p>Power usage effectiveness (PUE) optimization</p>
                            <p>Asset lifecycle management</p>
                            <p>Disaster recovery & business continuity support</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-4.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Why Szorzo? </h3>
                            <p>Reduced downtime risk</p>
                            <p>Optimized operational expenditure</p>
                            <p>Global compliance alignment</p>
                            <p>Future-ready digital infrastructure</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="service-single-content">
                    <div class="service-entry">
                        <h2 class="text-anime-style-2">Engineering Mission-Critical <span>Infrastructure for Global
                                Enterprises</span></h2>
                        <p class="wow fadeInUp">At Szorzo, we architect, construct, and sustain enterprise-class data
                            centers that power digital economies. Our approach combines engineering precision, operational
                            discipline, and strategic foresight to deliver resilient, scalable, and future-ready
                            infrastructure environments.</p>
                        <p class="wow fadeInUp" data-wow-delay="0.2s">We partner with organizations to design
                            high-availability facilities aligned to Tier standards, optimized for energy efficiency, and
                            engineered for uninterrupted performance across mission-critical workloads.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-5.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Advisory & Architecture</h3>
                            <p>Tier-aligned infrastructure design (I–IV)</p>
                            <p>Resilience modeling and capacity forecasting<p>
                            <p>Sustainable, high-density facility planning</p>
                            <p>Hybrid and cloud-ready architectural frameworks</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-6.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Engineering & Deployment</h3>
                            <p>Redundant power and advanced cooling systems</p>
                            <p>Structured cabling and core network architecture</p>
                            <p>Physical security and access governance</p>
                            <p>Modular and hyperscale-ready deployment models</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-7.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Lifecycle Operations</h3>
                            <p>24/7 monitoring and predictive maintenance</p>
                            <p>PUE optimization and energy governance</p>
                            <p>Asset lifecycle and capacity management</p>
                            <p>Business continuity and disaster recovery integration</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-8.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Executive Impact</h3>
                            <p>Enhanced operational resilience</p>
                            <p>Reduced infrastructure risk exposure</p>
                            <p>Optimized capital and operational expenditure</p>
                            <p>Long-term scalability aligned to enterprise growth</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="service-single-content">
                    <div class="service-entry">
                        <div class="service-benefits-box">
                            <h2 class="text-anime-style-2">Engineering Mission-Critical <span>Infrastructure</span></h2>
                            <p class="wow fadeInUp">Szorzo architects, constructs, and sustains enterprise-grade data
                                centers built for high availability, operational efficiency, and long-term scalability.
                                Whether greenfield development, modernization, or expansion, we deliver facilities aligned
                                with global standards and optimized for performance.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-1.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Advisory & Design</h3>
                            <p>Tier I–IV aligned architecture</p>
                            <p>Capacity forecasting & scalability modeling</p>
                            <p>High-density, cloud-ready design</p>
                            <p>Sustainable and energy-efficient planning</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-2.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Build & Deployment</h3>
                            <p>Redundant power and advanced cooling systems</p>
                            <p>Structured cabling & core network backbone</p>
                            <p>Physical security and access control systems</p>
                            <p>Modular and hyperscale-ready solutions</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-3.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Operations & Maintenance</h3>
                            <p>24/7 monitoring & predictive maintenance</p>
                            <p>Power Usage Effectiveness (PUE) optimization</p>
                            <p>Asset lifecycle management</p>
                            <p>Disaster recovery & business continuity integration</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-4.svg') }}" alt="">
                        </div>
                        <div class="work-step-content text-start">
                            <h3>Business Impact</h3>
                            <p>Reduced downtime risk</p>
                            <p>Optimized capital and operational expenditure</p>
                            <p>Increased resilience and reliability</p>
                            <p>Infrastructure aligned with long-term enterprise growth</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>
            </div>
        </div>
    </div>
@endsection
