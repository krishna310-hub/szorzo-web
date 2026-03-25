<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI-Driven Drug Discovery Form</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: #f4f7fb;
        font-family: 'Segoe UI', sans-serif;
    }

    .form-container {
        max-width: 1100px;
        margin: 40px auto;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .card-header {
        background: linear-gradient(90deg, #FF0000, #FF0000);
        color: white;
        font-weight: 600;
        border-radius: 15px 15px 0 0 !important;
    }

    .form-check {
        margin-bottom: 8px;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 15px;
        color: #FF0000;
    }

    .btn-submit {
        background: linear-gradient(90deg, #FF0000, #FF0000);
        border: none;
        padding: 12px;
        font-weight: 600;
        border-radius: 10px;
    }

    textarea {
        resize: none;
    }
</style>
</head>

<body>

<div class="container form-container">

    <h2 class="text-center mb-4">AI-Driven Drug Discovery Needs Assessment</h2>

    <form>

        <!-- Section 1 -->
        <div class="card">
            <div class="card-header">Section 1 — Company & Project Background</div>
            <div class="card-body">

                <p class="section-title">1. Therapeutic Area</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Oncology</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Immunology</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Rare Disease</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Neurology</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Infectious Disease</div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Other (specify)">
                    </div>
                </div>

                <p class="section-title mt-4">2. Program Stage</p>
                <select class="form-select">
                    <option>Select Stage</option>
                    <option>Target discovery</option>
                    <option>Hit identification</option>
                    <option>Lead optimization</option>
                    <option>Preclinical</option>
                    <option>Clinical (Phase I–III)</option>
                    <option>Post-market</option>
                </select>

                <p class="section-title mt-4">3. Primary Objective</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Identify novel biomarkers</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Validate biomarkers</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> New drug targets</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Mechanism of action</div>
                        <div class="form-check"><input type="checkbox" class="form-check-input"> Patient stratification</div>
                        <input type="text" class="form-control mt-2" placeholder="Other">
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 2 -->
        <div class="card">
            <div class="card-header">Section 2 — Data Availability</div>
            <div class="card-body">

                <p class="section-title">4. Available Datasets</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check"><input type="checkbox"> RNA-seq</div>
                        <div class="form-check"><input type="checkbox"> Proteomics</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check"><input type="checkbox"> Single-cell</div>
                        <div class="form-check"><input type="checkbox"> Clinical metadata</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check"><input type="checkbox"> Public datasets only</div>
                        <div class="form-check"><input type="checkbox"> No data</div>
                    </div>
                </div>

                <p class="section-title mt-4">5. Data Support Needed?</p>
                <select class="form-select">
                    <option>Yes</option>
                    <option>No</option>
                    <option>Not sure</option>
                </select>

                <p class="section-title mt-4">6. External Data Integration</p>
                <select class="form-select">
                    <option>Yes</option>
                    <option>No</option>
                    <option>Need guidance</option>
                </select>

            </div>
        </div>

        <!-- Section 3 -->
        <div class="card">
            <div class="card-header">Section 3 — AI & Analytics</div>
            <div class="card-body">

                <p class="section-title">7. AI Services</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check"><input type="checkbox"> Biomarker discovery</div>
                        <div class="form-check"><input type="checkbox"> Target identification</div>
                        <div class="form-check"><input type="checkbox"> Multi-omics integration</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check"><input type="checkbox"> Predictive modeling</div>
                        <div class="form-check"><input type="checkbox"> Generative AI</div>
                        <div class="form-check"><input type="checkbox"> Custom model</div>
                    </div>
                </div>

                <p class="section-title mt-4">8. Interpretability</p>
                <select class="form-select">
                    <option>High (Regulatory-ready)</option>
                    <option>Medium</option>
                    <option>Low</option>
                </select>

                <p class="section-title mt-4">9. Output Format</p>
                <div class="form-check"><input type="checkbox"> Full report</div>
                <div class="form-check"><input type="checkbox"> Executive summary</div>
                <div class="form-check"><input type="checkbox"> Dashboards</div>
                <div class="form-check"><input type="checkbox"> Raw outputs</div>

            </div>
        </div>

        <!-- Section 4 -->
        <div class="card">
            <div class="card-header">Section 4 — Validation</div>
            <div class="card-body">

                <p class="section-title">10. Experimental Validation</p>
                <div class="form-check"><input type="checkbox"> CRISPR</div>
                <div class="form-check"><input type="checkbox"> qPCR</div>
                <div class="form-check"><input type="checkbox"> Reporter assays</div>

                <p class="section-title mt-4">11. Evidence Level</p>
                <select class="form-select">
                    <option>Computational only</option>
                    <option>+ In vitro</option>
                    <option>+ In vivo</option>
                    <option>Regulatory-grade</option>
                </select>

            </div>
        </div>

        <!-- Section 5 -->
        <div class="card">
            <div class="card-header">Section 5 — Project Scope</div>
            <div class="card-body">

                <p class="section-title">12. Timeline</p>
                <select class="form-select">
                    <option>< 1 month</option>
                    <option>1–3 months</option>
                    <option>3–6 months</option>
                    <option>> 6 months</option>
                </select>

                <p class="section-title mt-4">13. Engagement Model</p>
                <select class="form-select">
                    <option>One-time</option>
                    <option>Ongoing</option>
                    <option>AI partner</option>
                </select>

                <p class="section-title mt-4">14. Budget</p>
                <select class="form-select">
                    <option>< €25k</option>
                    <option>€25k–€75k</option>
                    <option>€75k–€150k</option>
                    <option>> €150k</option>
                </select>

            </div>
        </div>

        <!-- Section 6 -->
        <div class="card">
            <div class="card-header">Additional Notes</div>
            <div class="card-body">

                <textarea class="form-control mb-3" rows="4" placeholder="Describe challenges or expectations..."></textarea>

                <p class="section-title">How did you hear about us?</p>
                <select class="form-select">
                    <option>Referral</option>
                    <option>Conference</option>
                    <option>Website</option>
                    <option>Publication</option>
                </select>

                <p class="section-title mt-4">Preferred Contact</p>
                <div class="form-check"><input type="radio" name="contact"> Email</div>
                <div class="form-check"><input type="radio" name="contact"> Virtual Meeting</div>
                <div class="form-check"><input type="radio" name="contact"> Phone</div>

                <p class="section-title mt-4">Free Consultation?</p>
                <div class="form-check"><input type="radio" name="consult"> Yes</div>
                <div class="form-check"><input type="radio" name="consult"> No</div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-submit">Submit Assessment</button>

    </form>

</div>

</body>
</html>
