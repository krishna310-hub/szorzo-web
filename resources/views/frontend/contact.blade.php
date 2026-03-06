@extends('frontend.includes.master')
@section('content')
    <!-- Page Header Start -->
    <div class="page-header-contact-us-bg">
        <div class="container">
            <div class="row align-items-left">
                <div class="col-lg-6" style="margin-top: 200px;">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 style="color: white; margin-top: 80px; margin-left:-400px" class="wow fadeInUp" data-cursor="-opaque">Contact
                            <span>Us</span>
                        </h1>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <section class="looking-section mt-5">
        <div class="container">

            <div class="text-center section-header">
                <div class="section-title section-title-center">
                    <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">I’m
                        <span style="font-weight: bold;">Looking For</span>
                    </h2>
                </div>
                <p>
                    Please select your area of interest below. An SZORZO representative
                    will contact you shortly after receiving your request.
                </p>
            </div>

            <div class="row looking-grid">

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>SZORZO AI</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Enterprise Transformation</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Enterprise Digitalization</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Enterprise Learning Solutions</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Organization Capacity Assessment</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Operations and HR Infrastructure Offerings</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>IT Infrastructure Services</h5>
                        <span class="underline"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="looking-card">
                        <h5>Merger & Acq Services</h5>
                        <span class="underline"></span>
                    </div>
                </div>

            </div>
        </div>
    </section>

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
@endsection
