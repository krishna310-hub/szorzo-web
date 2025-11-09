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

  <!-- Page Header Start -->
  <div class="mt-5">
    <div class="container">
      <div class="col-lg-12">
        <div class="marketing-service-card">
          <div class="section-title text-center">
            <h1 class="wow fadeInUp" data-wow-delay="0.2s" data-cursor="-opaque">
              <span>Be Part of our Mission</span>
            </h1>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Page Header End -->

  <div class="col-lg-8" style="margin-left: 250px;">
    <div class="team-single-content">
        <div class="team-member-info">
            <div class="section-title" style="text-align: center;">
                <p class="wow fadeInUp" data-wow-delay="0.4s" style="font-size: large;">What's destination next? Is it a better AI technology, a better you, or a better community of pioneers? At SZORZO, we’re not just offering a destination, but a journey powered by innovation and transformation. As the next-generation AI-first, digital-first, cloud-first partner, we stand at the forefront of business evolution.
Come join our community of AI-aware employees and thrive at SZORZO. Together, let's build what's next, ensuring your career never stands still as we move forward, taking the world with us.</p>
            </div>
        </div>
    </div>
  </div>

  <section class="career-list-section pb-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="filter-box p-4 shadow-sm rounded">
            <h5 class="fw-bold mb-4">Filter Jobs</h5>

            <!-- Location Filter -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Location</label>
              <select id="filter-location" class="form-select">
                <option value="">All</option>
                <option value="Coimbatore">Coimbatore</option>
                <option value="Chennai">Chennai</option>
                <option value="Bangalore">Bangalore</option>
              </select>
            </div>

            <!-- Work Mode Filter -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Work Mode</label>
              <div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="workMode" id="hybrid" value="Hybrid">
                  <label class="form-check-label" for="hybrid">Hybrid</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="workMode" id="remote" value="Remote">
                  <label class="form-check-label" for="remote">Remote</label>
                </div>
              </div>
            </div>

            <!-- Job Type Filter -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Job Type</label>
              <div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="jobType" id="fullTime" value="Full-time">
                  <label class="form-check-label" for="fullTime">Full-time</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="jobType" id="partTime" value="Part-time">
                  <label class="form-check-label" for="partTime">Part-time</label>
                </div>
              </div>
            </div>

            <!-- Apply Filter Button -->
            <!-- <button id="apply-filter" class="btn post-item-btn w-100">Apply Filters</button> -->
            <div class="post-item-body">
                <div class="post-item-btn">
                    <button id="apply-filter" class="btn-default w-100">Apply Filter</button>
                </div>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <!-- Search Box -->
          <div class="mb-4 text-center d-flex gap-3">
            <input type="text" id="career-search-title" class="form-control w-25" placeholder="Search job title..." autocomplete="">
            <!-- <input type="text" id="career-search-location" class="form-control w-25" placeholder="Search by Location"> -->
          </div>
          <!-- Career Cards -->
          <div id="career-list">
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">System Engineer</h3>
                <a href="system-engineer.html" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 5 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Hybrid</span>
              </div>
            </div>

            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">BPO</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Remote</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">SAP FICO</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">SAP ABAP</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
            <div class="career-card border-bottom p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <h3 class="fw-bold mb-2">Product Designer</h3>
                <a href="#" class="career-apply-btn fw-semibold">Apply <i class="bi bi-arrow-up-right"></i></a>
              </div>

              <div class="d-flex align-items-center flex-wrap mb-3 gap-5">
                <span class="text-muted small">Experience: 2–4 years</span>
                <div class="skills-list d-flex flex-wrap gap-2 align-items-center">
                  <span class="text-muted small">Skills:</span>
                  <span class="skill-tag">Figma</span>
                  <span class="skill-tag">Sketch</span>
                  <span class="skill-tag">UI/UX</span>
                  <span class="skill-tag">Adobe XD</span>
                </div>
              </div>

              <p class="text-muted mb-3">
                We’re looking for a mid-level product designer to join our team.
              </p>

              <div class="d-flex gap-2">
                <span class="career-tag"><i class="bi bi-geo-alt"></i>Coimbatore</span>
                <span class="career-tag"><i class="bi bi-clock"></i> Full-time</span>
              </div>
            </div>
          </div>

          <!-- Pagination Controls -->
          <div id="career-pagination" class="mt-4 text-center"></div>
        </div>
      </div>
    </div>
  </section>
  
    @section('scripts')
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- SimplePagination -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/simplePagination.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/jquery.simplePagination.min.js"></script>
        <script>
            $(document).ready(function () {
                const itemsPerPage = 5;
                let currentPage = 1;

                function paginateAndFilter() {
                    const searchValue = $('#career-search-title').val().toLowerCase();
                    const selectedLocation = $('#filter-location').val();
                    const selectedWorkMode = $('input[name="workMode"]:checked').val();
                    const selectedJobType = $('input[name="jobType"]:checked').val();

                    const $items = $('#career-list .career-card');

                    const filteredItems = $items.filter(function () {
                        const title = $(this).find('h3').text().toLowerCase();
                        const location = $(this).find('.career-tag:contains("Coimbatore"), .career-tag:contains("Chennai"), .career-tag:contains("Bangalore")').text();
                        const jobType = $(this).find('.career-tag:contains("Full-time"), .career-tag:contains("Part-time")').text();
                        const workMode = $(this).find('.career-tag:contains("Hybrid"), .career-tag:contains("Remote")').text();

                        const matchTitle = title.includes(searchValue);
                        const matchLocation = selectedLocation === "" || location.includes(selectedLocation);
                        const matchWorkMode = !selectedWorkMode || workMode.includes(selectedWorkMode);
                        const matchJobType = !selectedJobType || jobType.includes(selectedJobType);

                        return matchTitle && matchLocation && matchWorkMode && matchJobType;
                    });

                    $items.hide();

                    const totalItems = filteredItems.length;
                    const totalPages = Math.ceil(totalItems / itemsPerPage);

                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;

                    filteredItems.slice(start, end).show();

                    // Pagination check
                    if ($.fn.pagination) {
                        $('#career-pagination').pagination({
                            items: totalItems,
                            itemsOnPage: itemsPerPage,
                            currentPage: currentPage,
                            cssStyle: 'light-theme',
                            onPageClick: function (pageNumber) {
                                currentPage = pageNumber;
                                paginateAndFilter();
                            }
                        });
                    } else {
                        console.error("SimplePagination plugin not loaded!");
                    }
                }

                paginateAndFilter();

                $('#career-search-title').on('keyup', function () {
                    currentPage = 1;
                    paginateAndFilter();
                });

                $('#apply-filter').on('click', function () {
                    currentPage = 1;
                    paginateAndFilter();
                });
            });
        </script>
    @endsection
@endsection