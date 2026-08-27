@extends('frontend.includes.master')

@section('content')
@php
    $phases = [
        ['title' => 'Discovery & Research', 'stages' => [
            ['Intro & Business Objectives', 'Align business goals with software solution capabilities & establish ROI targets.', ['Define core problem statement', 'Identify key business KPIs', 'Determine budget & feasibility'], 'Executive & Product Lead', 'Week 1', 'ri-focus-2-line'],
            ['Requirements Gathering', 'Elicit detailed stakeholder feedback & turn business needs into user stories.', ['Stakeholder interview sessions', 'User persona mapping', 'Functional requirements document'], 'Business Analyst & Client', 'Weeks 1–2', 'ri-discuss-line'],
            ['Market Research', 'Analyze competitor landscapes, technology trends, and industry benchmarks.', ['Competitor feature matrix', 'SWOT & tech stack gap analysis', 'Target audience behavior evaluation'], 'Strategy & Product Ops', 'Week 2', 'ri-line-chart-line'],
        ]],
        ['title' => 'Scope & Architecture', 'stages' => [
            ['Scope, Features & Deliverables', 'Prevent scope creep by setting strict boundaries, MVP definitions, and acceptance criteria.', ['Drafting Software Requirement Specification (SRS)', 'Prioritizing backlog features (MoSCoW framework)', 'Sign-off on project milestone deliverables'], 'Product Manager, Tech Lead & Client', 'Week 3', 'ri-file-list-3-line'],
            ['Tech Selection & Architecture', 'Choose scalable languages, frameworks, cloud infrastructures, and system topologies.', ['Selecting Microservices vs. Monolith framework', 'Frontend (React/Vue/Flutter) & Backend (Node/Python) stack selection', 'High-Level Design (HLD) diagram generation'], 'Solution Architect & Principal Engineer', 'Weeks 3–4', 'ri-node-tree'],
        ]],
        ['title' => 'Project Planning & Roadmap', 'stages' => [
            ['Project Planning & Roadmap', 'Establish structured Agile sprint cycles, resource allocations, risk mitigation protocols, and milestone delivery dates across the complete delivery lifecycle.', ['Project charter and Agile backlog', 'Resource allocation & risk planning', 'Milestone-based delivery roadmap'], 'Project Manager, Scrum Master & Leads', '', 'ri-road-map-line'],
        ]],
        ['title' => 'Design & System Schematics', 'stages' => [
            ['UI/UX Design & Wireframing', 'Create intuitive visual experiences and seamless user flows.', ['Low-fidelity wireframes & user journey maps', 'High-fidelity Figma prototypes & Design System', 'Usability testing & interactive client walkthrough'], 'Lead UI/UX Designer', 'Weeks 4–6', 'ri-tools-line'],
            ['Database & Low-Level Design', 'Architect optimized, normalized database schemas and relationships.', ['Entity-Relationship Diagrams (ERD) & indexing strategy', 'SQL / NoSQL schema definition & migration plans', 'Data access layer & caching mechanisms (Redis)'], 'Database Admin & Backend Architect', 'Weeks 5–6', 'ri-database-2-line'],
        ]],
        ['title' => 'Core Application Engineering', 'stages' => [
            ['Frontend Dev', 'Build responsive web interfaces matching Figma specs.', ['Component Library', 'State Management', 'Responsive Layouts'], 'Frontend Team', 'Weeks 6–10', 'ri-code-s-slash-line'],
            ['Backend & APIs', 'Create robust core logic, REST/GraphQL APIs, & services.', ['Business Logic Layer', 'RESTful API Endpoints', 'Authentication Engine'], 'Backend Team', 'Weeks 6–11', 'ri-server-line'],
            ['Admin Panel', 'Develop management dashboards for content & analytics.', ['Role-Based Access', 'Data Analytics View', 'User & Order Mgmt'], 'Fullstack Devs', 'Weeks 8–11', 'ri-admin-line'],
            ['Mobile Apps', 'Cross-platform iOS/Android app builds if applicable.', ['React Native/Flutter', 'Native Push Hooks', 'Offline Caching'], 'Mobile Devs', 'Weeks 8–12', 'ri-smartphone-line'],
        ]],
        ['title' => 'Integrations & Security', 'stages' => [
            ['Third-Party Integrations', 'Connect essential external platforms for payments, communications, and business services securely.', ['Payment Gateways (Stripe, PayPal, Razorpay API setup)', 'SMS & Email services (Twilio, SendGrid API)', 'Push notification gateways (Firebase Cloud Messaging)'], 'Backend & Integration Engineers', 'Weeks 11–12', 'ri-plug-line'],
            ['Security & Data Protection', 'Harden infrastructure against vulnerabilities and enforce strict compliance standards.', ['OAuth 2.0 / JWT implementation & SSL TLS 1.3 encryption', 'OWASP Top 10 vulnerability remediation & SQLi protection', 'GDPR / HIPAA compliance audit & data masking policies'], 'Cyber Security Lead & DevOps', 'Weeks 12–13', 'ri-shield-check-line'],
        ]],
        ['title' => 'Validation & Client Sign-Off', 'stages' => [
            ['Testing, QA & Bug Fixing', 'Ensure software stability, performance, and bug-free user experiences across environments.', ['Unit, Integration, and Automated End-to-End testing', 'Performance, load, & stress testing (JMeter)', 'Bug triage, tracking (Jira), and regression validation'], 'QA Lead & Test Engineers', 'Weeks 12–14', 'ri-bug-line'],
            ['Client Review & UAT', 'Conduct User Acceptance Testing (UAT) with key business stakeholders for formal approval.', ['Staging environment client demonstration sessions', 'Collecting structured UAT feedback & backlog refinement', 'Final feature validation & launch authorization sign-off'], 'Product Manager, Client & Business Lead', 'Weeks 14–15', 'ri-user-follow-line'],
        ]],
        ['title' => 'Production Deployment & Launch', 'stages' => [
            ['Server Setup & CI/CD Pipelines', 'Provision cloud infrastructure and automated release pipelines.', ['AWS/GCP infrastructure provisioning with Terraform', 'CI/CD automation pipelines (GitHub Actions/Docker)', 'Domain DNS, SSL certificates & CDN setup'], 'DevOps & Cloud Engineer', 'Week 15', 'ri-cloud-line'],
            ['Production Launch & Verification', 'Execute seamless zero-downtime production release.', ['Blue-Green or Canary deployment rollout strategy', 'Post-launch sanity testing & telemetry monitoring', 'System backup and rollback plan verification'], 'DevOps, Tech Lead & Ops', 'Week 15', 'ri-rocket-2-line'],
        ]],
        ['title' => 'Growth & Continuous Support', 'stages' => [
            ['SEO & Marketing Setup', 'Optimize discoverability and set up conversion tracking.', ['Technical SEO & Schema markup', 'Google Analytics & Pixel hooks', 'Page speed performance audit'], 'SEO & Marketing Team', 'Week 16', 'ri-bar-chart-box-line'],
            ['Training & Documentation', 'Empower end-users & maintain internal technical guides.', ['API docs (Swagger/Postman)', 'Admin panel user video guides', 'System operations runbooks'], 'Tech Writer & PM', 'Week 16', 'ri-book-open-line'],
            ['Post-Launch Maintenance', 'Provide ongoing support, patch security, & plan v2 features.', ['SLA-based bug fixes & monitoring', 'OS & library security updates', 'Future roadmap iterations'], 'Support & Maintenance Team', 'Continuous', 'ri-customer-service-2-line'],
        ]],
    ];
    $stageNumber = 0;
    $raci = [
        ['Discovery & Research', 'Accountable', 'Consulted', 'Consulted', 'Informed', 'Informed'],
        ['Scope & Architecture', 'Responsible', 'Consulted', 'Accountable', 'Consulted', 'Consulted'],
        ['Design & Schematics', 'Consulted', 'Accountable', 'Responsible', 'Informed', 'Informed'],
        ['Engineering & Security', 'Consulted', 'Informed', 'Accountable', 'Responsible', 'Responsible'],
        ['Validation & Deployment', 'Accountable', 'Informed', 'Responsible', 'Accountable', 'Accountable'],
        ['Maintenance & Support', 'Responsible', 'Informed', 'Responsible', 'Responsible', 'Accountable'],
    ];
@endphp

<div class="tech-page">
    <section class="tech-hero">
        <div class="tech-orb tech-orb-one"></div><div class="tech-orb tech-orb-two"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-10">
                    <span class="tech-eyebrow wow fadeInUp"><i class="ri-code-box-line"></i> Software Engineering Lifecycle</span>
                    <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">End-to-End Software<br><span>Development Workflow</span></h1>
                    <p class="wow fadeInUp" data-wow-delay="0.3s">A complete 21-stage framework covering initial business discovery, technical architecture, agile engineering, QA, deployment, and post-launch maintenance.</p>
                    <div class="tech-actions wow fadeInUp" data-wow-delay="0.4s">
                        <a href="#workflow" class="tech-btn tech-btn-primary">Explore our workflow <i class="ri-arrow-down-line"></i></a>
                        {{-- <a href="{{ route('contact.it.services.form') }}" class="tech-btn tech-btn-ghost">Start a project <i class="ri-arrow-right-up-line"></i></a> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <nav class="tech-phase-nav" aria-label="Workflow phases">
        <div class="container"><div class="tech-phase-scroll">
            @foreach($phases as $index => $phase)<a href="#phase-{{ $index + 1 }}"><span>0{{ $index + 1 }}</span>{{ $phase['title'] }}</a>@endforeach
        </div></div>
    </nav>

    <section class="tech-intro" id="workflow">
        <div class="container">
            <div class="tech-section-head wow fadeInUp"><div><span class="tech-kicker">How we deliver</span><h2 data-cursor="-opaque">One disciplined path from <span>idea to impact.</span></h2></div><p>Every stage has a clear purpose, accountable experts, concrete outputs, and a defined place on the delivery timeline.</p></div>
        </div>
    </section>

    @foreach($phases as $phaseIndex => $phase)
    <section class="tech-phase {{ $phaseIndex % 2 ? 'tech-phase-alt' : '' }}" id="phase-{{ $phaseIndex + 1 }}">
        <div class="container">
            <div class="tech-phase-heading wow fadeInUp"><div class="tech-phase-number">0{{ $phaseIndex + 1 }}</div><div><span>Phase {{ $phaseIndex + 1 }}</span><h2 data-cursor="-opaque">{{ $phase['title'] }}</h2></div><div class="tech-stage-range">{{ count($phase['stages']) }} {{ Str::plural('stage', count($phase['stages'])) }}</div></div>
            @if($phaseIndex === 2)
            <div class="tech-roadmap">
                @foreach([
                    ['Sprint 0: Setup','Foundation',['Discovery & Architecture','Jira / Backlog setup','Environment preparation']],
                    ['Core Engineering','Build',['UX/UI Finalization','Frontend & API dev','Database integration']],
                    ['Integration Sprints','Connect',['3rd-party integrations','Admin & Mobile build','Security hardening']],
                    ['Release & Launch','Deliver',['UAT & QA regression','Production deployment','Training & SEO setup']]
                ] as $roadIndex => $road)
                <article class="wow fadeInUp" data-wow-delay="{{ number_format(($roadIndex + 1) * 0.1, 1) }}s"><div class="tech-road-num">{{ $roadIndex + 1 }}</div><h3>{{ $road[0] }}</h3><span>{{ $road[1] }}</span><ul>@foreach($road[2] as $item)<li>{{ $item }}</li>@endforeach</ul></article>
                @endforeach
            </div>
            @endif
            <div class="tech-stage-grid {{ count($phase['stages']) === 1 ? 'tech-single-stage' : '' }}">
                @foreach($phase['stages'] as $stage)
                @php $stageNumber++; @endphp
                <article class="tech-stage-card wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) * 0.1, 1) }}s">
                    <div class="tech-card-top"><div class="tech-icon"><img src="{{ asset('frontend/images/rhino-logo.webp') }}" alt="" width="38" height="38" loading="lazy" decoding="async"></div><span>Stage {{ str_pad($stageNumber, 2, '0', STR_PAD_LEFT) }}</span></div>
                    <h3>{{ $stage[0] }}</h3><p class="tech-purpose"><strong>Purpose:</strong> {{ $stage[1] }}</p>
                    <ul>@foreach($stage[2] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    <div class="tech-card-meta"><span><i class="ri-team-line"></i>{{ $stage[3] }}</span></div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endforeach

    <section class="tech-raci">
        <div class="container">
            <div class="tech-section-head tech-light wow fadeInUp"><div><span class="tech-kicker">Built-in accountability</span><h2 data-cursor="-opaque">Team Responsibilities <span>Matrix (RACI)</span></h2></div><p>Cross-functional accountability mapping across core SDLC phases.</p></div>
            <div class="tech-table-wrap wow fadeInUp" data-wow-delay="0.2s"><table><thead><tr><th>SDLC Phase</th><th>Product / PM</th><th>UI/UX Design</th><th>Engineering</th><th>QA Team</th><th>DevOps/Infra</th></tr></thead><tbody>
                @foreach($raci as $row)
                <tr>
                    <th>{{ $row[0] }}</th>
                    @foreach(array_slice($row, 1) as $cell)<td><span class="raci-{{ strtolower($cell) }}">{{ $cell }}</span></td>@endforeach
                </tr>
                @endforeach
            </tbody></table></div>
        </div>
    </section>

    <section class="tech-success"><div class="container"><div class="tech-success-card wow fadeInUp"><div><span class="tech-kicker">Summary & project success</span><h2 data-cursor="-opaque">Engineered for confident, <span>predictable delivery.</span></h2><p>By following a disciplined, 21-stage software development lifecycle, engineering teams deliver high-quality, scalable, and secure applications predictably on schedule.</p>
        {{-- <a href="{{ route('contact.it.services.form') }}" class="tech-btn tech-btn-primary">Discuss your project <i class="ri-arrow-right-line"></i></a> --}}
    </div><div class="tech-success-stats"><div><strong>21</strong><span>Structured Stages</span></div><div><strong>9</strong><span>Core Phases</span></div><div><strong>100%</strong><span>Traceability & Quality</span></div><div><strong>∞</strong><span>Continuous Improvement</span></div></div></div></div></section>
</div>

<style>
/* The shared stylesheet gives every <section> a 100vh minimum height. Reset it
   for this page so each workflow section follows its real content height. */
.tech-page section{
    margin-top:0 !important;
    min-height:0 !important;
    height:auto;
}

/* Continue the Tech Services hero treatment through the site header. */
body:has(.tech-page) .header{
    background:
        linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px),
        radial-gradient(circle at 80% 20%,#222660 0,transparent 35%),
        linear-gradient(135deg,#090f24,#171540) !important;
    background-size:46px 46px,46px 46px,auto,auto !important;
    border-bottom:1px solid rgba(255,255,255,.08);
}
body:has(.tech-page) .header .menu > ul > li > a{
    color:#fff !important;
    background:transparent !important;
}
body:has(.tech-page) .header .mobile-menu-trigger span,
body:has(.tech-page) .header .mobile-menu-trigger span::before,
body:has(.tech-page) .header .mobile-menu-trigger span::after{
    background:#fff !important;
}
.tech-page{--ink:#0b1225;--navy:#0d1330;--purple:#6657f6;--cyan:#38c9f2;--mint:#26c997;--line:#e4e7f0;background:#fff;color:var(--ink);font-family:Manrope,sans-serif;overflow:hidden}.tech-page *{box-sizing:border-box}.tech-hero{position:relative;padding:190px 0 110px;background:radial-gradient(circle at 80% 20%,#222660 0,transparent 35%),linear-gradient(135deg,#090f24,#171540);color:#fff}.tech-hero:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:46px 46px;mask-image:linear-gradient(to bottom,black,transparent)}.tech-orb{position:absolute;border-radius:50%;filter:blur(2px);opacity:.45}.tech-orb-one{width:280px;height:280px;background:#5949ff;right:-80px;top:100px}.tech-orb-two{width:160px;height:160px;background:#1dc8ef;left:-80px;bottom:-40px}.tech-eyebrow,.tech-kicker{display:inline-flex;align-items:center;gap:8px;text-transform:uppercase;letter-spacing:.16em;font-size:12px;font-weight:800}.tech-eyebrow{padding:9px 14px;border:1px solid rgba(255,255,255,.16);border-radius:999px;color:#71dcff;background:rgba(255,255,255,.05)}.tech-hero h1{font-size:clamp(48px,6.4vw,88px);letter-spacing:-.055em;line-height:1.02;margin:26px 0 24px;color:#fff}.tech-hero h1 span,.tech-section-head h2 span,.tech-success h2 span{background:linear-gradient(90deg,#8b7cff,#35c8ee);-webkit-background-clip:text;color:transparent}.tech-hero p{max-width:800px;font-size:18px;line-height:1.8;color:#b8bed4;margin:0}.tech-actions{display:flex;gap:14px;flex-wrap:wrap;margin-top:38px}.tech-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border-radius:12px;padding:14px 20px;font-weight:800;transition:.25s}.tech-btn-primary{color:#fff;background:linear-gradient(135deg,#6756f7,#4b3bd1);box-shadow:0 12px 30px rgba(89,73,255,.28)}.tech-btn:hover{transform:translateY(-2px);color:#fff}.tech-btn-ghost{color:#fff;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.05)}.tech-hero-panel{position:relative;padding:24px;border:1px solid rgba(255,255,255,.12);border-radius:24px;background:rgba(255,255,255,.07);box-shadow:0 25px 80px rgba(0,0,0,.25);backdrop-filter:blur(14px)}.tech-panel-top{display:flex;justify-content:space-between;color:#aeb5cc;font-size:12px;text-transform:uppercase;letter-spacing:.08em}.tech-live{color:#74e4c2}.tech-live i{display:inline-block;width:7px;height:7px;border-radius:50%;background:#2be0a7;margin-right:6px;box-shadow:0 0 0 5px rgba(43,224,167,.12)}.tech-ring{width:176px;height:176px;margin:34px auto;display:grid;place-content:center;text-align:center;border-radius:50%;background:radial-gradient(circle at center,#15183c 56%,transparent 57%),conic-gradient(#5c4cf2 0 76%,#37caee 76% 92%,rgba(255,255,255,.1) 92%)}.tech-ring strong{font-size:56px;line-height:1}.tech-ring span{text-transform:uppercase;letter-spacing:.15em;font-size:10px;color:#9fa7c2}.tech-mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.tech-mini-stats span{padding:12px 5px;text-align:center;background:rgba(255,255,255,.05);border-radius:10px;font-size:10px;text-transform:uppercase;color:#9ea6c0}.tech-mini-stats strong{display:block;font-size:18px;color:#fff}.tech-phase-nav{position:relative;z-index:4;background:#fff;border-bottom:1px solid var(--line);box-shadow:0 10px 35px rgba(15,22,48,.05)}.tech-phase-scroll{display:flex;overflow:auto;scrollbar-width:none}.tech-phase-scroll a{min-width:max-content;color:#555f73;padding:19px 18px;border-bottom:2px solid transparent;font-size:12px;font-weight:700}.tech-phase-scroll a:hover{color:var(--purple);border-color:var(--purple)}.tech-phase-scroll span{color:var(--purple);margin-right:8px}.tech-intro{padding:100px 0 60px}.tech-section-head{display:flex;align-items:end;justify-content:space-between;gap:60px}.tech-section-head>div{max-width:760px}.tech-kicker{color:var(--purple);margin-bottom:14px}.tech-section-head h2,.tech-success h2{font-size:clamp(34px,4vw,58px);letter-spacing:-.045em;line-height:1.08;margin:0}.tech-section-head>p{max-width:420px;color:#687186;line-height:1.8}.tech-phase{padding:65px 0 90px;scroll-margin-top:30px}.tech-phase-alt{background:#f7f8fc}.tech-phase-heading{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:22px;margin-bottom:34px}.tech-phase-number{font-size:13px;font-weight:800;color:var(--purple);width:52px;height:52px;border-radius:16px;background:#eae8ff;display:grid;place-content:center}.tech-phase-heading span{font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:#848ca0;font-weight:800}.tech-phase-heading h2{font-size:clamp(28px,3vw,42px);margin:3px 0 0;letter-spacing:-.035em}.tech-stage-range{font-size:11px;text-transform:uppercase;letter-spacing:.12em;padding:8px 12px;border:1px solid var(--line);border-radius:999px;color:#687186}.tech-stage-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}.tech-stage-grid:has(.tech-stage-card:nth-child(3)){grid-template-columns:repeat(3,minmax(0,1fr))}.tech-stage-grid:has(.tech-stage-card:nth-child(4)){grid-template-columns:repeat(2,minmax(0,1fr))}.tech-single-stage{grid-template-columns:1fr!important}.tech-stage-card{display:flex;flex-direction:column;min-height:100%;padding:28px;background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 12px 38px rgba(17,24,52,.055);transition:.25s}.tech-stage-card:hover{transform:translateY(-5px);border-color:#c9c3ff;box-shadow:0 20px 50px rgba(43,36,105,.11)}.tech-card-top{display:flex;align-items:center;justify-content:space-between}.tech-icon{width:48px;height:48px;border-radius:14px;display:grid;place-content:center;color:var(--purple);font-size:23px;background:linear-gradient(135deg,#eceaff,#e5f9ff)}.tech-card-top>span{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:#858da0;font-weight:800}.tech-stage-card h3{font-size:22px;line-height:1.3;margin:20px 0 12px}.tech-purpose{color:#657085;line-height:1.7;margin-bottom:17px}.tech-purpose strong{color:#20283c}.tech-stage-card ul,.tech-roadmap ul{list-style:none;padding:0;margin:0 0 22px}.tech-stage-card li,.tech-roadmap li{position:relative;padding:5px 0 5px 23px;color:#4e586c;line-height:1.55}.tech-stage-card li:before,.tech-roadmap li:before{content:'✓';position:absolute;left:0;color:var(--mint);font-weight:900}.tech-card-meta{display:flex;flex-wrap:wrap;gap:10px 16px;padding-top:17px;margin-top:auto;border-top:1px dashed #d9dce5}.tech-card-meta span{display:flex;gap:7px;align-items:center;font-size:11px;color:#737d91}.tech-card-meta i{color:var(--purple);font-size:16px}.tech-roadmap{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}.tech-roadmap article{position:relative;padding:25px 20px;border:1px solid var(--line);border-radius:18px;background:#fff}.tech-roadmap article:not(:last-child):after{content:'→';position:absolute;right:-14px;top:31px;z-index:2;color:#8f86ef;font-size:20px}.tech-road-num{width:33px;height:33px;display:grid;place-content:center;background:var(--purple);color:#fff;border-radius:50%;font-size:12px;font-weight:800}.tech-roadmap h3{font-size:17px;margin:17px 0 3px}.tech-roadmap article>span{color:#7c8598;font-size:12px}.tech-roadmap ul{margin-top:15px;font-size:12px}.tech-raci{padding:100px 0;background:linear-gradient(145deg,#0a1024,#16143b);color:#fff}.tech-light h2{color:#fff}.tech-light>p{color:#aeb5ca}.tech-table-wrap{overflow:auto;margin-top:42px;border:1px solid rgba(255,255,255,.1);border-radius:18px;background:rgba(255,255,255,.045)}.tech-table-wrap table{width:100%;min-width:960px;border-collapse:collapse}.tech-table-wrap th,.tech-table-wrap td{padding:20px;text-align:left;border-bottom:1px solid rgba(255,255,255,.08);font-size:13px}.tech-table-wrap thead th{color:#a9b0c5;text-transform:uppercase;letter-spacing:.08em;font-size:10px}.tech-table-wrap tbody th{color:#fff}.tech-table-wrap td span{display:inline-block;padding:7px 10px;border-radius:7px;font-size:10px;font-weight:800}.raci-accountable{background:#fff0b8;color:#875c00}.raci-responsible{background:#ffd7dc;color:#a62735}.raci-consulted{background:#d8e8ff;color:#24599c}.raci-informed{background:#c9f5e5;color:#147259}.tech-success{padding:100px 0;background:#f7f8fc}.tech-success-card{display:grid;grid-template-columns:1.2fr 1fr;gap:60px;padding:64px;border-radius:28px;background:#fff;border:1px solid var(--line);box-shadow:0 25px 75px rgba(13,19,48,.08)}.tech-success p{color:#687186;line-height:1.8;max-width:700px;margin:20px 0 28px}.tech-success-stats{display:grid;grid-template-columns:1fr 1fr;gap:14px}.tech-success-stats div{display:flex;flex-direction:column;justify-content:center;padding:24px;border-radius:18px;background:#f5f4ff;border:1px solid #e9e6ff}.tech-success-stats strong{font-size:36px;color:var(--purple)}.tech-success-stats span{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#626c80;font-weight:800}
.tech-page .tech-hero{padding-top:120px;padding-bottom:95px}
.tech-page .tech-intro{padding-top:65px;padding-bottom:35px}

/* Szorzo brand theme: existing site accent #dc2626 with black, white and gray. */
.tech-page{
    --purple:#dc2626;
    --cyan:#ef4444;
    --mint:#dc2626;
    --navy:#111111;
    font-family:var(--default-font,"Oswald",sans-serif);
}
body:has(.tech-page) .header{
    background:
        linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px),
        radial-gradient(circle at 82% 0,rgba(220,38,38,.36),transparent 38%),
        linear-gradient(135deg,#090909,#181010) !important;
}
.tech-page .tech-hero{
    background:
        radial-gradient(circle at 80% 18%,rgba(220,38,38,.34),transparent 36%),
        linear-gradient(135deg,#090909,#1c0d0d);
}
.tech-page .tech-orb-one{background:#dc2626}
.tech-page .tech-orb-two{background:#991b1b}
.tech-page .tech-eyebrow{color:#fca5a5;border-color:rgba(239,68,68,.28);background:rgba(220,38,38,.09)}
.tech-page .tech-hero h1 span,
.tech-page .tech-section-head h2 span,
.tech-page .tech-success h2 span{
    background:linear-gradient(90deg,#dc2626,#f87171);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
}
.tech-page .tech-btn-primary{
    background:linear-gradient(135deg,#dc2626,#991b1b);
    box-shadow:0 12px 30px rgba(220,38,38,.28);
}
.tech-page .tech-hero-panel{border-color:rgba(239,68,68,.2);background:rgba(54,18,18,.62)}
.tech-page .tech-live{color:#fca5a5}
.tech-page .tech-live i{background:#ef4444;box-shadow:0 0 0 5px rgba(239,68,68,.14)}
.tech-page .tech-ring{
    background:radial-gradient(circle at center,#160d0d 56%,transparent 57%),conic-gradient(#dc2626 0 76%,#f87171 76% 92%,rgba(255,255,255,.1) 92%);
}
.tech-page .tech-phase-number{background:#fee2e2;color:#b91c1c}
.tech-page .tech-stage-card:hover{border-color:#f0a3a3;box-shadow:0 20px 50px rgba(127,29,29,.12)}
.tech-page .tech-icon{color:#dc2626;background:linear-gradient(135deg,#fee2e2,#fff1f2)}
.tech-page .tech-icon img{display:block;width:38px;height:38px;object-fit:contain}
.tech-page .tech-roadmap article:not(:last-child)::after{color:#dc2626}
.tech-page .tech-road-num{background:linear-gradient(135deg,#dc2626,#991b1b)}
.tech-page .tech-raci{background:radial-gradient(circle at 85% 10%,rgba(220,38,38,.23),transparent 32%),linear-gradient(145deg,#090909,#1b0c0c)}
.tech-page .tech-success-stats div{background:#fff5f5;border-color:#fecaca}
.tech-page .tech-success-stats strong{color:#dc2626}

/* Match the Szorzo AI page's typography scale and interaction language. */
.tech-page{font-size:17px;line-height:1.75;font-weight:400;-webkit-font-smoothing:antialiased}
.tech-page .tech-hero h1{font-size:clamp(46px,5.5vw,70px);font-weight:600;line-height:1.1;letter-spacing:-.03em}
.tech-page .tech-section-head h2,
.tech-page .tech-success h2{font-size:clamp(38px,4.7vw,60px);font-weight:600;line-height:1.15;letter-spacing:-.03em}
.tech-page .tech-phase-heading h2{font-size:clamp(32px,3.5vw,48px);font-weight:600;line-height:1.2;letter-spacing:-.03em}
.tech-page .tech-stage-card h3{font-size:22px;font-weight:600;line-height:1.3}
.tech-page .tech-purpose,
.tech-page .tech-stage-card li{font-size:17px;line-height:1.65}
.tech-page .tech-card-meta span{font-size:14px;line-height:1.4}
.tech-page .tech-hero h1 span,
.tech-page .tech-section-head h2 span,
.tech-page .tech-success h2 span{background-size:200% auto;transition:background-position .4s ease-in-out}
.tech-page .tech-hero h1:hover span,
.tech-page .tech-section-head h2:hover span,
.tech-page .tech-success h2:hover span{background-position:right center}
.tech-page .tech-stage-card,
.tech-page .tech-roadmap article,
.tech-page .tech-success-stats div{transition:transform .35s ease,box-shadow .35s ease,border-color .35s ease,background-color .35s ease}
.tech-page .tech-icon img{transition:transform .35s ease}
.tech-page .tech-stage-card:hover .tech-icon img{transform:scale(1.12)}
.tech-page .tech-roadmap article:hover,
.tech-page .tech-success-stats div:hover{transform:translateY(-6px);border-color:#f0a3a3;box-shadow:0 18px 40px rgba(127,29,29,.12)}
@media(prefers-reduced-motion:reduce){.tech-page *{animation:none!important;transition:none!important}}
@media(max-width:1199px){.tech-stage-grid:has(.tech-stage-card:nth-child(3)){grid-template-columns:repeat(2,1fr)}.tech-roadmap{grid-template-columns:repeat(2,1fr)}.tech-roadmap article:after{display:none}}
@media(max-width:991px){.tech-hero{padding:150px 0 80px}.tech-section-head{align-items:start;flex-direction:column;gap:20px}.tech-success-card{grid-template-columns:1fr;padding:38px}.tech-stage-grid,.tech-stage-grid:has(.tech-stage-card:nth-child(3)),.tech-stage-grid:has(.tech-stage-card:nth-child(4)){grid-template-columns:1fr 1fr}}
@media(max-width:767px){.tech-hero h1{font-size:44px}.tech-hero p{font-size:15px}.tech-intro{padding:70px 0 35px}.tech-phase{padding:45px 0 65px}.tech-phase-heading{grid-template-columns:auto 1fr}.tech-stage-range{display:none}.tech-stage-grid,.tech-stage-grid:has(.tech-stage-card:nth-child(3)),.tech-stage-grid:has(.tech-stage-card:nth-child(4)),.tech-roadmap{grid-template-columns:1fr}.tech-success{padding:60px 0}.tech-success-card{padding:25px}.tech-success-stats{grid-template-columns:1fr 1fr}.tech-success-stats strong{font-size:28px}}
@media(max-width:420px){.tech-hero h1{font-size:38px}.tech-actions .tech-btn{width:100%}.tech-success-stats{grid-template-columns:1fr}.tech-stage-card{padding:22px}}
@media(max-width:767px){.tech-page .tech-hero h1{font-size:42px}.tech-page .tech-section-head h2,.tech-page .tech-success h2{font-size:38px}.tech-page .tech-phase-heading h2{font-size:32px}.tech-page .tech-purpose,.tech-page .tech-stage-card li{font-size:16px}}
@media(max-width:420px){.tech-page .tech-hero h1{font-size:36px}.tech-page .tech-section-head h2,.tech-page .tech-success h2{font-size:33px}}

/* Final readable type scale — intentionally last so compact legacy rules do not override it. */
.tech-page .tech-hero p{font-size:20px !important;line-height:1.65 !important;max-width:900px}
.tech-page .tech-phase-scroll a{font-size:14px !important;line-height:1.3;padding:20px 16px}
.tech-page .tech-eyebrow,
.tech-page .tech-kicker{font-size:14px !important;line-height:1.3}
.tech-page .tech-section-head>p{font-size:19px !important;line-height:1.65;max-width:460px}
.tech-page .tech-phase-heading>div>span{font-size:13px !important}
.tech-page .tech-stage-range{font-size:13px !important}
.tech-page .tech-stage-card h3{font-size:20px !important;line-height:1.35;font-weight:600}
.tech-page .tech-purpose{font-size:18px !important;line-height:1.7 !important}
.tech-page .tech-stage-card li{font-size:18px !important;line-height:1.65 !important}
.tech-page .tech-card-meta span{font-size:15px !important;line-height:1.45}
.tech-page .tech-card-top>span{font-size:12px !important}
.tech-page .tech-roadmap h3{font-size:21px !important;line-height:1.35}
.tech-page .tech-roadmap article>span{font-size:15px !important}
.tech-page .tech-roadmap li{font-size:16px !important;line-height:1.6}
.tech-page .tech-table-wrap th,
.tech-page .tech-table-wrap td{font-size:16px !important;line-height:1.4}
.tech-page .tech-table-wrap thead th{font-size:13px !important}
.tech-page .tech-table-wrap td span{font-size:13px !important}
.tech-page .tech-success p{font-size:19px !important;line-height:1.7}
.tech-page .tech-success-stats span{font-size:14px !important;line-height:1.4}
.tech-page .wow{animation-duration:.8s;animation-timing-function:ease-out}
.tech-page .tech-stage-card:hover{transform:translateY(-8px);box-shadow:0 22px 48px rgba(127,29,29,.14)}

@media(max-width:767px){
    .tech-page .tech-hero p{font-size:17px !important}
    .tech-page .tech-section-head>p{font-size:17px !important}
    .tech-page .tech-stage-card h3{font-size:19px !important}
    .tech-page .tech-purpose,
    .tech-page .tech-stage-card li{font-size:16px !important}
    .tech-page .tech-card-meta span{font-size:14px !important}
}
</style>
@endsection
