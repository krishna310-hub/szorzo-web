@extends('frontend.includes.master')
@section('content')
@include('frontend.includes.banner')
    <!-- About Us Section Start -->
    <div class="about-us">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <!-- Section Title Start -->
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp" style="font-size: x-large;">About Us</h3>
                        <p class="text-effect wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque"
                            style="font-size: xxx-large;">SZORZO is a global business transformation and engineering
                            services partner, enabling businesses to expand, innovate, and
                            thrive particularly in the dynamic Indian market.</p>
                    </div>
                    <!-- Section Title End -->
                </div>
            </div>
        </div>
    </div>
    <!-- About Us Section End -->

    <div class="our-services bg-section">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center">
                        <h3 class="wow fadeInUp" style="font-size: x-large;">INTRODUCTION TO SZORZO A GLOBAL PARTNER
                        </h3>
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">WE INSPIRE THE WORLD BY
                            <br><span style="font-weight: bold;">TAKING ACTION</span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-2.svg')}}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3><a href="service-single.html">Scope</a></h3>
                            <p style="text-align: justify;">SZORZO is a global digital transformation and engineering
                                services
                                partner, enabling businesses to expand, innovate, and thrive
                                particularly in the dynamic Indian market. We specialize in setting up
                                Global Capability Centers (GCCs), market entry & expansion
                                strategies, Market Intelligence, Talent Consolidation & Mapping and
                                delivering technology-led solutions that accelerate strategic
                                business growth.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-3.svg')}}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3><a href="service-single.html">Vision</a></h3>
                            <p style="text-align: justify;">We aspire to be a trusted global digital transformation
                                partner, recognized for building
                                resilent and forward-thinking Technology & Engineering Services models that empower
                                business transformation. Rooted in innovation, deligence, and integrity. we strive to
                                create lasting value for our customers by aligning technology with their evolving goals.
                                Through every engagement. we aim to inspire the world by setting new standards of
                                excellence. cultivating long term partnerships, and divining imactful change across
                                industries.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-service-1.svg')}}" alt="">
                        </div>
                        <div class="service-item-content">
                            <h3><a href="service-single.html">Mission</a></h3>
                            <p style="text-align: justify;">Our mission is to build a profitable and purpose-driven
                                organization from the ground
                                up-anchored in strong ethics, transaparent corporate
                                governance, and social responsibility. We are commited to delivering sustainable value
                                to all our stakeholders, including customers, employees,
                                vendor partners, and the broader community. Through innovation, collaboration, and a
                                relentless focus on excellence, we strive to faster success,
                                drive meaningful impact, and cultivate a culture of integrity and mutual growth &
                                respect.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="our-features">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <div class="section-title">
                        <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">Why Choose
                            <br><span style="font-weight: bold;">SZORZO Technologies ?</span>
                        </h2>
                    </div>
                </div>
                <div class="col-lg-6 mt-5">
                    <p style="text-align: justify;">We provide end-to-end GCC advisory services to global companies
                        looking to establish, acquire, or expand their footprint in India & any other global locations.
                        Our GCC practice is designed to offer strategic, operational, legal, and financial support at
                        every stage of the transaction lifecycle. From identifying opportunities to post-merger
                        integration, we ensure a seamless entry and expansion into the Indian market.</p>
                </div>

                <!-- <div class="">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-1.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>TRULY GLOBAL, <br>DEEPLY LOCAL</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-2.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>COMPLIANCE ACROSS JURISDICTIONS</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp" data-wow-delay="0.4s">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-3.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>INTEGRATED WITH YOUR BUSINESS EXPANSION ROADMAP</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-4.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>MULTILINGUAL, <br>MULTI-JURISDICTIONAL EXPERTS</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-4.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>SEAMLESS GLOBAL COLLABORATION</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="service-item wow fadeInUp" data-wow-delay="0.6s">
                                    <div class="icon-box">
                                        <img src="{{ asset('frontend/images/icon-service-4.svg')}}" alt="">
                                    </div>
                                    <div class="service-item-content">
                                        <h3>PROVEN TRACK RECORD</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="mt-3">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-1.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">TRULY GLOBAL,<br>DEEPLY LOCAL</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-2.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">COMPLIANCE ACROSS<br>JURISDICTIONS</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-3.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">INTEGRATED WITH YOUR<br>BUSINESS EXPANSION ROADMAP</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mt-3">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-4.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">MULTILINGUAL,<br>MULTI-JURISDICTIONAL EXPERTS</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mt-3">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-5.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">SEAMLESS GLOBAL<br>COLLABORATION</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mt-3">
                                <div class="service-box">
                                    <div class="icon-box"> <img src="{{ asset('frontend/images/icon-service-7.svg')}}" alt=""> </div>
                                    <div class="service-box-content section-title">
                                        <h3 style="font-size: 16px;">PROVEN TRACK RECORD</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="company-support-section">
        <div class="section-title" style="justify-items: center;">
            <h2 class="text-anime-style-2" data-cursor="-opaque"><span style="font-weight: bold;">Our Marquee Clients & Partners</span></h2>
        </div>

        <div class="company-support-scrolling-ticker">
            <div class="company-support-scrolling-box">
                <div class="scrolling-content">
                    <span><img src="{{ asset('frontend/images/company-supports-logo-1.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-2.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-3.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-4.png') }}" alt="Company Logo" class="logo-large"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-5.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-6.png') }}" alt="Company Logo" class="logo-large-6"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-7.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supporters-logo-8.svg') }}" alt="Company Logo" class="logo-large-8"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-9.png') }}" alt="Company Logo" class="logo-large-9"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-10.png') }}" alt="Company Logo" class="logo-large-10"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-11.png') }}" alt="Company Logo" class="logo-large-11"></span>
                </div>

                <!-- Duplicate for smooth infinite scroll -->
                <div class="scrolling-content">
                    <span><img src="{{ asset('frontend/images/company-supports-logo-1.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-2.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-3.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-4.png') }}" alt="Company Logo" class="logo-large"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-5.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-6.png') }}" alt="Company Logo" class="logo-large-6"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-7.png') }}" alt="Company Logo"></span>
                    <span><img src="{{ asset('frontend/images/company-supporters-logo-8.svg') }}" alt="Company Logo" class="logo-large-8"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-9.png') }}" alt="Company Logo" class="logo-large-9"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-10.png') }}" alt="Company Logo" class="logo-large-10"></span>
                    <span><img src="{{ asset('frontend/images/company-supports-logo-11.png') }}" alt="Company Logo" class="logo-large-11"></span>
                </div>
            </div>
        </div>

    </div>
    <br>

    <!-- <div class="our-testimonials">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="testimonial-content-box">
                        <div class="section-title">
                            <h2 class="wow fadeInUp" data-cursor="-opaque">Impressive stats backing our <span>AI
                                    solutions</span></h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">We are a forward-thinking AI agency
                                specializing in cutting-edge artificial intelligence and machine learning solutions to
                                build AI-powered tools that solve real-world problems.</p>
                        </div>

                        <div class="testimonial-counters">
                            <div class="testimonial-counter-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-testimonial-counter-1.svg')}}" alt="">
                                </div>
                                <div class="testimonial-counter-content">
                                    <h2><span class="counter">200</span>+</h2>
                                    <p>AI Agency Technology Project Complate</p>
                                </div>
                            </div>

                            <div class="testimonial-counter-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-testimonial-counter-2.svg')}}" alt="">
                                </div>
                                <div class="testimonial-counter-content">
                                    <h2><span class="counter">98</span>%</h2>
                                    <p>Client Satisfaction Rate in Our Agency</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="testimonial-slider-box dark-section">
                        <div class="testimonial-slider-box-content">
                            <div class="section-title">
                                <h3 class="wow fadeInUp">Testimonials</h3>
                                <h2 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">What our client
                                    says</h2>
                            </div>

                            <div class="testimonial-images">
                                <div class="satisfy-client-images">
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('frontend/images/author-1.jpg')}}" alt="">
                                        </figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('frontend/images/author-2.jpg')}}" alt="">
                                        </figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('frontend/images/author-3.jpg')}}" alt="">
                                        </figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('frontend/images/author-4.jpg')}}" alt="">
                                        </figure>
                                    </div>
                                    <div class="satisfy-client-image">
                                        <figure class="image-anime">
                                            <img src="{{ asset('frontend/images/author-5.jpg')}}" alt="">
                                        </figure>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="testimonial-slider">
                            <div class="swiper">
                                <div class="swiper-wrapper" data-cursor-text="Drag">
                                    <div class="swiper-slide">
                                        <div class="testimonial-item">
                                            <div class="testimonial-slider-content">
                                                <div class="testimonial-rating">
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                </div>
                                                <div class="testimonial-content">
                                                    <p>"Working with was a game-changer for our business. They took the
                                                        time understand our unique challenges & solutions that boosted
                                                        our efficiency & the team was knowledgeable, responsive &
                                                        incredibly easy to work with.!"</p>
                                                </div>
                                                <div class="author-content">
                                                    <h3>Sophia Reynolds</h3>
                                                    <p>Founder</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-slider-image">
                                                <figure class="image-anime">
                                                    <img src="{{ asset('frontend/images/testimonial-image-1.jpg')}}" alt="">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="swiper-slide">
                                        <div class="testimonial-item">
                                            <div class="testimonial-slider-content">
                                                <div class="testimonial-rating">
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                </div>
                                                <div class="testimonial-content">
                                                    <p>"Working with was a game-changer for our business. They took the
                                                        time understand our unique challenges & solutions that boosted
                                                        our efficiency & the team was knowledgeable, responsive &
                                                        incredibly easy to work with.!"</p>
                                                </div>
                                                <div class="author-content">
                                                    <h3>Jacob Jones</h3>
                                                    <p>CEO</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-slider-image">
                                                <figure class="image-anime">
                                                    <img src="{{ asset('frontend/images/testimonial-image-2.jpg')}}" alt="">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="swiper-slide">
                                        <div class="testimonial-item">
                                            <div class="testimonial-slider-content">
                                                <div class="testimonial-rating">
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                </div>
                                                <div class="testimonial-content">
                                                    <p>"Working with was a game-changer for our business. They took the
                                                        time understand our unique challenges & solutions that boosted
                                                        our efficiency & the team was knowledgeable, responsive &
                                                        incredibly easy to work with.!"</p>
                                                </div>
                                                <div class="author-content">
                                                    <h3>Olivia bennett</h3>
                                                    <p>Managing director</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-slider-image">
                                                <figure class="image-anime">
                                                    <img src="{{ asset('frontend/images/testimonial-image-3.jpg')}}" alt="">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="testimonial-pagination"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="testimonial-rating-box">
                        <div class="testimonial-rating-item">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-testimonial-rating-1.svg')}}" alt="">
                            </div>
                            <div class="testimonial-rating-content">
                                <p><span class="counter">982</span>+ Trustpilot 4.8 start review</p>
                            </div>
                        </div>

                        <div class="testimonial-rating-item">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-testimonial-rating-2.svg')}}" alt="">
                            </div>
                            <div class="testimonial-rating-content">
                                <p><span class="counter">487</span>+ Airbng 5 start reviews</p>
                            </div>
                        </div>

                        <div class="testimonial-rating-item">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-testimonial-rating-3.svg')}}" alt="">
                            </div>
                            <div class="testimonial-rating-content">
                                <p><span class="counter">182</span>+ Yelp 5 start reviews</p>
                            </div>
                        </div>

                        <div class="testimonial-rating-item">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-testimonial-rating-4.svg')}}" alt="">
                            </div>
                            <div class="testimonial-rating-content">
                                <p><span class="counter">897</span>+ Google 5 start reviews</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

@endsection
