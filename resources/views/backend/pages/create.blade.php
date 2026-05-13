@extends('backend.layouts.master')
@section('title')
    {{ 'Landing Pages' }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Add Page</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="pageForm" action="{{ route('admin.pages.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Page Name</label>
                                            <input type="text" name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name') }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Banner Image</label>
                                            <input type="file" name="image"
                                                class="form-control @error('image') is-invalid @enderror">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        {{-- <div class="col-md-6">
                    <label class="form-label">URL Slug</label>
                    <input type="text" name="url_slug" class="form-control @error('url_slug') is-invalid @enderror"
                           value="{{ old('url_slug') }}">
                    @error('url_slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> --}}
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="location"
                                                class="form-control @error('location') is-invalid @enderror"
                                                value="{{ old('location') }}">
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <input type="text" name="category"
                                                class="form-control @error('category') is-invalid @enderror"
                                                value="{{ old('category') }}">
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">URL Slug</label>
                                            <input type="text" name="url"
                                                class="form-control @error('url') is-invalid @enderror"
                                                value="{{ old('url') }}">
                                            @error('url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6 banner-editor">
                                            <label class="form-label">Banner Content</label>
                                            <textarea id="banner_content" name="banner_content" class="form-control @error('banner_content') is-invalid @enderror"
                                                rows="3">{{ old('banner_content') }}</textarea>
                                            @error('banner_content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 page-editor">
                                            <label class="form-label">Page Content</label>
                                            <textarea id="page_content" name="page_content" class="form-control @error('page_content') is-invalid @enderror"
                                                rows="10">{{ old('page_content') }}</textarea>
                                            @error('page_content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">FAQs</label>

                                        <div id="faq-container">
                                            <div class="faq-item row mb-2">
                                                <div class="col-sm-4">
                                                    <input type="text" name="faqs[1][question]"
                                                        class="form-control faq-question" placeholder="Question">
                                                </div>
                                                <div class="col-sm-7">
                                                    <textarea name="faqs[1][answer]" class="form-control faq-answer" placeholder="Answer" rows="4"></textarea>
                                                </div>
                                                {{-- <div class="col-sm-3">
                            <textarea name="faqs[1][schema]"
                                    class="form-control faq-schema"
                                    placeholder="Paste FAQ Schema here"
                                    style="height: 80px; overflow-y: auto;"></textarea>
                        </div> --}}
                                                <div class="col-sm-1">
                                                    <button type="button" class="btn btn-sm btn-primary mt-2"
                                                        id="add-faq">Add</button>
                                                </div>
                                            </div>
                                        </div>

                                        @error('faqs')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Meta Title</label>
                                            <input type="text" name="meta_title"
                                                class="form-control @error('meta_title') is-invalid @enderror"
                                                value="{{ old('meta_title') }}">
                                            @error('meta_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Meta Description</label>
                                            <input type="text" name="meta_description"
                                                class="form-control @error('meta_description') is-invalid @enderror"
                                                value="{{ old('meta_description') }}">
                                            @error('meta_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Meta Keywords</label>
                                            <input type="text" name="meta_keywords"
                                                class="form-control @error('meta_keywords') is-invalid @enderror"
                                                value="{{ old('meta_keywords') }}">
                                            @error('meta_keywords')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Status</label>
                                            <select name="status"
                                                class="form-select @error('status') is-invalid @enderror">
                                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active
                                                </option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                                    Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success">Create Page</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        ClassicEditor.create(document.querySelector('#banner_content'), {
                toolbar: [
                    'heading',
                    '|',
                    'bold', 'italic', 'underline', 'strikethrough',
                    'link',
                    'bulletedList', 'numberedList',
                    'blockQuote',
                    'undo', 'redo'
                ]
            })
            .then(editor => {
                // FORCE banner height
                editor.ui.view.editable.element.style.height = '150px';
                editor.ui.view.editable.element.style.overflowY = 'auto';
            })
            .catch(error => console.error(error));

        ClassicEditor.create(document.querySelector('#page_content'), {
                toolbar: [
                    'heading',
                    '|',
                    'bold', 'italic', 'underline', 'strikethrough',
                    'link',
                    'bulletedList', 'numberedList',
                    'blockQuote',
                    'undo', 'redo'
                ]
            })
            .then(editor => {
                // FORCE page content height
                editor.ui.view.editable.element.style.height = '400px';
                editor.ui.view.editable.element.style.overflowY = 'auto';
            })
            .catch(error => console.error(error));
    </script>
    <script>
        $(document).ready(function() {
            $.validator.addClassRules('faq-question', {
                required: true,
                maxlength: 255
            });
            // $.validator.addClassRules('faq-schema', {
            //     required: true,
            // });
            $.validator.addClassRules('faq-answer', {
                required: true,
                maxlength: 1000
            });
            $('#pageForm').validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 255
                    },
                    // url_slug: { required: true, maxlength: 255 },
                    url: {
                        required: true,
                        maxlength: 255
                    },
                    location: {
                        required: true,
                        maxlength: 255
                    },
                    category: {
                        required: true,
                        maxlength: 255
                    },
                    meta_title: {
                        required: true,
                        maxlength: 255
                    },
                    meta_description: {
                        required: true,
                        maxlength: 255
                    },
                    meta_keywords: {
                        required: true,
                        maxlength: 255
                    },
                    banner_content: {
                        required: true,
                        maxlength: 1500
                    },
                    page_content: {
                        required: true,
                        maxlength: 1500
                    },
                    // image: { extension: "jpg|jpeg|png|webp" },
                    status: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please enter page name",
                        maxlength: "Name cannot exceed 255 characters"
                    },
                    url_slug: {
                        required: "Please enter URL slug",
                        maxlength: "Slug cannot exceed 255 characters"
                    },
                    location: {
                        required: "Please enter location",
                        maxlength: "Location cannot exceed 255 characters"
                    },
                    category: {
                        required: "Please enter category",
                        maxlength: "Category cannot exceed 255 characters"
                    },
                    // image: { extension: "Please upload a valid image (jpg, jpeg, png, webp)" },
                    status: {
                        required: "Please select status"
                    }
                },
                errorClass: 'text-danger',
                errorElement: 'div',
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function(form) {

                    // Sync CKEditor → textarea
                    if (bannerEditor) {
                        $('#banner_content').val(bannerEditor.getData());
                    }
                    if (pageEditor) {
                        $('#page_content').val(pageEditor.getData());
                    }

                    form.submit();
                }
            });

            $('#add-faq').click(function() {

                let faqCount = $('.faq-item').length; // 0,1,2...
                faqCount++;
                let faqItem = `
                <div class="faq-item row mb-2">
                    <div class="col-sm-4">
                        <input type="text"
                            name="faqs[${faqCount}][question]"
                            class="form-control faq-question"
                            placeholder="Question">
                    </div>
                    <div class="col-sm-7">
                        <textarea name="faqs[${faqCount}][answer]"
                                class="form-control faq-answer"
                                placeholder="Answer"
                                rows="4"></textarea>
                    </div>
                    
                    <div class="col-sm-1">
                        <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
                    </div>
                </div>
            `;

                $('#faq-container').append(faqItem);
            });


            $(document).on('click', '.remove-faq', function() {
                $(this).closest('.faq-item').remove();
            });
        });
    </script>
@endsection
