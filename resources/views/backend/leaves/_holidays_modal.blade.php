<!-- Employee Holidays List Modal -->
<div class="modal fade atl-modal" id="employeeHolidaysModal" tabindex="-1" aria-labelledby="employeeHolidaysModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" style="z-index: 1085 !important; position: relative !important;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important; box-shadow: 0 16px 48px rgba(0,0,0,0.4) !important;">
            <!-- Modal Header -->
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="ri-calendar-2-line fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="employeeHolidaysModalLabel">
                            Company Holidays (<span id="modalHolidaysYearBadge">{{ $currentYear ?? now()->year }}</span>)
                        </h5>
                        <p class="text-muted small mb-0">Official holidays list. Dates are auto-marked on your attendance.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Filter & Search Bar -->
            <div class="px-4 py-3 bg-white border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="ri-calendar-line"></i></span>
                            <select id="modalHolidaysYearSelect" class="form-select border-start-0">
                                @foreach($holidayYears as $y)
                                    <option value="{{ $y }}" @selected(($currentYear ?? now()->year) === (int)$y)>Year {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="ri-search-line"></i></span>
                            <input type="text" id="modalHolidaysSearchInput" class="form-control border-start-0" placeholder="Search holiday name, festival, or category...">
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-2 pt-1 small">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                        Total: <span id="modalHolidaysTotalCount">{{ $holidays->count() }}</span>
                    </span>
                    @php
                        $upCount = $holidays->filter(fn($h) => $h->date && $h->date->toDateString() >= now()->toDateString())->count();
                    @endphp
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                        Upcoming: <span id="modalHolidaysUpcomingCount">{{ $upCount }}</span>
                    </span>
                    <span class="text-muted ms-auto small" style="font-size: 11px;">
                        <i class="ri-information-line"></i> Required hours = 0 for all holidays
                    </span>
                </div>
            </div>

            <!-- Modal Body (Holidays List) -->
            <div class="modal-body p-4 bg-light-subtle">
                <div id="modalHolidaysLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                    <span class="text-muted small">Loading holidays list...</span>
                </div>

                <div id="modalHolidaysContainer" class="d-flex flex-column gap-2">
                    @forelse($holidays as $h)
                        @php
                            $dateStr = $h->date?->toDateString();
                            $todayStr = now()->toDateString();
                            $isToday = $dateStr === $todayStr;
                            $isUpcoming = $dateStr > $todayStr;
                            $daysLeft = $h->date ? now()->startOfDay()->diffInDays($h->date->startOfDay(), false) : 0;
                        @endphp
                        <div class="card border-0 shadow-sm rounded-3 holiday-item mb-1 p-3 bg-white"
                             data-title="{{ strtolower($h->title) }}"
                             data-type="{{ strtolower($h->type ?? '') }}"
                             data-desc="{{ strtolower($h->description ?? '') }}">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 text-center p-2 border bg-light" style="min-width: 58px;">
                                        <div class="fw-bold text-dark fs-16 lh-1">{{ $h->date?->format('d') }}</div>
                                        <div class="text-muted text-uppercase fw-semibold" style="font-size: 10px;">{{ $h->date?->format('M') }}</div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold text-dark mb-0 fs-15">{{ $h->title }}</h6>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-11 px-2 py-0">
                                                {{ $h->type ?: 'Company' }}
                                            </span>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="ri-time-line me-1"></i>{{ $h->formatted_date }} • <span class="fw-semibold text-secondary">{{ $h->day_name }}</span>
                                            @if($h->description)
                                                <span class="ms-1 text-muted">— {{ $h->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($isToday)
                                        <span class="badge bg-danger rounded-pill px-3 py-1">
                                            <i class="ri-fire-fill me-1"></i> Today
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                            <i class="ri-calendar-check-line me-1"></i> In {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1">
                                            Past
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="modalHolidaysEmpty" class="text-center py-5 text-muted">
                            <i class="ri-calendar-event-line text-muted mb-2 d-block" style="font-size: 40px;"></i>
                            <h6 class="fw-bold text-dark">No Holidays Listed for {{ $currentYear ?? now()->year }}</h6>
                            <p class="small text-muted mb-0">Holidays for this year will appear here once published by Admin.</p>
                        </div>
                    @endforelse
                </div>

                <div id="modalHolidaysNoResults" class="text-center py-4 text-muted d-none">
                    <i class="ri-search-line fs-24 mb-1 d-block"></i>
                    <span class="small">No matching holidays found for your search term.</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white border-top px-4 py-2 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    <i class="ri-shield-user-line me-1 text-primary"></i> Company Approved Calendar
                </span>
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold btn-sm" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function ensureEmployeeHolidaysModalInBody() {
    var el = document.getElementById('employeeHolidaysModal');
    if (el && el.parentElement !== document.body) {
        document.body.appendChild(el);
    }
}
ensureEmployeeHolidaysModalInBody();

document.addEventListener('DOMContentLoaded', function () {
    ensureEmployeeHolidaysModalInBody();

    var modalEl = document.getElementById('employeeHolidaysModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', ensureEmployeeHolidaysModalInBody);
        modalEl.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }

    const yearSelect = document.getElementById('modalHolidaysYearSelect');
    const searchInput = document.getElementById('modalHolidaysSearchInput');
    const container = document.getElementById('modalHolidaysContainer');
    const loading = document.getElementById('modalHolidaysLoading');
    const noResults = document.getElementById('modalHolidaysNoResults');
    const yearBadge = document.getElementById('modalHolidaysYearBadge');
    const totalCount = document.getElementById('modalHolidaysTotalCount');
    const upCount = document.getElementById('modalHolidaysUpcomingCount');

    // Instant client-side search
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const items = container.querySelectorAll('.holiday-item');
            let matchCount = 0;

            items.forEach(item => {
                const title = item.getAttribute('data-title') || '';
                const type = item.getAttribute('data-type') || '';
                const desc = item.getAttribute('data-desc') || '';

                if (!query || title.includes(query) || type.includes(query) || desc.includes(query)) {
                    item.style.display = '';
                    matchCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.classList.toggle('d-none', matchCount > 0 || items.length === 0);
            }
        });
    }

    // Dynamic Year Switching via AJAX
    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            const selectedYear = this.value;
            if (yearBadge) yearBadge.textContent = selectedYear;
            if (loading) loading.classList.remove('d-none');
            if (container) container.innerHTML = '';
            if (noResults) noResults.classList.add('d-none');
            if (searchInput) searchInput.value = '';

            fetch('{{ route("admin.holidays.list-json") }}?year=' + selectedYear)
                .then(res => res.json())
                .then(data => {
                    if (loading) loading.classList.add('d-none');

                    if (totalCount) totalCount.textContent = data.total || 0;

                    let upcoming = 0;
                    const todayStr = '{{ now()->toDateString() }}';

                    if (!data.holidays || data.holidays.length === 0) {
                        if (upCount) upCount.textContent = '0';
                        container.innerHTML = `
                            <div class="text-center py-5 text-muted">
                                <i class="ri-calendar-event-line text-muted mb-2 d-block" style="font-size: 40px;"></i>
                                <h6 class="fw-bold text-dark">No Holidays Listed for ` + selectedYear + `</h6>
                                <p class="small text-muted mb-0">Holidays for this year will appear here once published by Admin.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.holidays.forEach(h => {
                        const isToday = h.date === todayStr;
                        const isUpcoming = h.date > todayStr;
                        if (isUpcoming || isToday) upcoming++;

                        let statusBadge = '';
                        if (isToday) {
                            statusBadge = '<span class="badge bg-danger rounded-pill px-3 py-1"><i class="ri-fire-fill me-1"></i> Today</span>';
                        } else if (isUpcoming) {
                            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1"><i class="ri-calendar-check-line me-1"></i> Upcoming</span>';
                        } else {
                            statusBadge = '<span class="badge bg-light text-muted border rounded-pill px-3 py-1">Past</span>';
                        }

                        const dParts = h.date ? h.date.split('-') : [];
                        const dayNum = dParts[2] || '';
                        const monthAbbr = h.formatted_date ? h.formatted_date.split(' ')[1] : '';

                        html += `
                            <div class="card border-0 shadow-sm rounded-3 holiday-item mb-1 p-3 bg-white"
                                 data-title="` + (h.title || '').toLowerCase() + `"
                                 data-type="` + (h.type || '').toLowerCase() + `"
                                 data-desc="` + (h.description || '').toLowerCase() + `">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 text-center p-2 border bg-light" style="min-width: 58px;">
                                            <div class="fw-bold text-dark fs-16 lh-1">` + dayNum + `</div>
                                            <div class="text-muted text-uppercase fw-semibold" style="font-size: 10px;">` + monthAbbr + `</div>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="fw-bold text-dark mb-0 fs-15">` + (h.title || '') + `</h6>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-11 px-2 py-0">
                                                    ` + (h.type || 'Company') + `
                                                </span>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="ri-time-line me-1"></i>` + (h.formatted_date || '') + ` • <span class="fw-semibold text-secondary">` + (h.day_name || '') + `</span>
                                                ` + (h.description ? '<span class="ms-1 text-muted">— ' + h.description + '</span>' : '') + `
                                            </div>
                                        </div>
                                    </div>
                                    <div>` + statusBadge + `</div>
                                </div>
                            </div>
                        `;
                    });

                    if (upCount) upCount.textContent = upcoming;
                    container.innerHTML = html;
                })
                .catch(err => {
                    if (loading) loading.classList.add('d-none');
                    container.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load holidays for ' + selectedYear + '. Please try again.</div>';
                });
        });
    }
});
</script>
