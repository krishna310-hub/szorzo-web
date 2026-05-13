<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <!--====== Title ======-->
    <title>{{ $page->meta_title ?? '' }}</title>

    <!--====== Required meta tags ======-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="canonical" href="{{ url()->current() }}" />

    <meta name="description" content="{!! $page->meta_description !!}">
    <meta name="keywords" content="{!! $page->meta_keyword !!}">
    <meta name="msvalidate.01" content="ED6B0D966B8C929AEC3D052969BEE2A9" />

    <meta name="robots" content="INDEX,FOLLOW">
    <meta name="Bingbot" content="INDEX,FOLLOW">
    <meta name="googlebot" content="INDEX, FOLLOW">

    <!-- Open Graph (SEO + Social) -->
    <meta property="og:title" content="{{ $page->meta_title }}">
    <meta property="og:type" content="website" />
    <meta property="og:description" content="{!! $page->meta_description !!}">
    <meta property="og:image" content="https://szorzo.com/public/frontend/images/logo-bg.png">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: 'var(--primary-color)',
                        secondary: 'var(--secondary-color)',
                        accent: 'var(--accent-color)',
                        'accent-secondary': 'var(--accent-secondary-color)',
                        'divider': 'var(--divider-color)',
                        'dark-divider': 'var(--dark-divider-color)',
                    },
                    fontFamily: {
                        oswald: ['Oswald', 'sans-serif'],
                        montserrat: ['Montserrat', 'sans-serif'],
                        jakarta: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-10px)'
                            },
                        }
                    }
                }
            }
        }
    </script>
    @php
        $faqs = json_decode($page->faqs, true);

        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [],
        ];

        if (is_array($faqs)) {
            foreach ($faqs as $faq) {
                // Split questions and answers by "/"
                $questions = array_map('trim', explode('/ ', $faq['question'] ?? ''));
                $answers = array_map('trim', explode('/ ', $faq['answer'] ?? ''));

                // Pair questions with answers
                foreach ($questions as $index => $question) {
                    if (!empty($question) && !empty($answers[$index])) {
                        $faqSchema['mainEntity'][] = [
                            '@type' => 'Question',
                            'name' => strip_tags($question),
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => strip_tags($answers[$index]),
                            ],
                        ];
                    }
                }
            }
        }
    @endphp

    @if (!empty($faqSchema['mainEntity']))
        <script type="application/ld+json">
    {!! json_encode($faqSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @verbatim
        <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "No.1 Award Winning Digital Marketing Agency and Company",
    "image": "https://szorzo.com/public/frontend/images/logo-bg.png",
    "description": "We are the Best digital marketing agency delivering powerful strategies that grow brands, increase visibility, and drive real revenue.",
    "sku": "NIL",
    "mpn": "NIL",
    "brand": {
        "@type": "Brand",
        "name": "Webbitech.tech"
    },
    "logo": "https://szorzo.com/public/frontend/images/logo-bg.png",
    "category": "Digital Marketing and Branding",
    "review": {
        "@type": "Review",
        "reviewRating": {
        "@type": "Rating",
        "ratingValue": "4.9",
        "bestRating": "5"
        },
        "author": {
        "@type": "Person",
        "name": "Ranjith Kumar"
        }
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "3000"
    }
    }
    </script>
    @endverbatim
    <style>
        :root {
            --primary-color: #111111;
            --secondary-color: #f0f2f4;
            --text-color: #333333;
            --accent-color: #dc2626;
            --accent-secondary-color: #000000;
            --white-color: #ffffff;
            --divider-color: #1111111a;
            --dark-divider-color: #ffffff10;
            --error-color: rgb(230, 87, 87);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-color);
            background-color: var(--white-color);
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Oswald', sans-serif;
        }

        .hero-bg {
            background-image: linear-gradient(to right, rgba(17, 17, 17, 0.95) 0%, rgba(17, 17, 17, 0.7) 100%), url('https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .glass-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .glass-input {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .glass-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.2);
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* * DYNAMIC CONTENT TYPOGRAPHY SYSTEM * */
        .dynamic-content {
            color: var(--text-color);
            line-height: 1.8;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dynamic-content h1,
        .dynamic-content h2,
        .dynamic-content h3,
        .dynamic-content h4,
        .dynamic-content h5,
        .dynamic-content h6 {
            font-family: 'Oswald', sans-serif;
            color: var(--primary-color);
            margin-top: 2em;
            margin-bottom: 0.75em;
            font-weight: 600;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .dynamic-content h1 {
            font-size: 2.5rem;
            color: var(--accent-color);
        }

        .dynamic-content h2 {
            font-size: 2rem;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.25em;
            display: inline-block;
        }

        .dynamic-content h3 {
            font-size: 1.75rem;
            color: var(--accent-color);
        }

        .dynamic-content h4 {
            font-size: 1.25rem;
            border-left: 3px solid var(--accent-color);
            padding-left: 10px;
        }

        .dynamic-content p {
            margin-bottom: 1.25em;
            font-size: 1.05rem;
            color: #4b5563;
        }

        .dynamic-content ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 1.5em;
        }

        .dynamic-content ul li {
            position: relative;
            padding-left: 1.75em;
            margin-bottom: 0.75em;
        }

        .dynamic-content ul li::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--accent-color);
        }

        .dynamic-content ol {
            list-style-type: decimal;
            padding-left: 1.2em;
            margin-bottom: 1.5em;
            font-weight: 600;
        }

        .dynamic-content ol li {
            margin-bottom: 0.5em;
            padding-left: 0.5em;
        }

        .dynamic-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 2em 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .dynamic-content th,
        .dynamic-content td {
            border: 1px solid var(--divider-color);
            padding: 1rem;
            text-align: left;
        }

        .dynamic-content th {
            background-color: var(--primary-color);
            color: var(--white-color);
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dynamic-content tr:nth-child(even) {
            background-color: var(--secondary-color);
        }

        .dynamic-content tr:hover {
            background-color: #fef2f2;
            transition: background-color 0.2s ease;
        }

        .dynamic-content blockquote {
            border-left: 4px solid var(--accent-color);
            background: var(--secondary-color);
            padding: 1.5rem;
            margin: 2em 0;
            font-style: italic;
            border-radius: 0 8px 8px 0;
            color: #555;
        }

        .dynamic-content a {
            color: var(--accent-color);
            text-decoration: underline;
            text-underline-offset: 4px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .dynamic-content a:hover {
            color: var(--primary-color);
        }

        /* * END DYNAMIC CONTENT TYPOGRAPHY SYSTEM * */

        .nav-scrolled {
            background-color: rgba(17, 17, 17, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .faq-answer {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows 0.3s ease-out, opacity 0.3s ease-out;
        }

        .faq-answer>div {
            overflow: hidden;
        }

        .faq-item.active .faq-answer {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
            color: var(--accent-color);
        }

        .wa-btn {
            box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            animation: waPulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes waPulse {
            to {
                box-shadow: 0 0 0 20px rgba(37, 211, 102, 0);
            }
        }

        .bento-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .bento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--accent-color);
        }
    </style>
</head>

<body class="antialiased selection:bg-accent selection:text-white">

    <nav id="navbar"
        class="fixed w-full z-50 transition-all duration-300 py-4 border-b border-white/10 bg-primary/80 md:bg-transparent backdrop-blur-sm md:backdrop-blur-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center relative">

                <div class="logo flex-shrink-0 z-50">
                    <a class="navbar-brand flex items-center gap-2 md:gap-3" href="http://127.0.0.1:8000">
                        <img src="http://127.0.0.1:8000/frontend/images/rhino-logo.png" alt="Logo"
                            class="w-12 sm:w-16 md:w-[80px] h-auto object-contain">
                        <img src="http://127.0.0.1:8000/frontend/images/logo-bg.png" alt="Logo"
                            class="w-24 sm:w-32 md:w-[230px] h-auto object-contain logo-second">
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#services"
                        class="text-white hover:text-accent font-oswald tracking-wide transition-colors text-sm uppercase">Services</a>
                    <a href="#why-us"
                        class="text-white hover:text-accent font-oswald tracking-wide transition-colors text-sm uppercase">Why
                        Choose Us</a>
                    <a href="#benefits"
                        class="text-white hover:text-accent font-oswald tracking-wide transition-colors text-sm uppercase">Methodology</a>
                    <a href="#contact"
                        class="px-6 py-2 bg-accent text-white font-oswald tracking-wide text-sm uppercase rounded hover:bg-white hover:text-primary transition-all duration-300">Get
                        Started</a>
                </div>

                <div class="md:hidden flex items-center z-50">
                    <button id="mobile-menu-btn" class="text-white focus:outline-none p-2"
                        aria-label="Toggle Navigation">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu"
            class="hidden md:hidden absolute top-full left-0 w-full bg-[#111111] border-b border-white/10 shadow-2xl transition-all duration-300 origin-top">
            <div class="px-6 py-6 space-y-4 flex flex-col">
                <a href="#services"
                    class="mobile-link text-white hover:text-accent font-oswald tracking-wide transition-colors text-lg uppercase py-2 border-b border-white/5">Services</a>
                <a href="#why-us"
                    class="mobile-link text-white hover:text-accent font-oswald tracking-wide transition-colors text-lg uppercase py-2 border-b border-white/5">Why
                    Choose Us</a>
                <a href="#benefits"
                    class="mobile-link text-white hover:text-accent font-oswald tracking-wide transition-colors text-lg uppercase py-2 border-b border-white/5">Methodology</a>
                <a href="#contact"
                    class="mobile-link mt-4 text-center px-6 py-3 bg-accent text-white font-oswald tracking-wide text-lg uppercase rounded active:scale-95 transition-transform">Get
                    Started</a>
            </div>
        </div>
    </nav>

    <header class="hero-bg min-h-[100vh] flex items-center relative pt-28 md:pt-[140px] pb-12">
        <div class="max-w-7xl mx-auto px-4 w-full relative z-10">
            <div class="grid lg:grid-cols-12 gap-10 md:gap-12 items-center">

                <div class="lg:col-span-7 text-left order-2 lg:order-1" data-aos="fade-right" data-aos-duration="1000">
                    <p
                        class="text-accent font-oswald tracking-[0.2em] font-bold text-xs sm:text-sm mb-4 uppercase flex items-center gap-4">
                        <span class="w-8 md:w-12 h-px bg-accent"></span>
                        Welcome to Szorzo
                    </p>
                    <h1
                        class="text-4xl sm:text-5xl lg:text-7xl font-oswald font-bold text-white leading-[1.1] mb-4 md:mb-6 uppercase">
                        GCC Services in India –<br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 text-3xl sm:text-4xl lg:text-6xl block mt-2">Build
                            Your Global Capability Center</span>
                    </h1>

                    <div class="space-y-4 md:space-y-6 text-gray-300 font-jakarta text-base md:text-lg">
                        <p>
                            <strong class="text-white">SZORZO</strong> is a trusted global business transformation and
                            engineering services partner helping organizations establish and scale <strong>Global
                                Capability Centers (GCCs) in India</strong>.
                        </p>
                        <p class="font-light">
                            We support businesses with end-to-end GCC setup, expansion strategies, market intelligence,
                            talent acquisition, and operational excellence. Built to scale, structured for long-term
                            growth.
                        </p>
                    </div>

                    <div class="mt-8 md:mt-10 flex items-center gap-4 md:gap-6">
                        <div class="flex -space-x-3 md:-space-x-4">
                            <img class="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 border-primary"
                                src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=100&q=80"
                                alt="Consultant">
                            <img class="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 border-primary"
                                src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=100&q=80"
                                alt="Consultant">
                            <img class="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 border-primary"
                                src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=100&q=80"
                                alt="Consultant">
                            <div
                                class="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 border-primary bg-accent flex items-center justify-center text-white font-bold text-[10px] md:text-xs">
                                +25Y</div>
                        </div>
                        <p class="text-xs md:text-sm font-oswald text-gray-300 uppercase tracking-wide">Combined
                            Global<br><span class="text-white font-bold">Consulting Expertise</span></p>
                    </div>
                </div>

                <div class="lg:col-span-5 order-1 lg:order-2 mb-8 lg:mb-0" data-aos="fade-left" data-aos-duration="1000"
                    data-aos-delay="200">
                    <div class="glass-form p-6 md:p-10 rounded-2xl relative overflow-hidden">
                        <div
                            class="absolute -top-10 -right-10 w-32 h-32 bg-accent blur-[50px] opacity-40 pointer-events-none">
                        </div>

                        <h3 class="text-xl md:text-2xl font-oswald text-white uppercase mb-2">Request a Free
                            Consultation</h3>
                        <p class="text-gray-400 text-xs md:text-sm mb-6 md:mb-8 font-jakarta">Fill out the form below
                            and our GCC experts will reach out to you within 24 hours.</p>

                        <form class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-oswald text-gray-400 uppercase tracking-wide mb-1">First
                                        Name</label>
                                    <input type="text" class="glass-input w-full px-4 py-3 rounded text-sm"
                                        placeholder="John">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-oswald text-gray-400 uppercase tracking-wide mb-1">Last
                                        Name</label>
                                    <input type="text" class="glass-input w-full px-4 py-3 rounded text-sm"
                                        placeholder="Doe">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-oswald text-gray-400 uppercase tracking-wide mb-1">Work
                                    Email</label>
                                <input type="email" class="glass-input w-full px-4 py-3 rounded text-sm"
                                    placeholder="john@company.com">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-oswald text-gray-400 uppercase tracking-wide mb-1">Phone
                                    Number</label>
                                <input type="tel" class="glass-input w-full px-4 py-3 rounded text-sm"
                                    placeholder="+1 (555) 000-0000">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-oswald text-gray-400 uppercase tracking-wide mb-1">Service
                                    Required</label>
                                <select class="glass-input w-full px-4 py-3 rounded text-sm appearance-none">
                                    <option value="" class="text-black">Select a service...</option>
                                    <option value="gcc-setup" class="text-black">GCC Setup & Advisory</option>
                                    <option value="market-entry" class="text-black">Market Entry Strategy</option>
                                    <option value="talent" class="text-black">Talent Consolidation</option>
                                    <option value="compliance" class="text-black">Legal & Compliance</option>
                                </select>
                            </div>

                            <button type="button"
                                class="w-full bg-accent hover:bg-red-700 text-white font-oswald uppercase tracking-widest py-4 rounded transition-all duration-300 mt-4 shadow-lg shadow-red-500/30 group active:scale-[0.98]">
                                Submit Request
                                <i
                                    class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <main class="bg-white">

        <section id="services" class="py-16 md:py-24 px-4 bg-gray-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-4xl mx-auto mb-12 md:mb-16" data-aos="fade-up">
                    <p class="text-accent font-oswald tracking-widest text-xs md:text-sm font-bold uppercase mb-4">•
                        Our Core Offerings •</p>
                    <h2
                        class="text-3xl md:text-4xl lg:text-5xl font-oswald text-primary uppercase leading-tight mb-4 md:mb-6">
                        End-to-End GCC Setup and Expansion Services in India
                    </h2>
                    <p class="text-gray-600 text-base md:text-lg font-jakarta">
                        SZORZO provides complete GCC advisory and setup services for global organizations looking to
                        establish, acquire, or expand operations in India. We ensure a smooth and efficient setup
                        process, reducing risks and accelerating market entry.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="100">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-building text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">GCC Setup & Advisory
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed">End-to-end consulting to establish your
                            offshore capability center from planning to execution efficiently.</p>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="200">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-map-location-dot text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">Market Entry Strategy
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Data-driven, highly localized approaches and
                            strategic mapping for successful expansion into the Indian market.</p>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="300">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-users-gear text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">Talent Consolidation
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Comprehensive workforce mapping, hierarchy
                            structuring, and acquisition of top-tier talent tailored to your needs.</p>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="400">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-scale-balanced text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">Legal & Compliance</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Navigating complex multi-jurisdictional
                            regulatory frameworks and ensuring total local and global compliance.</p>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="500">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-network-wired text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">Operational Setup</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Complete physical and IT infrastructure
                            planning, setup, and ensuring day-one operational readiness.</p>
                    </div>

                    <div class="bg-white p-6 md:p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-shadow group"
                        data-aos="fade-up" data-aos-delay="600">
                        <div
                            class="w-12 h-12 md:w-14 md:h-14 bg-red-50 rounded-lg flex items-center justify-center mb-5 md:mb-6 text-accent group-hover:bg-accent group-hover:text-white transition-colors duration-300">
                            <i class="fa-solid fa-rocket text-xl md:text-2xl"></i>
                        </div>
                        <h3 class="font-oswald text-lg md:text-xl text-primary uppercase mb-3">Business Transformation
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Strategic consulting to modernize operations,
                            integrate technologies, and structure post-merger integrations.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="why-us" class="py-16 md:py-24 px-4 bg-white relative">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row gap-12 lg:gap-16">

                    <div class="lg:w-1/3">
                        <div class="lg:sticky lg:top-32" data-aos="fade-right">
                            <h3
                                class="text-3xl md:text-4xl lg:text-5xl font-oswald text-primary uppercase leading-tight mb-6">
                                Why Choose <span class="text-accent">SZORZO</span> for GCC Services?
                            </h3>
                            <p
                                class="text-gray-600 font-jakarta mb-8 lg:mb-10 border-l-4 border-accent pl-4 text-sm md:text-base">
                                SZORZO combines global expertise with local market knowledge to deliver scalable
                                business solutions. We help enterprises build resilient and future-ready operations.
                            </p>

                            <div
                                class="bg-primary text-white p-6 md:p-8 rounded-xl shadow-xl border-b-4 border-accent relative overflow-hidden hidden sm:block">
                                <div class="absolute -right-4 -bottom-4 opacity-10">
                                    <i class="fa-solid fa-chart-line text-8xl md:text-9xl"></i>
                                </div>
                                <h4 class="font-oswald text-lg md:text-xl uppercase mb-6 text-accent relative z-10">
                                    Business Growth Highlights</h4>
                                <ul
                                    class="space-y-3 md:space-y-4 relative z-10 font-oswald uppercase text-xs md:text-sm tracking-wide">
                                    <li class="flex justify-between border-b border-gray-700 pb-2">
                                        <span class="text-gray-400">Consulting Exp.</span>
                                        <span class="font-bold text-white text-base md:text-lg">25+ Years</span>
                                    </li>
                                    <li class="flex justify-between border-b border-gray-700 pb-2">
                                        <span class="text-gray-400">Transaction Value</span>
                                        <span class="font-bold text-white text-base md:text-lg">200 Cr</span>
                                    </li>
                                    <li class="flex justify-between border-b border-gray-700 pb-2">
                                        <span class="text-gray-400">Trusted Customers</span>
                                        <span class="font-bold text-white text-base md:text-lg">100+</span>
                                    </li>
                                    <li class="flex justify-between border-b border-gray-700 pb-2">
                                        <span class="text-gray-400">Talent Deployed</span>
                                        <span class="font-bold text-white text-base md:text-lg">500+</span>
                                    </li>
                                    <li class="flex justify-between pb-2">
                                        <span class="text-gray-400">Global Locations</span>
                                        <span class="font-bold text-white text-base md:text-lg">10+</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="lg:w-2/3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">

                            <div class="bento-card bg-gray-50 border border-gray-200 p-6 md:p-8 rounded-xl"
                                data-aos="fade-up" data-aos-delay="100">
                                <i
                                    class="fa-solid fa-earth-americas text-2xl md:text-3xl text-blue-500 mb-3 md:mb-4"></i>
                                <h4 class="font-oswald text-lg md:text-xl uppercase text-primary mb-2">Truly
                                    Global,<br>Deeply Local</h4>
                                <p class="text-gray-500 text-xs md:text-sm">Operating with a global mindset while
                                    maintaining deep understanding of local nuances, labor laws, and cultural dynamics
                                    in the Indian market.</p>
                            </div>

                            <div class="bento-card bg-gray-50 border border-gray-200 p-6 md:p-8 rounded-xl"
                                data-aos="fade-up" data-aos-delay="200">
                                <i
                                    class="fa-solid fa-clipboard-check text-2xl md:text-3xl text-green-500 mb-3 md:mb-4"></i>
                                <h4 class="font-oswald text-lg md:text-xl uppercase text-primary mb-2">Compliance
                                    Across Jurisdictions</h4>
                                <p class="text-gray-500 text-xs md:text-sm">Expert handling of cross-border legalities,
                                    tax structuring, and statutory compliance required to run a captive center smoothly.
                                </p>
                            </div>

                            <div class="bento-card sm:col-span-2 bg-gradient-to-br from-primary to-[#2a0808] p-6 md:p-10 rounded-xl border border-gray-800 text-white relative overflow-hidden"
                                data-aos="fade-up" data-aos-delay="300">
                                <i
                                    class="fa-solid fa-route absolute right-0 bottom-0 text-7xl md:text-9xl text-white opacity-5 -mr-5 md:-mr-10 -mb-5 md:-mb-10 transform -rotate-12"></i>

                                <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                                    <div class="md:w-1/3">
                                        <i
                                            class="fa-solid fa-road-circle-check text-4xl md:text-5xl text-accent mb-3 md:mb-4"></i>
                                        <h4 class="font-oswald text-xl md:text-2xl uppercase text-white leading-tight">
                                            Integrated Expansion Roadmap</h4>
                                    </div>
                                    <div class="md:w-2/3">
                                        <p class="text-gray-300 text-sm leading-relaxed">
                                            We don't just set up an office; we build an integrated business expansion
                                            roadmap. Our multi-disciplinary teams ensure your GCC aligns perfectly with
                                            your global corporate strategy, integrating engineering, IT, HR, and
                                            facilities into one cohesive unit.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bento-card bg-gray-50 border border-gray-200 p-6 md:p-8 rounded-xl"
                                data-aos="fade-up" data-aos-delay="400">
                                <i class="fa-solid fa-language text-2xl md:text-3xl text-purple-500 mb-3 md:mb-4"></i>
                                <h4 class="font-oswald text-lg md:text-xl uppercase text-primary mb-2">Multilingual
                                    Experts</h4>
                                <p class="text-gray-500 text-xs md:text-sm">Communication without barriers. Our team of
                                    multi-jurisdictional experts bridges the gap between HQ and the offshore center
                                    seamlessly.</p>
                            </div>

                            <div class="bento-card bg-gray-50 border border-gray-200 p-6 md:p-8 rounded-xl"
                                data-aos="fade-up" data-aos-delay="500">
                                <i class="fa-solid fa-trophy text-2xl md:text-3xl text-orange-500 mb-3 md:mb-4"></i>
                                <h4 class="font-oswald text-lg md:text-xl uppercase text-primary mb-2">Proven Track
                                    Record</h4>
                                <p class="text-gray-500 text-xs md:text-sm">70+ Global Delivery Awards and successful
                                    RPO projects validate our execution capability across highly demanding sectors.</p>
                            </div>
                        </div>

                        <div class="sm:hidden mt-8 bg-primary text-white p-6 rounded-xl shadow-xl border-b-4 border-accent relative overflow-hidden"
                            data-aos="fade-up">
                            <h4 class="font-oswald text-lg uppercase mb-4 text-accent relative z-10">Business Growth
                                Highlights</h4>
                            <ul class="space-y-3 relative z-10 font-oswald uppercase text-xs tracking-wide">
                                <li class="flex justify-between border-b border-gray-700 pb-2"><span
                                        class="text-gray-400">Consulting Exp.</span><span class="font-bold">25+
                                        Years</span></li>
                                <li class="flex justify-between border-b border-gray-700 pb-2"><span
                                        class="text-gray-400">Transaction</span><span class="font-bold">200 Cr</span>
                                </li>
                                <li class="flex justify-between border-b border-gray-700 pb-2"><span
                                        class="text-gray-400">Customers</span><span class="font-bold">100+</span></li>
                                <li class="flex justify-between border-b border-gray-700 pb-2"><span
                                        class="text-gray-400">Talent</span><span class="font-bold">500+</span></li>
                                <li class="flex justify-between pb-2"><span
                                        class="text-gray-400">Locations</span><span class="font-bold">10+</span></li>
                            </ul>
                        </div>

                        <div class="mt-8 md:mt-12 bg-white border border-gray-200 p-6 md:p-8 rounded-xl"
                            data-aos="fade-up">
                            <h4
                                class="font-oswald text-xl md:text-2xl uppercase text-primary mb-4 md:mb-6 border-b border-gray-100 pb-3 md:pb-4">
                                Industries We Support</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-3 md:gap-y-4 gap-x-2">
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">IT & BPM Services</span>
                                </div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Banking &
                                        Financial</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Healthcare &
                                        Pharma</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Manufacturing &
                                        Eng.</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Aviation & Defence</span>
                                </div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Infra & Real
                                        Estate</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Retail &
                                        E-Commerce</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Media &
                                        Entertainment</span></div>
                                <div class="flex items-center gap-2"><i
                                        class="fa-solid fa-check text-accent text-[10px] md:text-xs"></i><span
                                        class="text-xs md:text-sm font-jakarta text-gray-600">Renewable Energy</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- DYNAMIC CONTENT: BENEFITS & METHODOLOGY SECTION -->
        <section id="benefits" class="px-4 bg-[#fffafa] border-y border-red-50">
            <div class="max-w-7xl mx-auto dynamic-content px-2 sm:px-0" data-aos="fade-up">

                <h2>Comprehensive Setup Framework</h2>
                <p>Partnering with SZORZO ensures that your transition into the Indian market is not just seamless, but
                    strategically advantageous. We focus on long-term value creation and operational resilience.</p>

                <!-- Dynamic Unordered List -->
                <ul class="text-sm md:text-base">
                    <li><strong>Faster market entry into India:</strong> Bypass administrative bottlenecks with our
                        established operational frameworks.</li>
                    <li><strong>Reduced operational and compliance risks:</strong> Shield your parent organization from
                        local regulatory complexities.</li>
                    <li><strong>Access to top-tier talent:</strong> Leverage our extensive RPO experience for workforce
                        planning and executive hiring.</li>
                    <li><strong>Scalable infrastructure support:</strong> Flexible physical and IT setups that grow as
                        your team expands.</li>
                </ul>

                <h3>Implementation Timeline & Phases</h3>
                <p>We believe in transparency and structured execution. Our frameworks are designed to mitigate risks
                    while accelerating your go-to-market strategy. Below is a standard phased approach to setting up
                    your global capability center.</p>

                <!-- Dynamic Table -->
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Phase</th>
                                <th>Timeline</th>
                                <th>Key Deliverables & Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Phase 1: Discovery & Strategy</strong></td>
                                <td>Weeks 1-4</td>
                                <td>Market Feasibility, Location Strategy, Legal Entity Structuring</td>
                            </tr>
                            <tr>
                                <td><strong>Phase 2: Setup & Infrastructure</strong></td>
                                <td>Weeks 5-12</td>
                                <td>Office Space Procurement, IT Setup, Statutory Registrations</td>
                            </tr>
                            <tr>
                                <td><strong>Phase 3: Talent Acquisition</strong></td>
                                <td>Weeks 8-16</td>
                                <td>Leadership Hiring, Core Team Onboarding, HR Policies Formulation</td>
                            </tr>
                            <tr>
                                <td><strong>Phase 4: Go-Live & Ops</strong></td>
                                <td>Week 16 onwards</td>
                                <td>Day 1 Readiness, Payroll Management, Ongoing Operational Support</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4>Client Success Testimonial</h4>
                <!-- Dynamic Blockquote -->
                <blockquote>
                    "SZORZO didn't just help us open an office in India; they built an extension of our global
                    headquarters. Their holistic approach to legal, HR, and IT infrastructure saved us months of trial
                    and error."
                    <br><br><span class="font-bold text-primary">— CTO, Fortune 500 Enterprise</span>
                </blockquote>

                <p>Ready to explore your options? Review our <a href="#faq">Frequently Asked Questions</a> below or
                    <a href="#contact">contact our advisory team</a> directly for a personalized consultation regarding
                    your business needs.
                </p>

            </div>
        </section>

        <section id="faq" class="py-16 md:py-24 px-4 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-10 md:mb-16" data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-oswald text-primary uppercase mb-3 md:mb-4">
                        Frequently Asked Questions</h2>
                    <p class="text-gray-600 font-jakarta text-sm md:text-base">Everything you need to know about
                        establishing a Global Capability Center.</p>
                </div>

                <div class="space-y-3 md:space-y-4" data-aos="fade-up" data-aos-delay="100">
                    <div
                        class="faq-item bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-colors hover:border-accent">
                        <button
                            class="w-full text-left px-4 md:px-6 py-4 md:py-5 flex justify-between items-center focus:outline-none"
                            onclick="toggleFaq(this)">
                            <span
                                class="faq-question-text font-oswald text-base md:text-lg text-primary transition-colors uppercase tracking-wide pr-4">1.
                                What is a Global Capability Center (GCC)?</span>
                            <i
                                class="fa-solid fa-chevron-down faq-icon text-gray-400 transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer">
                            <div
                                class="px-4 md:px-6 pb-4 md:pb-5 text-gray-600 font-jakarta border-t border-gray-200 pt-3 md:pt-4 text-xs md:text-sm leading-relaxed">
                                A GCC is an offshore or captive center established by global companies to manage
                                business operations, technology, engineering, and support services efficiently. Unlike
                                traditional outsourcing, a GCC is a wholly-owned entity that retains intellectual
                                property and drives core innovation.
                            </div>
                        </div>
                    </div>

                    <div
                        class="faq-item bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-colors hover:border-accent">
                        <button
                            class="w-full text-left px-4 md:px-6 py-4 md:py-5 flex justify-between items-center focus:outline-none"
                            onclick="toggleFaq(this)">
                            <span
                                class="faq-question-text font-oswald text-base md:text-lg text-primary transition-colors uppercase tracking-wide pr-4">2.
                                How can SZORZO help with GCC setup in India?</span>
                            <i
                                class="fa-solid fa-chevron-down faq-icon text-gray-400 transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer">
                            <div
                                class="px-4 md:px-6 pb-4 md:pb-5 text-gray-600 font-jakarta border-t border-gray-200 pt-3 md:pt-4 text-xs md:text-sm leading-relaxed">
                                SZORZO provides end-to-end GCC advisory, setup, legal compliance, talent acquisition,
                                and operational support. We act as your local implementation partner, managing the
                                complexities of real estate, IT infrastructure, recruitment, and regulatory compliance
                                on your behalf.
                            </div>
                        </div>
                    </div>

                    <div
                        class="faq-item bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-colors hover:border-accent">
                        <button
                            class="w-full text-left px-4 md:px-6 py-4 md:py-5 flex justify-between items-center focus:outline-none"
                            onclick="toggleFaq(this)">
                            <span
                                class="faq-question-text font-oswald text-base md:text-lg text-primary transition-colors uppercase tracking-wide pr-4">3.
                                Which industries does SZORZO support?</span>
                            <i
                                class="fa-solid fa-chevron-down faq-icon text-gray-400 transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer">
                            <div
                                class="px-4 md:px-6 pb-4 md:pb-5 text-gray-600 font-jakarta border-t border-gray-200 pt-3 md:pt-4 text-xs md:text-sm leading-relaxed">
                                We support a diverse range of industries including IT & BPM, Banking & Financial
                                Services (BFSI), Healthcare & Pharmaceuticals, Manufacturing & Engineering, Aviation,
                                Telecom, Retail, and Renewable Energy.
                            </div>
                        </div>
                    </div>

                    <div
                        class="faq-item bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-colors hover:border-accent">
                        <button
                            class="w-full text-left px-4 md:px-6 py-4 md:py-5 flex justify-between items-center focus:outline-none"
                            onclick="toggleFaq(this)">
                            <span
                                class="faq-question-text font-oswald text-base md:text-lg text-primary transition-colors uppercase tracking-wide pr-4">4.
                                Why should companies choose India for GCC setup?</span>
                            <i
                                class="fa-solid fa-chevron-down faq-icon text-gray-400 transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer">
                            <div
                                class="px-4 md:px-6 pb-4 md:pb-5 text-gray-600 font-jakarta border-t border-gray-200 pt-3 md:pt-4 text-xs md:text-sm leading-relaxed">
                                India offers an unmatched combination of scale and quality: a massive pool of
                                English-speaking STEM graduates, significant cost arbitrage, a mature IT and real estate
                                infrastructure, and favorable government policies actively encouraging foreign direct
                                investment.
                            </div>
                        </div>
                    </div>

                    <div
                        class="faq-item bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-colors hover:border-accent">
                        <button
                            class="w-full text-left px-4 md:px-6 py-4 md:py-5 flex justify-between items-center focus:outline-none"
                            onclick="toggleFaq(this)">
                            <span
                                class="faq-question-text font-oswald text-base md:text-lg text-primary transition-colors uppercase tracking-wide pr-4">5.
                                What makes SZORZO different from other firms?</span>
                            <i
                                class="fa-solid fa-chevron-down faq-icon text-gray-400 transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer">
                            <div
                                class="px-4 md:px-6 pb-4 md:pb-5 text-gray-600 font-jakarta border-t border-gray-200 pt-3 md:pt-4 text-xs md:text-sm leading-relaxed">
                                We go beyond advisory. SZORZO combines high-level strategic consulting with "boots on
                                the ground" execution capability. Our integrated approach—handling legal, HR, IT, and
                                facilities under one roof—ensures accountability and faster time-to-market compared to
                                managing multiple disjointed vendors.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-16 md:py-24 bg-primary relative overflow-hidden px-4">
            <div
                class="absolute top-0 right-0 w-48 md:w-64 h-48 md:h-64 border-[30px] md:border-[40px] border-[#1a1a1a] rounded-full transform translate-x-1/3 -translate-y-1/3 opacity-50">
            </div>
            <div
                class="absolute bottom-0 left-0 w-64 md:w-80 h-64 md:h-80 border-[30px] md:border-[50px] border-accent rounded-full transform -translate-x-1/2 translate-y-1/2 opacity-20">
            </div>

            <div class="max-w-4xl mx-auto text-center relative z-10" data-aos="zoom-in">
                <h2
                    class="text-3xl sm:text-4xl md:text-6xl font-oswald text-white uppercase mb-4 md:mb-6 leading-tight">
                    Ready to Set Up Your <br><span class="text-accent">GCC in India?</span>
                </h2>
                <p
                    class="text-base md:text-xl text-gray-300 mb-8 md:mb-10 font-jakarta max-w-2xl mx-auto px-4 sm:px-0">
                    Partner with SZORZO to establish, scale, and optimize your Global Capability Center with confidence.
                    Get in touch with our experts today.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
                    <button
                        class="bg-accent text-white font-oswald uppercase tracking-widest text-sm md:text-lg px-8 py-4 hover:bg-red-700 transition-all duration-300 shadow-lg shadow-red-500/30 w-full sm:w-auto">
                        Consult an Expert
                    </button>
                    <button
                        class="bg-transparent text-white font-oswald uppercase tracking-widest text-sm md:text-lg px-8 py-4 border border-white hover:bg-white hover:text-primary transition-all duration-300 w-full sm:w-auto">
                        Download Brochure
                    </button>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#0a0a0a] pt-16 md:pt-20 pb-8 md:pb-10 border-t border-gray-800 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10 md:gap-12 mb-12 md:mb-16">
                <div class="col-span-1 sm:col-span-2 md:col-span-1">
                    <div class="logo flex items-center gap-2 mb-6">
                        <a class="flex items-center gap-2" href="http://127.0.0.1:8000">
                            <img src="http://127.0.0.1:8000/frontend/images/rhino-logo.png" alt="Logo"
                                class="w-12 h-auto object-contain brightness-0 invert opacity-90">
                            <img src="http://127.0.0.1:8000/frontend/images/logo-bg.png" alt="Logo"
                                class="w-24 h-auto object-contain brightness-0 invert opacity-90">
                        </a>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm font-jakarta mb-6 leading-relaxed">
                        Global business transformation and engineering services partner, enabling businesses to expand,
                        innovate, and thrive.
                    </p>
                </div>

                <div>
                    <h5 class="text-white font-oswald uppercase tracking-wider mb-4 md:mb-6 text-sm">Quick Links</h5>
                    <ul class="space-y-2 md:space-y-3">
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">GCC
                                Services</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">SZORZO
                                AI</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Telecom
                                Services</a></li>
                        <li><a href="#about"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">About
                                Us</a></li>
                        <li><a href="#contact"
                                class="text-accent hover:text-white transition-colors text-xs md:text-sm font-jakarta">Contact
                                Us</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-white font-oswald uppercase tracking-wider mb-4 md:mb-6 text-sm">Enterprise
                        Services</h5>
                    <ul class="space-y-2 md:space-y-3">
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Enterprise
                                Innovation</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Digital
                                Assets</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Learning
                                Solutions</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Marketing
                                As A Service</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Capacity
                                Assessment</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Merger
                                & Acquisition</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-white font-oswald uppercase tracking-wider mb-4 md:mb-6 text-sm">IT Services</h5>
                    <ul class="space-y-2 md:space-y-3">
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Infrastructure
                                Services</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Data
                                Center Setup</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Managed
                                Services</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">Cyber
                                Security</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-accent transition-colors text-xs md:text-sm font-jakarta">System
                                Integration</a></li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col-reverse md:flex-row justify-between items-center pt-6 md:pt-8 border-t border-gray-800 gap-4">
                <p class="text-gray-500 text-xs md:text-sm font-jakarta text-center md:text-left">
                    &copy; 2026 SZORZO Technologies. All Rights Reserved.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-500 hover:text-accent transition-colors"><i
                            class="fa-solid fa-location-dot"></i></a>
                    <a href="#" class="text-gray-500 hover:text-accent transition-colors"><i
                            class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="text-gray-500 hover:text-accent transition-colors"><i
                            class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-500 hover:text-accent transition-colors"><i
                            class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/1234567890" target="_blank" rel="noopener noreferrer"
        class="fixed bottom-4 md:bottom-6 right-4 md:right-6 bg-[#25D366] text-white w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center text-2xl md:text-3xl hover:scale-110 transition-transform z-50 wa-btn"
        aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('nav-scrolled');
                navbar.classList.remove('py-4', 'border-b', 'border-white/10', 'bg-primary/80', 'md:bg-transparent',
                    'backdrop-blur-sm', 'md:backdrop-blur-none');
                navbar.classList.add('py-2');
            } else {
                navbar.classList.remove('nav-scrolled');
                navbar.classList.add('py-4', 'border-b', 'border-white/10', 'bg-primary/80', 'md:bg-transparent',
                    'backdrop-blur-sm', 'md:backdrop-blur-none');
                navbar.classList.remove('py-2');
            }
        });

        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileBtn.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            }
        }

        mobileBtn.addEventListener('click', toggleMobileMenu);

        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (!mobileMenu.classList.contains('hidden')) {
                    toggleMobileMenu();
                }
            });
        });

        function toggleFaq(button) {
            const item = button.parentElement;
            document.querySelectorAll('.faq-item').forEach(faq => {
                if (faq !== item) {
                    faq.classList.remove('active');
                }
            });
            item.classList.toggle('active');
        }
    </script>
</body>

</html>
