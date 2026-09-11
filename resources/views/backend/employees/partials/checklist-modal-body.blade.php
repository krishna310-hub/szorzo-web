@php
    $percent = $completion['percentage'] ?? 0;
    $color = $employee->progress_color;
    $badgeClass = $percent >= 80 ? 'bg-success text-white' : ($percent >= 40 ? 'bg-warning text-dark' : 'bg-danger text-white');
@endphp

<div class="checklist-modal-container">
    <!-- Top Progress Banner -->
    <div class="card border mb-3 bg-light-subtle shadow-none">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <!-- Profile Avatar with Circular Progress Ring -->
                    <div class="avatar-progress-container avatar-lg-ring" title="Profile Completion: {{ $percent }}%">
                        <div class="avatar-circle-ring" style="background: conic-gradient({{ $color }} 0% {{ $percent }}%, #e9ebec {{ $percent }}% 100%); width: 70px; height: 70px; padding: 4px;">
                            <img src="{{ $employee->avatar_url }}" alt="{{ $employee->employee_name }}" class="avatar-circle-img" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <span class="avatar-percent-pill {{ $badgeClass }}" style="bottom: -2px; right: -2px; font-size: 11px;">
                            {{ $percent }}%
                        </span>
                    </div>

                    <div>
                        <h5 class="mb-1 text-dark fw-bold">{{ $employee->employee_name }}</h5>
                        <p class="text-muted mb-1 small">
                            <span class="me-2"><i class="bx bx-id-card me-1"></i>ID: <strong>{{ $employee->employee_no ?? 'N/A' }}</strong></span>
                            @if($employee->designation)
                                <span><i class="bx bx-briefcase me-1"></i>{{ $employee->designation }}</span>
                            @endif
                        </p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $badgeClass }} px-2 py-1 fs-6">
                                <i class="bx bx-check-double me-1"></i>Complete: {{ $completion['ratio_text'] }} ({{ $percent }}%)
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ $completion['verified_count'] }} of {{ $completion['total_count'] }} Verified
                            </span>
                            <span class="badge bg-info-subtle text-info">
                                {{ $completion['uploaded_count'] }} Documents Uploaded
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-success verify-all-uploaded-btn mb-1" title="Mark all uploaded documents as verified">
                        <i class="bx bx-check-double me-1"></i>Verify All Uploaded
                    </button>
                    <div class="small text-muted">Check/uncheck boxes below to update verification.</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mt-3" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $color }};" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- 10 Point Checklist Table & Responsive Cards -->
    <div class="table-responsive-md">
        <table class="table table-bordered table-hover align-middle mb-0 checklist-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Checklist Point / Document</th>
                    <th style="width: 260px;">Uploaded Document(s)</th>
                    <th style="width: 170px;" class="text-center">Manual Verify</th>
                    <th style="width: 220px;">Notes / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completion['items'] as $key => $item)
                    @php
                        $isVerified = $item['is_verified'];
                        $isUploaded = $item['is_uploaded'];
                        $rowClass = $isVerified ? 'table-success-subtle' : ($isUploaded ? 'table-warning-subtle' : '');
                    @endphp
                    <tr class="checklist-row {{ $rowClass }}" data-key="{{ $key }}" data-uploaded="{{ $isUploaded ? '1' : '0' }}">
                        <td class="text-center fw-bold text-muted d-none d-md-table-cell checklist-col-num">{{ $item['number'] }}</td>
                        <td class="checklist-col-doc">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill d-md-none fw-bold px-2 py-1">#{{ $item['number'] }}</span>
                                    <div class="fw-semibold text-dark fs-6">{{ $item['label'] }}</div>
                                </div>
                                <div class="d-md-none checklist-mobile-status">
                                    @if($isVerified)
                                        <span class="badge bg-success-subtle text-success"><i class="bx bx-check me-1"></i>Verified</span>
                                    @elseif($isUploaded)
                                        <span class="badge bg-warning-subtle text-warning"><i class="bx bx-time-five me-1"></i>Uploaded</span>
                                    @else
                                        <span class="badge bg-light text-muted border">Pending</span>
                                    @endif
                                </div>
                            </div>
                            @if($key === 'personal_details')
                                <small class="text-muted d-block mt-1">Basic profile, contact and address information</small>
                            @elseif($key === 'photograph')
                                <small class="text-muted d-block mt-1">Passport size photo for profile &amp; ID</small>
                            @elseif($key === 'educational_certificates')
                                <small class="text-muted d-block mt-1">10th, 12th, Degree, Post-Graduation certificates</small>
                            @endif
                        </td>
                        <td class="checklist-col-files checklist-card-section">
                            <div class="checklist-mobile-label">
                                <i class="bx bx-file"></i><span>Uploaded Document(s)</span>
                            </div>
                            @if($isUploaded)
                                @if(!empty($item['files']))
                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                        @foreach($item['files'] as $file)
                                            <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="{{ $file['name'] }}">
                                                <i class="bx bx-file"></i>
                                                <span>{{ $file['label'] ?? 'Document ' . $loop->iteration }}</span>
                                                <i class="ri-external-link-line" style="font-size: 10px;"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @elseif($key === 'personal_details')
                                    <span class="badge bg-success-subtle text-success"><i class="bx bx-check me-1"></i>Details Provided</span>
                                @else
                                    <span class="badge bg-success-subtle text-success mb-1"><i class="bx bx-check me-1"></i>Uploaded</span>
                                @endif
                            @else
                                <div class="mb-1"><span class="badge bg-light text-muted border"><i class="bx bx-x me-1"></i>Not Uploaded</span></div>
                            @endif

                            @if($key !== 'personal_details')
                                <div class="mt-1 pt-1 border-top">
                                    @if($item['type'] === 'document_array')
                                        <input type="file" class="form-control form-control-sm checklist-file-upload" name="{{ $key }}[]" multiple accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px;">
                                    @elseif($item['type'] === 'image')
                                        <input type="file" class="form-control form-control-sm checklist-file-upload" name="employee_image" accept="image/*" style="font-size: 11px;">
                                    @elseif($key === 'pan_card')
                                        <input type="file" class="form-control form-control-sm checklist-file-upload" name="pan_card_file" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px;">
                                    @elseif($key === 'aadhaar_card')
                                        <input type="file" class="form-control form-control-sm checklist-file-upload" name="aadhaar_file" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px;">
                                    @elseif($key === 'educational_certificates')
                                        <input type="file" class="form-control form-control-sm checklist-file-upload" name="educational_certificates[]" multiple accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px;">
                                    @endif
                                    <span class="text-muted" style="font-size: 10px;"><i class="bx bx-cloud-upload me-1"></i>Admin upload</span>
                                </div>
                            @endif
                        </td>
                        <td class="text-md-center checklist-col-verify checklist-card-section">
                            <div class="checklist-mobile-label">
                                <i class="bx bx-check-shield"></i><span>Manual Verification</span>
                            </div>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input checklist-checkbox" type="checkbox"
                                       id="chk_verify_{{ $key }}"
                                       name="checklist[{{ $key }}]"
                                       value="1"
                                       {{ $isVerified ? 'checked' : '' }}
                                       style="cursor: pointer; transform: scale(1.15);">
                                <label class="form-check-label fw-semibold ms-1 checklist-label {{ $isVerified ? 'text-success' : 'text-muted' }}" for="chk_verify_{{ $key }}">
                                    {{ $isVerified ? 'Verified' : 'Pending' }}
                                </label>
                            </div>
                            @if($isVerified && !empty($item['verified_at']))
                                <div class="text-muted mt-1" style="font-size: 10px;">
                                    <i class="bx bx-time-five me-1"></i>{{ $item['verified_at'] }}
                                    @if(!empty($item['verified_by_name']))
                                        <br><span>by {{ $item['verified_by_name'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="checklist-col-notes checklist-card-section">
                            <div class="checklist-mobile-label">
                                <i class="bx bx-notepad"></i><span>Notes / Remarks</span>
                            </div>
                            <input type="text" class="form-control form-control-sm checklist-notes-input"
                                   name="notes[{{ $key }}]"
                                   value="{{ $item['notes'] ?? '' }}"
                                   placeholder="Verification notes / comment"
                                   style="font-size: 12px;">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

