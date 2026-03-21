@extends('frontend.includes.master')
@section('content')
    <!-- Page Header Start -->
    <div class="page-header-contact-us-bg">
        <div class="container">
            <div class="row align-items-left">
                <div class="col-lg-6" style="margin-top: 200px;">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 style="color: white; margin-top: 50px; margin-left: -470px" class="wow fadeInUp"
                            data-cursor="-opaque">WE SERVE THE <br>
                            <span>WORLD ?</span>
                        </h1>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <div class="container mt-5">

        <div class="text-center section-header">
            <div class="section-title section-title-center">
                <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                    <span style="font-weight: bold;">Choose your services and let’s get started</span>
                </h2>
            </div>
            <p>
                Please select your area of interest below. An SZORZO Experts
                will contact you shortly after receiving your request.
            </p>
        </div>

        <div class="mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6">
                    </div>
                    <div class="col-lg-4 col-md-6 mb-2">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#szorzoModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img
                                    src="{{ asset('frontend/images/service-icons/artificial-intelligence.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 35px; color:red">SZORZO AI</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#itServicesModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/settings.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">IT Services</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3" data-bs-toggle="modal" data-bs-target="#itInfrastructureServicesModal"
                            style="cursor:pointer;">
                        <div class="service-box">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/it-infra.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">IT Infrastructure <br> Services</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3" data-bs-toggle="modal" data-bs-target="#cyberSecurityServicesModal"
                            style="cursor:pointer;">
                        <div class="service-box">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/virus-attack.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Cyber Security <br> Services</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                    </div>
                    <div class="col-lg-4 col-md-6 mb-2">
                        <div class="service-box" data-bs-toggle="" data-bs-target=""
                            style="cursor:pointer;">
                            <div class="icon-box"> <img
                                    src="{{ asset('frontend/images/service-icons/outsourcing.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 35px; color:red">Enterprise Services</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#enterpriseTransformationModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/city.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content ">
                                <h3 style="font-size: 25px;">Enterprise <br> Transformation</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#enterpriseDigitalizationModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img
                                    src="{{ asset('frontend/images/service-icons/social-media-marketing.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Enterprise <br> Digitalization</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#enterpriseLearningSolutionModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/learning.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Enterprise Learning Solutions</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#organizationCapacityAssessmentModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/people.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Organization Capacity Assessment</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#hrInfrastructureOfferingsModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/customer-care.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Operations and HR Infrastructure Offerings</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-3">
                        <div class="service-box" data-bs-toggle="modal" data-bs-target="#mergerAndAcquisitionServicesModal"
                            style="cursor:pointer;">
                            <div class="icon-box"> <img src="{{ asset('frontend/images/service-icons/acquisition.png') }}"
                                    alt=""> </div>
                            <div class="service-box-content">
                                <h3 style="font-size: 25px;">Merger and Acquisition Services</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <section id="features" class="section-padding mt-5">
            <div class="container">
                <div class="section-header">
                    <div class="shape wow fadeInDown"></div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        <div class="content-left">
                            <div class="box-item animated wow fadeInLeft">
                                <div class="text">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#szorzoModal">
                                        <h4>SZORZO AI</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInLeft" data-wow-delay="0.6s">
                                <div class="text">
                                    <a href="{{ route('contact.enterprise.transformation.form') }}">
                                        <h4>Enterprise Transformation</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInLeft" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.enterprise.digitalization.form') }}">
                                        <h4>Enterprise Digitalization</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInLeft" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.enterprise.learning.solution.form') }}">
                                        <h4>Enterprise Learning Solutions</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInLeft" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.merger.services.form') }}">
                                        <h4>Merger and Acquisition Services</h4>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        <div class="show-box animated wow fadeInUp">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        <div class="content-right">
                            <div class="box-item animated wow fadeInRight">
                                <div class="text">
                                    <a href="{{ route('contact.organization.capacity.form') }}">
                                        <h4>Organization Capacity Assessment</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInRight" data-wow-delay="0.6s">
                                <div class="text">
                                    <a href="{{ route('contact.operation.hr.offering.form') }}">
                                        <h4>Operations and HR Infrastructure Offerings</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInRight" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.it.services.form') }}">
                                        <h4>IT Services</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInRight" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.szorzo.ai.form') }}">
                                        <h4>IT Infrastructure Services</h4>
                                    </a>
                                </div>
                            </div>
                            <div class="box-item animated wow fadeInRight" data-wow-delay="0.9s">
                                <div class="text">
                                    <a href="{{ route('contact.szorzo.ai.form') }}">
                                        <h4>Cyber Security Services</h4>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
    </div>

    <div class="text-center section-header location-section mb-5">

        <div class="section-title section-title-center">
            <h2>
                <span style="font-weight:bold;">GLOBAL PRESENCE</span>
            </h2>

            <p style="font-size:40px">
                Delivering innovative solutions across global markets through our growing international presence.
            </p>
        </div>

        <div class="country-icons">

            <div class="country-icon">
                <div style="font-size: 35px; color:white">INDIA</div>
                <div class="preview-card">
                    <img src="{{ asset('frontend/images/white-tiger-stands-proudly-snowy-winter-landscape.jpg') }}">
                    <span>INDIA</span>
                </div>
            </div>

            <div class="country-icon">
                <div style="font-size: 35px; color:white">DUBAI</div>
                <div class="preview-card">
                    <img src="{{ asset('frontend/images/shanghai-aerial-sunset.jpg') }}">
                    <span>DUBAI</span>
                </div>
            </div>

            <div class="country-icon">
                <div style="font-size: 35px; color:white">US</div>
                <div class="preview-card">
                    <img src="{{ asset('frontend/images/view-world-monument-celebrate-world-heritage-day.jpg') }}">
                    <span>WASHINGTON DC</span>
                </div>
            </div>

            <div class="country-icon">
                <div style="font-size: 35px; color:white">UK</div>
                <div class="preview-card">
                    <img
                        src="{{ asset('frontend/images/illuminated-landmark-reflects-water-majestic-man-made-structure-generated-by-ai.jpg') }}">
                    <span>LONDON</span>
                </div>
            </div>

            <div class="country-icon">
                <div style="font-size: 35px; color:white">AFRICA</div>
                <div class="preview-card">
                    <img src="{{ asset('frontend/images/cityscape-sunset-skyscrapers-silhouetted.jpg') }}">
                    <span>AFRICA</span>
                </div>
            </div>

        </div>

    </div>

    {{-- <div class="col-md-12 d-flex justify-content-center position-relative">

        <!-- LEFT ARROW -->
        <button class="arrow left"><</button>

        <ul class="cards">

            <li class="card card--current">
                <img src="{{ asset('frontend/images/white-tiger-stands-proudly-snowy-winter-landscape.jpg') }}" class="card-img india-img">
                <div class="card-overlay">INDIA</div>
            </li>

            <li class="card">
                <img src="{{ asset('frontend/images/shanghai-aerial-sunset.jpg') }}" class="card-img dubai-img">
                <div class="card-overlay">DUBAI</div>
            </li>

            <li class="card">
                <img src="{{ asset('frontend/images/view-world-monument-celebrate-world-heritage-day.jpg') }}" class="card-img washington-img">
                <div class="card-overlay">WASHINGTON DC</div>
            </li>

            <li class="card">
                <img src="{{ asset('frontend/images/illuminated-landmark-reflects-water-majestic-man-made-structure-generated-by-ai.jpg') }}" class="card-img london-img">
                <div class="card-overlay">UK LONDON</div>
            </li>

            <li class="card">
                <img src="{{ asset('frontend/images/cityscape-sunset-skyscrapers-silhouetted.jpg') }}" class="card-img">
                <div class="card-overlay">AFRICA</div>
            </li>

        </ul>

        <!-- RIGHT ARROW -->
        <button class="arrow right">></button>

    </div> --}}

    {{-- <section class="locations-section">
        <div class="container">

            <div class="location-header">
                <h2>Locations</h2>
                <p>Explore our service offerings and subsidiaries <br>
                    in specific geography</p>
            </div>

            <!-- Location Cards -->
            <div class="row g-0">

                <div class="col-lg-3 col-md-1">
                    <div class="location-card">
                        <img src=" {{ asset('frontend/images/indian-city-buildings-scene.jpg') }} ">
                        <div class="location-overlay">
                            <h4>Bangalore</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="location-card">
                        <img src="{{ asset('frontend/images/taj-mahal-view-through-archway-sunset.jpg') }}">
                        <div class="location-overlay">
                            <h4>Delhi</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="location-card">
                        <img src="{{ asset('frontend/images/highway-aerial-view.jpg') }}">
                        <div class="location-overlay">
                            <h4>Chennai</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="location-card">
                        <img src="{{ asset('frontend/images/vertical-shot-buildings-cloudy-sky.jpg') }}">
                        <div class="location-overlay">
                            <h4>Coimbatore</h4>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section> --}}

    {{-- <div class="page-contact-us mb-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Contact Info List Start -->
                    <div class="contact-info-list">
                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-phone.svg') }}" alt="">
                            </div>
                            <div class="contact-info-content">
                                <h3>contact us</h3>
                                <p><a href="tel:+91 990 141 9393">+91 990 141 9393</a></p>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->

                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp" data-wow-delay="0.2s">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-mail.svg') }}" alt="">
                            </div>
                            <div class="contact-info-content">
                                <h3>Make a quote</h3>
                                <p><a href="mailto:info@domain.com">business@szorzo.com</a></p>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->

                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp" data-wow-delay="0.4s">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-clock.svg') }}" alt="">
                            </div>
                            <div class="contact-info-content">
                                <h3>Working hours</h3>
                                <p>Mon-Fri : 09.30am - 06.30pm</p>
                                <p>Sat - Sun : close</p>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->

                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp" data-wow-delay="0.6s">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-location.svg') }}" alt="">
                            </div>
                            <div class="contact-info-content">
                                <h3>location</h3>
                                <p>India</p>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->
                    </div>
                    <!-- Contact Info List Start -->
                </div>

                <div class="col-lg-12">
                    <!-- Contact Us Form Start -->
                    <div class="conatct-us-form">
                        <!-- Google Map Iframe Start -->
                        <div class="google-map-iframe order-lg-1 order-2">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3889.3688585067102!2d77.55142337454527!3d12.8839870167941!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae3f7345fa86f3%3A0xeba05664185fcf5b!2sClayWorks%20Shankaraa!5e0!3m2!1sen!2sin!4v1760083040513!5m2!1sen!2sin"
                                width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <!-- Google Map Iframe End -->

                        <!-- Contact Form Start -->
                        <div class="contact-form order-lg-2 order-1">
                            <!-- Section Title Start -->
                            <div class="section-title">
                                <h3 class="wow fadeInUp">Contact us</h3>
                                <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">Send us <span>a
                                        message</span></h2>
                            </div>
                            <!-- Section Title End -->

                            <!-- Contact Form Start -->
                            <form id="contactForm" action="#" method="POST" data-toggle="validator"
                                class="wow fadeInUp" data-wow-delay="0.4s">
                                <div class="row">
                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="fname" class="form-control" id="fname"
                                            placeholder="First Name" required>
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="lname" class="form-control" id="lname"
                                            placeholder="Last Name" required>
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="form-group col-md-6 mb-4">
                                        <input type="text" name="phone" class="form-control" id="phone"
                                            placeholder="Phone No." required>
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="form-group col-md-6 mb-4">
                                        <input type="email" name ="email" class="form-control" id="email"
                                            placeholder="Email Address" required>
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="form-group col-md-12 mb-5">
                                        <textarea name="message" class="form-control" id="message" rows="4" placeholder="Write Message..."></textarea>
                                        <div class="help-block with-errors"></div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="contact-form-btn">
                                            <button type="submit" class="btn-default"><span>submit
                                                    now</span></button>
                                            <div id="msgSubmit" class="h3 hidden"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- Contact Form End -->
                        </div>
                        <!-- Contact Form End -->
                    </div>
                    <!-- Contact Us Form End -->
                </div>
            </div>
        </div>
    </div> --}}

    {{-- modal popup --}}
    <div class="modal fade" id="szorzoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="szorzoForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">SZORZO AI Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="enterpriseTransformationModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="enterpriseTransformationForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Enterprise Transformation Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="enterpriseDigitalizationModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="enterpriseDigitalizationForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Enterprise Digitalization Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="enterpriseLearningSolutionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="enterpriseLearningSolutionForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Enterprise Learning Solutions Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="organizationCapacityAssessmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="organizationCapacityAssessmentForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Organization Capacity Assessment Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hrInfrastructureOfferingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="hrInfrastructureOfferingsForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Operations and HR Infrastructure Offerings Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mergerAndAcquisitionServicesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="mergerAndAcquisitionServicesForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Merger and Acquisition Services Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itServicesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="itServicesModalForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">IT Services Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itInfrastructureServicesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="itInfrastructureServicesForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">IT Infrastructure Services Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cyberSecurityServicesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="cyberSecurityServicesForm">
                        @csrf
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">Cyber Security Services Contact Form</h4>

                        <div id="successAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                            <strong>✅ Success!</strong> <span id="successMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                            <strong>⚠️ Error!</strong> Please fix the highlighted fields below.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-firstname">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback" id="err-lastname">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-email">Please enter a valid email</div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company"
                                placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-company">Company is required</div>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship" name="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Vendor Partner</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <label for="relationship">Relationship with SZORZO <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-relationship">Relationship with Szorzo is required</div>
                        </div>

                        {{-- Phone --}}
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback" id="err-phone">Phone must be exactly 10 digits</div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" name="info"
                                style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-40" id="submitBtn">
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {

            $("#szorzoForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#enterpriseTransformationForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#enterpriseDigitalizationForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#enterpriseLearningSolutionForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#organizationCapacityAssessmentForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#hrInfrastructureOfferingsForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#mergerAndAcquisitionServicesForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#itServicesModalForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#itInfrastructureServicesForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $(document).ready(function() {

            $("#cyberSecurityServicesForm").validate({

                rules: {
                    firstname: {
                        required: true,
                        maxlength: 100
                    },
                    lastname: {
                        required: true,
                        maxlength: 100
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    company: {
                        required: true
                    },
                    relationship: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },

                messages: {
                    firstname: "First name is required",
                    lastname: "Last name is required",
                    email: "Enter valid email",
                    company: "Company is required",
                    relationship: "Select relationship",
                    phone: "Phone must be 10 digits"
                },

                errorElement: "div",
                errorClass: "invalid-feedback",

                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },

                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },

                submitHandler: function(form) {

                    // Show loading spinner
                    $("#submitBtn").prop("disabled", true);
                    $("#submitSpinner").removeClass("d-none");

                    $("#successAlert").addClass("d-none");
                    $("#errorAlert").addClass("d-none");

                    $.ajax({
                        url: "{{ route('contact.store') }}",
                        type: "POST",
                        data: $(form).serialize(),

                        success: function(res) {

                            if (res.success) {

                                // Show success alert
                                $("#successMsg").text(res.message);
                                $("#successAlert")
                                    .removeClass("d-none")
                                    .addClass("show");

                                // Reset form
                                $("#szorzoForm")[0].reset();
                                $(".form-control, .form-select").removeClass("is-invalid");

                                // Auto hide alert
                                setTimeout(function() {
                                    $("#successAlert").fadeOut();
                                }, 4000);

                            }

                        },

                        error: function(xhr) {

                            $("#errorAlert").removeClass("d-none");

                            if (xhr.status === 422) {

                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {

                                    $("#" + key).addClass("is-invalid");
                                    $("#err-" + key).text(value[0]);

                                });

                            }

                        },

                        complete: function() {

                            // Hide spinner
                            $("#submitBtn").prop("disabled", false);
                            $("#submitSpinner").addClass("d-none");

                        }

                    });

                }

            });

        });

        $.fn.commentCards = function() {

            return this.each(function() {

                var $this = $(this),
                    $cards = $this.find('.card'),
                    $current = $cards.filter('.card--current'),
                    $next;

                $cards.on('click', function() {
                    if (!$current.is(this)) {
                        $cards.removeClass('card--current card--out card--next');
                        $current.addClass('card--out');
                        $current = $(this).addClass('card--current');
                        $next = $current.next();
                        $next = $next.length ? $next : $cards.first();
                        $next.addClass('card--next');
                    }
                });

                if (!$current.length) {
                    $current = $cards.last();
                    $cards.first().trigger('click');
                }

                $this.addClass('cards--active');

            })

        };

        const icons = document.querySelectorAll(".country-icon");
        const preview = document.getElementById("locationPreview");
        const previewImg = document.getElementById("previewImg");
        const previewText = document.getElementById("previewText");

        icons.forEach(icon => {

            icon.addEventListener("mouseenter", function() {
                preview.style.display = "block";
                previewImg.src = this.dataset.img;
                previewText.innerText = this.dataset.name;
            });

        });

        document.addEventListener("DOMContentLoaded", function () {

            const iconsContainer = document.querySelector(".country-icons");

            if (iconsContainer) {
                iconsContainer.addEventListener("mouseleave", function () {
                    preview.style.display = "none";
                });
            }

        });
    </script>
@endsection
