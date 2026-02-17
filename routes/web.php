<?php

use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\backend\LoginController;
use App\Http\Controllers\backend\RoleController;
use App\Http\Controllers\backend\SettingsController;
use App\Http\Controllers\backend\UserController;
use App\Http\Controllers\frontend\HomeController;
use Illuminate\Support\Facades\Route;


Route::group(['controller' => HomeController::class], function () {
    Route::get('/', 'index')->name('index');
    // pages
    Route::get('/about-us','aboutUs')->name('about.us');
    Route::get('/contact','contact')->name('contact');
    Route::get('/careers','careers')->name('careers');
    Route::get('/careers/list','careersList')->name('careers.list');

    //Enterprise Transformation
    //Enterprice
    Route::get('/enterprice/formation','enterpriceFormation')->name('enterprice.formation');
    Route::get('/marketing/service','marketingService')->name('marketing.service');
    Route::get('/organization-and-capacity-assessment','organizationCapacityAssessment')->name('org.capacity.ass');
    Route::get('/operations/infrastructure-offerings','operationsInfrastructureOfferings')->name('opt.infra.off');
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

});
