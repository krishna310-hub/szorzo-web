@extends('frontend.includes.master')
@section('content')
    <!-- Page Header Start -->
    <div class="page-header-it-bg">
        <div class="container">
            <div class="col-lg-12" style="margin-top: 150px; margin-left: -140px;">
                <div class="white-card">
                    <div class="section-title">
                        <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                            Merger and Acquisition<span> Advisory Services</span>
                        </h1>
                        <div style="color: white;" class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                            <p style="font-weight: bold; font-size: x-large; text-align: justify;">SZORZO supports global
                                clients
                                with comprehensive M&A advisory
                                services, including cross-border
                                deal sourcing, valuation, due
                                diligence, and integration. Our
                                expertise ensures seamless
                                transactions that align with your
                                strategic goals, whether
                                expanding internationally,
                                entering new markets, or
                                divesting assets.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Page Services Start -->
    {{-- <div class="page-services">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-1.svg') }}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3>Deal sourcing & Target Identification</h3>
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-2.svg') }}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3>Valuation & Financial Modelling</h3>
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-3.svg') }}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3>Due Diligence Management</h3>
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-4.svg') }}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3>Deal Structuring & Negotiation</h3>
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Service Item Start -->
                    <div class="service-item wow fadeInUp" data-wow-delay="0.8s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-5.svg') }}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3>Post-Merger Integration Planning</h3>
                        </div>
                    </div>
                    <!-- Service Item End -->
                </div>
            </div>
        </div>
    </div> --}}
    <!-- Page Services End -->

    <!-- How It Work Section Start -->
    {{-- <div class="how-it-work bg-section">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp" style="font-size: x-large;">Connecting Capital, Strategy, and
                            Opportunity Globally</h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">MERGER & ACQUISITION <span
                                style="font-weight: bold;"><br>ADVISORY SERVICES</span></h2>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" style="margin-left: 100px;">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-1.svg') }}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>Market Entry Strategy</h3>
                            <p>Market Opportunity Assessment</p>
                            <p>Entry Strategy Design</p>
                            <p>Regulatory & Legal Navigation</p>
                            <p>Go-to-Market Strategy & Planning</p>
                            <p>Partner Identification & Local Setup</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" style="margin-left: 80px;" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-2.svg') }}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>Business Expansion Advisory</h3>
                            <p>Expansion Feasability & ROI Modeling</p>
                            <p>Geographic Diversification Planning</p>
                            <p>Operational Scaling Strategy</p>
                            <p>Capital Raising for Expansion</p>
                            <p>Strategic Alliances & JV Structuring</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Work Step Item Start -->
                    <div class="work-step-item wow fadeInUp" style="margin-left: 40px;" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-work-step-3.svg') }}" alt="">
                        </div>
                        <div class="work-step-content">
                            <h3>Strategic Advisory Services</h3>
                            <p>Strategic Planning & Growth Roadmaps</p>
                            <p>Investor Relations & Board Advisory</p>
                            <p>Turnaround & Restructing</p>
                        </div>
                    </div>
                    <!-- Work Step Item End -->
                </div>
            </div>
        </div>
    </div> --}}
    <!-- How It Work Section End -->

    {{-- <div class="page-project-single">
        <div class="container">
            <div class="row">

                <div class="col-lg-12">
                    <div class="project-single-content">

                        <div class="project-entry">
                            <div class="project-performance-box">
                                <h2 class="wow fadeInUp">Our Key M&A Service <span
                                        style="font-weight: bold;">Offerings</span></h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s" style="font-size: x-large;">We provide
                                    end-to-end M&A advisory services to
                                    global companies looking to establish, acquire, or
                                    expand their footprint in India & any other global
                                    locations.</p>

                                <div class="performance-step-list">
                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.4s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 01</h3>
                                            </div>
                                            <h3>Strategic Advisory</h3>
                                            <p>We develop tailored M&A strategies, assess market
                                                entry opportunities, and align deal structures with
                                                your long-term business objectives in India & the
                                                Global Markets.</p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Market entry and sector analysis.</li>
                                                <li>Buy-side and sell-side strategy formulation</li>
                                                <li>Deal structuring & investment roadmap</li>
                                                <li>Joint ventures, strategic alliances & partnership planning</li>
                                                <li>Cross-border synergy analysis</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.6s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 02</h3>
                                            </div>
                                            <h3>Target Identification & Evaluation</h3>
                                            <p>We identify suitable acquisition targets, evaluate
                                                strategic fit, and assess financial, operational, and
                                                cultural compatibility for successful transactions.</p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Identification of Companies (SMEs, Startups, Large Enterprises)</li>
                                                <li>In-depth commercial, operational & financial analysis</li>
                                                <li>SWOT analysis and strategic fit assessment</li>
                                                <li class="mb-5">Management team profiling and cultural compatibility
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.6s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 03</h3>
                                            </div>
                                            <h3>Due Diligence</h3>
                                            <p>We conduct comprehensive due diligence across
                                                financial, legal, tax, and operational domains to
                                                mitigate risks and validate investment assumptions.</p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Financial due diligence (FDD)</li>
                                                <li>Legal and regulatory due diligence</li>
                                                <li>Tax and structuring due diligence</li>
                                                <li>Operational and commercial due diligence</li>
                                                <li class="mb-1">ESG, compliance, and risk assessments</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.6s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 04</h3>
                                            </div>
                                            <h3>Valuation & Deal Structuring</h3>
                                            <p>We offer robust business valuation and structure
                                                deals to maximize value, ensure compliance, and
                                                optimize tax and regulatory outcomes.</p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Business and asset valuation (DCF, comparables, market approach)</li>
                                                <li>Structuring transactions for tax efficiency and compliance</li>
                                                <li>Evaluation of funding options (debt, equity, hybrid instruments)</li>
                                                <li class="mb-5">Advice on foreign exchange regulations (FEMA, RBI
                                                    approvals)</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.6s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 05</h3>
                                            </div>
                                            <h3>Negotiation & Deal Execution</h3>
                                            <p>We support contract negotiations, coordinate with
                                                stakeholders, and manage documentation and
                                                regulatory filings for smooth deal closure.
                                            </p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Preparation of information memorandums & term sheets</li>
                                                <li>Support in SPA, SHA, and definitive agreement negotiations</li>
                                                <li>Assistance with regulatory filings and clearances (SEBI, RBI, MCA, etc.)
                                                </li>
                                                <li>End-to-end transaction management and coordination</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="performance-step-item wow fadeInUp" data-wow-delay="0.6s">
                                        <div class="performance-step-content">
                                            <div class="performance-step-no">
                                                <h3>Step 06</h3>
                                            </div>
                                            <h3>Post-Merger Integration (PMI)</h3>
                                            <p>We manage integration of people, systems, and
                                                operations to unlock synergies, align cultures, and
                                                ensure a successful post-deal transition.
                                            </p>
                                        </div>
                                        <div class="project-solution-content wow fadeInUp" data-wow-delay="0.8s">
                                            <ul>
                                                <li>Integration planning across finance, HR, IT, and operations</li>
                                                <li>Change management and cultural alignment</li>
                                                <li>Synergy realization and performance tracking</li>
                                                <li>Legal entity rationalization and corporate restructuring</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Project Entry End -->
                    </div>
                    <!-- Project Single Content End -->
                </div>
            </div>
        </div>
    </div> --}}
@endsection
