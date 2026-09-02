<!-- Header Start -->
<header class="header" id="sticky-menu">
    <div class="container-fluid">
        <div class="row v-center align-items-center">

            <!-- Logo -->
            <div class="header-item item-left header-logo m-0">
                <div class="logo">
                    <a class="navbar-brand" href="{{ route('index') }}">

                        <!-- Rhino Logo -->
                        <img
                            src="{{ asset('frontend/images/rhino-logo.webp') }}"
                            alt="Szorzo Rhino Logo"
                            width="100"
                            height="100"
                            loading="eager"
                            decoding="async"
                            style="height:auto;"
                        >

                        <!-- Logo Text -->
                        <img
                            src="{{ asset('frontend/images/logo-bg.webp') }}"
                            alt="Szorzo Logo"
                            width="230"
                            height="55"
                            class="logo-second"
                            loading="eager"
                            decoding="async"
                            style="height:auto;"
                        >
                    </a>
                </div>
            </div>

            <!-- Menu -->
            <div class="header-item item-center">
                <div class="menu-overlay"></div>

                <nav class="menu">

                    <div class="mobile-menu-head">

                        <div class="go-back">
                            <i class="fa fa-angle-left"></i>
                        </div>

                        <!-- Mobile Logo -->
                        <div class="mobile-logo">
                            <a href="{{ route('index') }}">

                                <img
                                    src="{{ asset('frontend/images/rhino-logo.webp') }}"
                                    alt="Szorzo Rhino Logo"
                                    width="80"
                                    height="80"
                                    loading="eager"
                                    decoding="async"
                                    style="height:auto;"
                                >

                                {{-- Optional --}}
                                {{--
                                <img
                                    src="{{ asset('frontend/images/logo-bg.webp') }}"
                                    alt="Szorzo Logo"
                                    width="150"
                                    height="35"
                                    loading="eager"
                                    decoding="async"
                                    style="height:auto;"
                                >
                                --}}
                            </a>
                        </div>

                        <div class="current-menu-title"></div>

                        <div class="mobile-menu-close">
                            &times;
                        </div>
                    </div>

                    <ul class="menu-main mb-0">

                        <!-- SZORZO AI -->
                        <li>
                            <a
                                href="{{ route('szorzo.ai') }}"
                                style="font-size: large; color: white; font-weight: bold;"
                            >
                                SZORZO AI
                            </a>
                        </li>

                        <!-- GCC SERVICES -->
                        <li class="menu-item-has-children">
                            <a
                                href="{{ route('index') }}"
                                style="font-size: large; color: white; font-weight: bold;"
                            >
                                GCC SERVICES
                            </a>

                            <div class="sub-menu single-column-menu">
                                <h4 class="title">
                                    <a href="">
                                        ENTERPRISE SERVICES
                                    </a>
                                </h4>

                                <ul class="gcc-menu">
                                    <li>
                                        <a href="{{ route('enterprice.formation') }}">
                                            Enterprise Formation
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            Enterprise Digitalization
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('enterprise.learning.solution') }}">
                                            Enterprise Learning Solutions
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('marketing.service') }}">
                                            Marketing as A Service (MaaS)
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('org.capacity.ass') }}">
                                            Organization Capacity Assessment
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('opt.infra.off') }}">
                                            Operations and HR Infrastructure Offerings
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('strategic.advisory') }}">
                                            Merger and Acquisition Services
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- IT SERVICES -->
                        <li class="menu-item-has-children">
                            <a
                                href="#"
                                style="font-size: large; color: white; font-weight: bold;"
                            >
                                IT SERVICES
                            </a>

                            <div class="sub-menu single-column-menu">
                                <h4 class="title">
                                    <a href="">
                                        IT SERVICES
                                    </a>
                                </h4>

                                <ul class="gcc-menu">
                                    <li>
                                        <a href="{{ route('it.infrastructure') }}">
                                            IT Infrastructure Services
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('data.center.design') }}">
                                            Data Center - Design, Built & Maintain
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('data.center.managed.service') }}">
                                            Data Center - Managed Services
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('cyber.security') }}">
                                            Cyber Security Services
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('certificate.compliance') }}">
                                            Certification & Compliance
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('hardware.software') }}">
                                            Hardware + Software Integration
                                        </a>
                                    </li>

                                    {{-- <li>
                                        <a href="{{ route('tech.services') }}">
                                            Tech Services
                                        </a>
                                    </li> --}}
                                </ul>
                            </div>
                        </li>

                        <!-- TELECOM SERVICES -->
                        <li>
                            <a
                                href="{{ route('telecom.services') }}"
                                style="font-size: large; color: white; font-weight: bold;"
                            >
                                TELECOM SERVICES
                            </a>
                        </li>

                        <!-- ABOUT US -->
                        <li class="menu-item-has-children">
                            <a
                                href="{{ route('about.us') }}"
                                style="font-size: large; color: white; font-weight: bold;"
                            >
                                ABOUT US
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
