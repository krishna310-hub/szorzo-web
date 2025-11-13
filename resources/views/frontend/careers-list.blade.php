@extends('frontend.includes.master')

@section('content')
<!-- Page Header Start -->
  <div class="page-header-careers-bg">
    <div class="container">
      <div class="col-lg-12" style="width: 10%; margin-top: 300px;">
        <div class="page-header-box">
          <h1 class="wow fadeInUp" data-cursor="-opaque"><span>CAREERS</span></h1>
        </div>
      </div>
    </div>
  </div>
  <!-- Page Header End -->
<div class="container py-5">
    {{-- Zoho Recruit Embedded Careers Widget --}}
    <link rel="stylesheet" href="https://static.zohocdn.com/recruit/embed_careers_site/css/v1.1/embed_jobs.css" type="text/css">

    <div class="embed_jobs_head embed_jobs_with_style_3">
        <div class="embed_jobs_head2">
            <div class="embed_jobs_head3">
                <div id="rec_job_listing_div"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://static.zohocdn.com/recruit/embed_careers_site/javascript/v1.1/embed_jobs.js"></script>
    <script type="text/javascript">
        rec_embed_js.load({
            widget_id: "rec_job_listing_div",
            page_name: "Careers",
            source: "CareerSite",
            site: "https://szorzo.zohorecruit.in",
            brand_color: "#6875E2",
            empty_job_msg: "No current Openings"
        });

        // Wait a bit and replace the text with <h2>
        setTimeout(() => {
            const emptyMsg = document.querySelector("#rec_job_listing_div .no_jobs_div");
            if (emptyMsg) {
                emptyMsg.innerHTML = "<h2 class='text-center text-muted'>No current Openings</h2>";
            }
        }, 1000);
    </script>
</div>

@endsection
