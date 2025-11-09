@extends('backend.layouts.master')
@section('title')
    {{'Settings'}}
@endsection
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title mb-0">App Settings</h4>
                        </div>

                        <div class="card-body">

                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ session('type', 'general') === 'general' ? 'active' : '' }}"
                                    data-bs-toggle="tab" href="#general" role="tab">General</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ session('type') === 'email' ? 'active' : '' }}"
                                    data-bs-toggle="tab" href="#email" role="tab">Email Settings</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ session('type') === 'social' ? 'active' : '' }}"
                                    data-bs-toggle="tab" href="#social" role="tab">Social Media</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ session('type') === 'maintenance' ? 'active' : '' }}"
                                    data-bs-toggle="tab" href="#maintenance-mode" role="tab">Maintenance Mode</a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content mt-3">

                                <!-- General Tab -->
                                <div class="tab-pane fade {{ session('type', 'general') === 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
                                    <form action="{{ route('admin.settings.store', 'general') }}" method="POST" id="general-setting" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row gy-4">
                                            <div class="col-md-3">
                                                <label class="form-label">App Name</label>
                                                <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Mobile No</label>
                                                <input type="number" name="mobile" class="form-control" value="{{ $settings['mobile'] ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">App Logo</label>
                                                <div class="input-group">
                                                    <input type="file" name="app_logo" class="form-control">
                                                    @if(!empty($settings['app_logo']) && file_exists(public_path($settings['app_logo'])))
                                                        <span class="input-group-text p-0 show_image">
                                                            <img src="{{ asset($settings['app_logo']) }}" alt="App Logo" 
                                                                class="img-thumbnail m-0 show_image" data-title="{{ $settings['app_name'] }}"
                                                                data-url="{{ asset($settings['app_logo']) }}"
                                                                style="width:36px; height:36px; cursor:pointer;">
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control">{{ $settings['address'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <button class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Email Settings Tab -->
                                <div class="tab-pane fade {{ session('type') === 'email' ? 'show active' : '' }}" id="email" role="tabpanel">
                                    <form action="{{ route('admin.settings.store', 'email') }}" method="POST">
                                        @csrf
                                        <div class="row gy-4">
                                            <div class="col-md-3">
                                                <label class="form-label">Mail Driver</label>
                                                <select name="mail_mailer" class="form-select">
                                                    <option value="smtp" {{ ($settings['mail_mailer'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                    <option value="mailgun" {{ ($settings['mail_mailer'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">SMTP Host</label>
                                                <input type="text" name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">SMTP Port</label>
                                                <input type="text" name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">SMTP Encryption</label>
                                                <select name="smtp_encryption" class="form-select">
                                                    <option value="tls" {{ ($settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                                    <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                    <option value="" {{ ($settings['smtp_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">SMTP User</label>
                                                <input type="text" name="smtp_user" class="form-control" value="{{ $settings['smtp_user'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">SMTP Password</label>
                                                <input type="password" name="smtp_pass" class="form-control" value="{{ $settings['smtp_pass'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">From Email</label>
                                                <input type="email" name="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">From Name</label>
                                                <input type="text" name="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="mt-3 text-center">
                                            <button class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Social Media Tab -->
                                <div class="tab-pane fade {{ session('type') === 'social' ? 'show active' : '' }}" id="social" role="tabpanel">
                                    <form action="{{ route('admin.settings.store', 'social') }}" method="POST">
                                        @csrf
                                        <div class="row gy-4">
                                            <div class="col-md-4">
                                                <label class="form-label">Facebook URL</label>
                                                <input type="url" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Twitter URL</label>
                                                <input type="url" name="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Instagram URL</label>
                                                <input type="url" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Youtube URL</label>
                                                <input type="url" name="youtube_url" class="form-control" value="{{ $settings['youtube_url'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <button class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Maintenance Mode Tab -->
                                <div class="tab-pane fade {{ session('type') === 'maintenance' ? 'show active' : '' }}" id="maintenance-mode" role="tabpanel">
                                    <form action="{{ route('admin.settings.store','maintenance') }}" method="POST" id="maintenance-form" class="text-center p-4">
                                        @csrf
                                        
                                        <div class="card mx-auto shadow-lg p-4" style="max-width: 400px; border-radius: 15px;">
                                            <h5 class="mb-4 fw-bold">Maintenance Mode</h5>

                                            <div class="form-check form-switch form-switch-lg mb-3">
                                                <input class="form-check-input" type="checkbox" name="mode" id="maintenanceSwitch"
                                                    value="on"
                                                    {{ (isset($settings['mode']) && $settings['mode'])  == 'on' ? 'checked' : '' }}
                                                    style="width: 3rem; height: 1.5rem;">
                                                <label class="form-check-label" for="maintenanceSwitch" style="font-size: 1.1rem;">
                                                    Enable Maintenance Mode
                                                </label>
                                            </div>

                                            <button type="submit" class="btn btn-primary px-4 mt-3 text-center">
                                                <i class="bi bi-save"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div> <!-- end card-body -->

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('#general-setting').on('click',function(){

            });
            $("#general-setting").validate({
                rules: {
                    app_logo: {
                        required: true,
                        extension: "jpg|jpeg|png|gif|svg"
                    },
                    address:{
                        required:true,
                    },
                    mobile:{
                        required:true,
                        minlength:10,
                        maxlength:10
                    },
                    app_name:{
                        required:true,
                    }
                },
                messages: {
                    app_logo: {
                        required: "Please choose a logo",
                        extension: "Only image files (jpg, jpeg, png, gif, svg) are allowed"
                    }
                }
            });
            $("#change-pass").validate({
                rules: {
                    current_password: {
                        required: true,
                        minlength: 6
                    },
                    new_password: {
                        required: true,
                        minlength: 8
                    },
                    new_password_confirmation: {
                        required: true,
                        equalTo: "#new_password"
                    }
                },
                messages: {
                    current_password: {
                        required: "Please enter your current password",
                        minlength: "Your password must be at least 6 characters long"
                    },
                    new_password: {
                        required: "Please enter a new password",
                        minlength: "New password must be at least 8 characters long"
                    },
                    new_password_confirmation: {
                        required: "Please confirm your new password",
                        equalTo: "Passwords do not match"
                    }
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("text-danger");
                    error.insertAfter(element);
                }
            });

        });
    </script>
@endsection