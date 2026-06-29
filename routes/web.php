<?php

use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\backend\ClientController;
use App\Http\Controllers\backend\LoginController;
use App\Http\Controllers\backend\InterviewLevelController;
use App\Http\Controllers\backend\LocationController;
use App\Http\Controllers\backend\RoleController;
use App\Http\Controllers\backend\SettingsController;
use App\Http\Controllers\backend\UserController;
use App\Http\Controllers\backend\ContactController;
use App\Http\Controllers\backend\PageController;
use App\Http\Controllers\backend\SitemapRobotsController;
use App\Http\Controllers\frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => HomeController::class], function () {
    Route::get('/', 'index')->name('index');
    // pages
    Route::get('/about-us','aboutUs')->name('about.us');
    Route::get('/contact','contact')->name('contact');
    Route::post('/contact/store','contactStore')->name('contact.store');
    Route::get('/careers','careers')->name('careers');
    Route::get('/careers/list','careersList')->name('careers.list');
    Route::get('/szorzo-ai','szorzoAi')->name('szorzo.ai');

    //Enterprise Transformation
    //Enterprice
    Route::get('/enterprice/formation','enterpriceFormation')->name('enterprice.formation');
    Route::get('/marketing/service','marketingService')->name('marketing.service');
    Route::get('/organization-and-capacity-assessment','organizationCapacityAssessment')->name('org.capacity.ass');
    Route::get('/operations/infrastructure-offerings','operationsInfrastructureOfferings')->name('opt.infra.off');
    Route::get('/enterprise-learning-solution','enterpriseLearningSolution')->name('enterprise.learning.solution');
    //Merger & Acquisition Advisory
    Route::get('/strategic/advisory','strategicAdvisory')->name('strategic.advisory');
    Route::get('/target-identification-evaluation','targetIdentificationEvaluation')->name('target.identification.evaluation');
    Route::get('/due-diligence','dueDiligence')->name('due.diligence');
    Route::get('/valuation-and-deal-structuring','valuationDealStructuring')->name('valuation.deal.structuring');
    Route::get('/negotiation-and-deal-execution','negotiationDealExecution')->name('negotiation.deal.execution');
    Route::get('/post-merger-integration','postMergerIntegration')->name('post.merger.integration');
    // IT Infrastructure Services
    Route::get('/it/data-center-design','dataCenterDesign')->name('data.center.design');
    Route::get('/it/data-center-managed-service','dataCenterManagedService')->name('data.center.managed.service');
    Route::get('/it/it-infrastructure','itInfrastructure')->name('it.infrastructure');
    Route::get('/it/cyber-security','cyberSecurity')->name('cyber.security');
    Route::get('/it/certificate-compliance','certificateCompliance')->name('certificate.compliance');
    Route::get('/it/hardware-software','hardwareSoftware')->name('hardware.software');
    // Contact Forms
    Route::get('/contact/szorzo-ai-form','szorzoAiForm')->name('contact.szorzo.ai.form');
    Route::get('/contact/enterprise-transformation-form','enterpriseTransformationForm')->name('contact.enterprise.transformation.form');
    Route::get('/contact/enterprise-digitalization-form','enterpriseDigitalizationForm')->name('contact.enterprise.digitalization.form');
    Route::get('/contact/enterprise-learning-solution-form','enterpriseLearningSolutionForm')->name('contact.enterprise.learning.solution.form');
    Route::get('/contact/organization-capacity-form','organizationCapacityForm')->name('contact.organization.capacity.form');
    Route::get('/contact/operation-hr-offering-form','operationHrOfferingForm')->name('contact.operation.hr.offering.form');
    Route::get('/contact/it-services-form','itServicesForm')->name('contact.it.services.form');
    Route::get('/contact/merger-services-form','mergerServicesForm')->name('contact.merger.services.form');
    // Telecom Services
    Route::get('/telecom-services','telecomServices')->name('telecom.services');
    // Feedback Form
    Route::get('/feedback-form','feedbackForm')->name('feedback.form');
});

Route::group(['controller' => LoginController::class], function () {
    Route::get('/admin/login', 'index')->name('login');
    Route::post('/check-login', 'login')->name('login.check');
    Route::post('/logout', 'logout')->name('logout');
});

Route::middleware(['admin','maintenance'])->name('admin.')->prefix('admin')->group(function () {
    // Maintenance Mode
    Route::get('/lock-screen', [AdminController::class, 'lock'])->name('lock.screen');
    Route::post('/lock-screen', [AdminController::class, 'unlock'])->name('lock.screen.unlock');

    Route::group(['controller' => AdminController::class], function () {
        Route::get('/dashboard','index')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/upload-profile-image', 'uploadProfile')->name('upload.profile');
    });

    // Roles
    Route::prefix('roles')->group(function () {
        Route::resource('role', RoleController::class)->except(['show']);
    });

    // Settings
    Route::prefix('settings')->controller(SettingsController::class)->group(function () {
        Route::get('/index', 'index')->name('settings.index');
        Route::post('/store/{type}', 'store')->name('settings.store');
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::resource('user', UserController::class);
    });

    Route::prefix('pages')->name('pages.')->controller(PageController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::delete('/delete-all','deleteAll')->name('deleteAll');
        Route::post('/bulk-upload', 'bulkUpload')->name('bulk.upload');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/update', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('delete');
    });

    Route::prefix('masters')->group(function () {
        Route::prefix('clients')->name('clients.')->controller(ClientController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });

        Route::prefix('interview-levels')->name('interview-levels.')->controller(InterviewLevelController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });

        Route::prefix('locations')->name('locations.')->controller(LocationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });
    });

    Route::prefix('sitemap')->name('sitemap.')->controller(SitemapRobotsController::class)->group(function () {
        Route::get('/sitemap-robots', 'index')->name('sitemap-robots.index');     
        Route::post('/robots-upload',  'upload')->name('robots.upload'); 
        Route::get('/sitemap-download', 'downloadSitemap')->name('sitemap.download');
        Route::get('/robots-download', 'downloadRobots')->name('robots.download');
    });

    Route::get('/enquiries',[ContactController::class,'index'])->name('enquiry.index');
    Route::delete('/enquiry/delete/{id}',[ContactController::class,'delete'])->name('enquiry.delete');
    Route::post('/enquiry/status',[ContactController::class,'changeStatus'])->name('enquiry.status');

    
});

// Landing Pages
Route::get('{slug}', [HomeController::class, 'landing'])->name('landing');
