<!-- Header Start -->
<header class="header" id="sticky-menu">
    <div class="container-fluid">
        <div class="row v-center align-items-center">

            <!-- Logo -->
            <div class="header-item item-left header-logo m-0">
                <div class="logo">
                    <a class="navbar-brand" href="{{route('index')}}">
                        <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                        <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px" class="logo-second">
                    </a>
                </div>
            </div>

            <!-- Menu -->
            <div class="header-item item-center">
                <div class="menu-overlay"></div>

                <nav class="menu">
                    <div class="mobile-menu-head">
                        <div class="go-back"><i class="fa fa-angle-left"></i></div>

                        <div class="mobile-logo">
                            <a href="{{route('index')}}">
                                <img src="{{ asset('frontend/images/rhino-logo.png') }}" width="80">
                                {{-- <img src="{{ asset('frontend/images/logo-bg.png') }}" width="150px"> --}}
                            </a>
                        </div>

                        <div class="current-menu-title"></div>
                        <div class="mobile-menu-close">&times;</div>
                    </div>

                    <ul class="menu-main mb-0">

                        <li>
                            <a href="{{route('szorzo.ai')}}" style="font-size: large; color: white; font-weight: bold;">SZORZO AI</a>
                        </li>

                        <!-- GCC SERVICES -->
                        <li class="menu-item-has-children">
                            <a href="{{route('index')}}" style="font-size: large; color: white; font-weight: bold;">
                                GCC SERVICES <i class="ri-arrow-down-s-line"></i>
                            </a>

                            <!-- FIXED: single column -->
                            <div class="sub-menu single-column-menu">
                                <ul class="gcc-menu">
                                    <li><a href="{{ route('enterprice.formation') }}">Enterprise Formation</a></li>
                                    <li><a href="#">Enterprise Digitalization</a></li>
                                    <li><a href="{{ route('enterprise.learning.solution') }}">Enterprise Learning Solutions</a></li>
                                    <li><a href="{{ route('marketing.service') }}">Marketing as A Service (MaaS)</a></li>
                                    <li><a href="{{ route('org.capacity.ass') }}">Organization Capacity Assessment</a></li>
                                    <li><a href="{{ route('opt.infra.off') }}">Operations and HR Infrastructure Offerings</a></li>
                                    <li><a href="{{ route('strategic.advisory') }}">Merger and Acquisition Services</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- IT SERVICES -->
                        <li class="menu-item-has-children">
                            <a href="#" style="font-size: large; color: white; font-weight: bold;">
                                IT SERVICES <i class="ri-arrow-down-s-line"></i>
                            </a>

                            <!-- FIXED: single column -->
                            {{-- <div class="sub-menu single-column-menu"> --}}
                            <div class="sub-menu single-column-menu">
                                <ul class="gcc-menu">
                                    <li><a href="{{ route('it.infrastructure') }}">IT Infrastructure Services</a></li>
                                    <li><a href="{{ route('data.center.design') }}">Data Center - Design, Built & Maintain</a></li>
                                    <li><a href="{{ route('data.center.managed.service') }}">Data Center - Managed Services</a></li>
                                    <li><a href="{{ route('cyber.security') }}">Cyber Security Services</a></li>
                                    <li><a href="{{ route('certificate.compliance') }}">Certification & Compliance</a></li>
                                    <li><a href="{{ route('hardware.software') }}">Hardware + Software Integration</a></li>
                                </ul>
                            </div>

                            {{-- <div class="list-item header-cta">
                                <img src="{{ asset('frontend/images/rhino-white.jpg') }}" alt="Szorzo" />
                                <div class="cta-content">
                                    <h2>Boost Your Security with Szorzo</h2>
                                    <a href="{{ route('contact') }}" class="btn-default">Get Started</a>
                                </div>
                            </div> --}}
                        </li>

                        <li>
                            <a href="{{ route('telecom.services') }}" style="font-size: large; color: white; font-weight: bold;">
                                TELECOM SERVICES
                            </a>
                        </li>

                        {{-- KEEP AS IT IS --}}
                        {{-- <li class="menu-item-has-children"> ... </li> --}}

                        <li class="menu-item-has-children">
                            <a href="{{ route('about.us') }}" style="font-size: large; color: white; font-weight: bold;">
                                ABOUT US
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('contact') }}" style="font-size: large; color: white; font-weight: bold;">
                                CONTACT US
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>

            <!-- Right -->
            <div class="header-item item-right">
                <div class="mobile-menu-trigger">
                    <span></span>
                </div>
            </div>

        </div>
    </div>
</header>
<!-- Header End -->