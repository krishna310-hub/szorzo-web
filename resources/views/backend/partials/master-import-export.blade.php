<div class="d-flex flex-wrap gap-2 justify-content-end">
    @if (!empty($showExportFilters))
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#candidateFilterOffcanvas" aria-controls="candidateFilterOffcanvas">
            <i class="ri-filter-3-line me-1"></i> Filter
        </button>
    @endif
    <a href="{{ route('admin.' . $routePrefix . '.export') }}" class="btn btn-sm btn-success">
        <i class="ri-file-excel-2-line me-1"></i> Export
    </a>
    @can('create', $model)
        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#importSpreadsheetModal">
            <i class="ri-upload-2-line me-1"></i> Import
        </button>
    @endcan
</div>

@can('create', $model)
    <div class="modal fade" id="importSpreadsheetModal" tabindex="-1" aria-labelledby="importSpreadsheetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header">
                    <h5 class="modal-title" id="importSpreadsheetModalLabel">Import {{ $moduleName }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.' . $routePrefix . '.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <div class="fw-semibold mb-1">For the best result, start with the template.</div>
                            <div>Keep the header row unchanged. Related values such as client, job role, location, and billing must already exist in Masters.</div>
                        </div>

                        <a href="{{ route('admin.' . $routePrefix . '.import-template') }}" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="ri-download-2-line me-1"></i> Download Import Template
                        </a>

                        <label for="import_file" class="form-label">Spreadsheet file <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import_file" name="import_file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Accepted: XLSX, XLS, or CSV. Maximum size: 10 MB.</div>

                        <div class="mt-3">
                            <div class="fw-semibold mb-2">Available columns</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($fields as $field)
                                    <span class="badge bg-light text-dark border">{{ $field }}</span>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Record ID is optional. Leave it blank for a new record; keep it when editing exported data.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-upload-2-line me-1"></i> Start Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
