<!-- Header Start -->
<header class="header" id="sticky-menu">
    <div class="container-fluid">
        <div class="row v-center align-items-center">
            <div class="header-item item-left header-logo m-0">
                <div class="logo">
                    <a class="navbar-brand" href="{{route('index')}}">
                        <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                        <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px">
                    </a>
                </div>
            </div>
            <div class="header-item item-center">
                <div class="menu-overlay"></div>
                <nav class="menu">
                    <div class="mobile-menu-head">
                        <div class="go-back"><i class="fa fa-angle-left"></i></div>
                        <div class="mobile-logo">
                            <a href="{{route('index')}}">
                                <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo"
                                    width="80">
                                <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="150px">
                            </a>
                        </div>
                        <div class="current-menu-title"></div>
                        <div class="mobile-menu-close">&times;</div>
                    </div>
                    <ul class="menu-main mb-0">
                        <li>
                            <a href="{{route('index')}}" style="font-size: large; color: white; font-weight: bold;">Home</a>
                        </li>
                        <li class="menu-item-has-children">
                            <a style="font-size: large; color: white; font-weight: bold;">Services
                                <!-- <i class="ri-arrow-down-s-line"></i> -->
                            </a>
                            <div class="sub-menu mega-menu mega-menu-column-4">
                                <div class="list-item">
                                    <h4 class="title"> <a href="">Enterprise Transformation</a></h4>
                                    <ul>
                                        <li><a href="{{ route('enterprice.formation') }}">Enterprise Formation</a></li>
                                        <li><a href="{{ route('marketing.service') }}">Marketing as A Service (MaaS)</a></li>
                                        <li><a href="{{ route('org.capacity.ass') }}">Organization Capacity Assessment</a></li>
                                        <li><a href="{{ route('opt.infra.off') }}">Operations and Infrastructure Offerings</a></li>
                                    </ul>
                                </div>
                                <div class="list-item">
                                    <h4 class="title"> <a href="{{ route('strategic.advisory') }}">Merger & Acquisition
                                            Advisory</a></h4>
                                    <ul>
                                        <li><a href="{{ route('strategic.advisory') }}">Strategic Advisory</a></li>
                                        <li><a href="{{ route('strategic.advisory') }}">Target Identification &
                                                Evaluation</a></li>
                                        <li><a href="{{ route('strategic.advisory') }}">Due Diligence</a></li>
                                        <li><a href="{{ route('strategic.advisory') }}">Valuation & Deal Structuring</a>
                                        </li>
                                        <li><a href="{{ route('strategic.advisory') }}">Negotiation & Deal Execution</a>
                                        </li>
                                        <li><a href="{{ route('strategic.advisory') }}">Post-Merger Integration (PMI)</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="list-item">
                                    <h4 class="title"> <a href="">Technology & Engineering Services</a></h4>
                                    <ul>
                                        <li><a href="#">IT Services</a></li>
                                        <li><a href="#">Cyber Security</a></li>
                                        <li><a href="#">Engineering Services</a></li>
                                    </ul>
                                </div>
                                <div class="list-item header-cta">
                                    <img src="{{ asset('frontend/images/rhino-white.jpg') }}" alt="Szorzo" />
                                    <div class="cta-content">
                                        <h2>Boost Your Security with Szorzo</h2>
                                        <a href="{{ route('contact') }}" class="btn-default">Get Started</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a href="{{ route('about.us') }}#industries"
                                style="font-size: large; color: white; font-weight: bold;">Industries</a>
                        </li>
                        {{-- ?#industries --}}
                        <li class="menu-item-has-children">
                            <a href="{{ route('about.us') }}" style="font-size: large; color: white; font-weight: bold;">About
                                Us</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}" style="font-size: large; color: white; font-weight: bold;">Contact
                                Us</a>
                        </li>
                        <li class="menu-item-has-children">
                            <a style="font-size: large; color: white; font-weight: bold;">Careers</a>
                            <div class="sub-menu mega-menu mega-menu-column-2">
                                <div class="list-item">
                                    <ul>
                                        <li>
                                            <a href="https://szorzo.zohorecruit.in/jobs/Careers">
                                                Current Job Openings
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('careers') }}">
                                                Candidate Registration Form
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="header-item item-right">

                <!-- <div class="d-none d-lg-block">
                    <div class="header-btn">
                        <a href="contact.html" class="btn-default">Get Started</a>
                    </div>
                </div> -->
                <div class="mobile-menu-trigger">
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header End -->
