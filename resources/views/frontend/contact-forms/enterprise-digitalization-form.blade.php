@extends('frontend.includes.master')

@section('content')
    <!-- Page Header Start -->
    <div class="page-header-light-red">
        <div class="container">
            <div class="container">
                <div class="row align-items-left">
                    <div class="col-lg-6" style="margin-top: 200px;">
                        <!-- Page Header Box Start -->
                        <div class="page-header-box">
                            <h1 style="color: white; margin-left:-200px" class="wow fadeInUp"
                                data-cursor="-opaque">Contact Us
                                <span></span>
                            </h1>
                        </div>
                        <!-- Page Header Box End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <div class="service-request-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <h1 class="form-title">Enterprise Digitalization</h1>

                    <p class="form-desc">
                        Please let us know what service you are interested in by completing the form below.
                        We will get in touch with you shortly.
                    </p>

                    <p class="mandatory">Mandatory fields are marked with <span class="text-danger">*</span></p>

                    <div style="margin-left:20px">
                        <p class="small-text">All the fields marked with <span class="text-danger">*</span> are required</p>

                        <form id="contactForm">

                            <div class="form-group">
                                <div class="floating-input">
                                    <input type="text" id="firstname" required>
                                    <label for="firstname">FIRST NAME <span class="text-danger">*</span></label>
                                    <small class="error-msg"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="floating-input">
                                    <input type="text" id="lastname" required>
                                    <label for="lastname">LAST NAME <span class="text-danger">*</span></label>
                                    <small class="error-msg"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="floating-input">
                                    <input type="email" id="email" required>
                                    <label for="email">EMAIL <span class="text-danger">*</span></label>
                                    <small class="error-msg"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="floating-input">
                                    <input type="text" id="company" required>
                                    <label for="company">COMPANY <span class="text-danger">*</span></label>
                                    <small class="error-msg"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="relationship" class="form-label">
                                    RELATIONSHIP WITH SZORZO <span class="text-danger">*</span>
                                </label>

                                <select id="relationship" class="form-control select-input" required>
                                    <option value="">Select - Relationship with Szorzo</option>
                                    <option>Customer</option>
                                    <option>Partner</option>
                                    <option>Vendor</option>
                                </select>

                                <small class="error-msg"></small>
                            </div>

                            <div class="form-group">
                                <div class="floating-input">
                                    <input type="number" id="phone_number" required>
                                    <label for="phone_number">PHONE NUMBER <span class="text-danger">*</span></label>
                                    <small class="error-msg"></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="additional_info" class="form-label">
                                    ADDITIONAL INFORMATION <span class="text-danger">*</span>
                                </label>
                                <textarea id="additional_info" name="additional_info" class="form-control line-input" rows="3" required></textarea>
                                <small class="error-msg"></small>
                            </div>

                            <div class="form-check marketing-check">
                                <input type="checkbox" class="form-check-input">
                                <label class="form-check-label">
                                    Opt in for marketing communication
                                    <a href="#" style="text-decoration: underline;">Privacy Statement</a>
                                </label>
                            </div>

                            <button type="submit" class="submit-btn">SUBMIT</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById("contactForm");

        form.addEventListener("submit", function(e) {

            e.preventDefault();
            let isValid = true;

            const fields = form.querySelectorAll("input, textarea, select");

            fields.forEach(function(field) {

                const errorMsg = field.closest(".form-group").querySelector(".error-msg");

                if (field.value.trim() === "") {

                    let label = "";

                    if (field.nextElementSibling && field.nextElementSibling.tagName === "LABEL") {
                        label = field.nextElementSibling.innerText;
                    } else if (field.previousElementSibling) {
                        label = field.previousElementSibling.innerText;
                    }

                    errorMsg.innerText = "Please enter " + label;
                    errorMsg.style.display = "block";

                    isValid = false;

                }

            });

            if (isValid) {
                alert("Form Submitted Successfully");
                form.submit();
            }

        });


        /* REMOVE ERROR WHEN USER TYPES */
        document.querySelectorAll("#contactForm input, #contactForm textarea").forEach(function(field) {

            field.addEventListener("input", function() {

                const errorMsg = this.closest(".form-group").querySelector(".error-msg");

                if (this.value.trim() !== "") {
                    errorMsg.innerText = "";
                    errorMsg.style.display = "none";
                }

            });

        });

        /* REMOVE ERROR WHEN SELECT CHANGES */
        document.querySelector("#relationship").addEventListener("change", function() {

            const errorMsg = this.closest(".form-group").querySelector(".error-msg");

            if (this.value !== "") {
                errorMsg.innerText = "";
                errorMsg.style.display = "none";
            }

        });
    </script>
@endsection
