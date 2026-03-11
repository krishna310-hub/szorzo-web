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
                            data-cursor="-opaque">HOW CAN WE <br>
                            <span>HELP YOU ?</span>
                        </h1>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <div class="our-approach bg-section mt-5 mb-5">
        <div class="container">

            <div class="text-center section-header">
                <div class="section-title section-title-center">
                    <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
                        <span style="font-weight: bold;">Choose your service and let’s get started</span>
                    </h2>
                </div>
                <p>
                    Please select your area of interest below. An SZORZO experts
                    will contact you shortly after receiving your request.
                </p>
            </div>

            <section id="features" class="section-padding">
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
            </section>
        </div>
    </div>

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

    <div class="page-contact-us mb-5">
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
    </div>

    <div class="modal fade" id="szorzoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="border-bottom:none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 100px; margin-top:-80px">

                    <form id="szorzoForm">
                        <a class="navbar-brand" style="margin-left:-20px">
                            <img src="{{ asset('frontend/images/rhino-logo.png') }}" alt="Logo" width="100">
                            <img src="{{ asset('frontend/images/logo-bg.png') }}" alt="Logo" width="230px"
                                class="logo-second">
                        </a>
                        <h4 class="modal-title mb-3">SZORZO AI Contact Form</h4>

                        <!-- First & Last Name -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstname" placeholder="First Name">
                                    <label for="firstname">First Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback">First name is required</div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastname" placeholder="Last Name">
                                    <label for="lastname">Last Name <span class="text-danger">*</span></label>
                                    <div class="invalid-feedback">Last name is required</div>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" placeholder="Email">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="invalid-feedback">Please enter a valid email</div>
                        </div>

                        <!-- Company -->
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" placeholder="Company">
                            <label for="company">Company <span class="text-danger">*</span></label>
                            <div class="invalid-feedback">Company is required</div>
                        </div>

                        <!-- Relationship Dropdown -->
                        <div class="form-floating mb-4">
                            <select class="form-select" id="relationship">
                                <option value="">Select Relationship with SZORZO</option>
                                <option>Alliance</option>
                                <option>Technology Alliance</option>
                                <option>Customer</option>
                            </select>
                            <div class="invalid-feedback">Relationship with Szorzo is required</div>
                        </div>

                        <!-- Phone -->
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="phone" placeholder="Phone">
                            <label for="phone">Phone <span class="text-danger">*</span></label>
                            <div class="invalid-feedback">Phone is required</div>
                        </div>

                        <!-- Additional Info -->
                        <div class="form-floating mb-4">
                            <textarea class="form-control" placeholder="Additional Information" id="info" style="height:100px"></textarea>
                            <label for="info">Additional Information</label>
                        </div>

                        <button class="btn btn-danger w-40">Submit</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $("#szorzoForm").submit(function(e) {

                e.preventDefault();

                let valid = true;

                let firstname = $("#firstname").val().trim();
                let lastname = $("#lastname").val().trim();
                let email = $("#email").val().trim();
                let company = $("#company").val().trim();
                let relationship = $("#relationship").val();
                let phone = $("#phone").val().trim();

                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let phonePattern = /^[0-9]{10}$/;

                $(".form-control, .form-select").removeClass("is-invalid");

                if (firstname == "") {
                    $("#firstname").addClass("is-invalid");
                    valid = false;
                }

                if (lastname == "") {
                    $("#lastname").addClass("is-invalid");
                    valid = false;
                }

                if (!emailPattern.test(email)) {
                    $("#email").addClass("is-invalid");
                    valid = false;
                }

                if (company == "") {
                    $("#company").addClass("is-invalid");
                    valid = false;
                }

                if (relationship == "") {
                    $("#relationship").addClass("is-invalid");
                    valid = false;
                }

                if (!phonePattern.test(phone)) {
                    $("#phone").addClass("is-invalid");
                    valid = false;
                }

                if (valid) {
                    alert("Form submitted successfully");
                    this.submit();
                }

            });

            $("#szorzoForm input, #szorzoForm select").on("keyup change", function() {
                if ($(this).val().trim() !== "") {
                    $(this).removeClass("is-invalid");
                }
            });

        });
    </script>
@endsection
