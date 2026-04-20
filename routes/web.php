<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\ConsentLandingController;
use App\Http\Controllers\Public\CouponLandingController;
use App\Http\Controllers\Public\LegalPageController;
use Illuminate\Support\Facades\Route;

// ─── Raíz ───────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Autenticación ───────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Panel Administración ────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Usuarios
    Route::resource('users', Admin\UserController::class)->except(['show']);

    // Roles (básico)
    Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [Admin\RoleController::class, 'create'])->name('roles.create');
    Route::post('roles', [Admin\RoleController::class, 'store'])->name('roles.store');

    // Campañas
    Route::resource('campaigns', Admin\CampaignController::class);
    Route::post('campaigns/{campaign}/activate',        [Admin\CampaignController::class, 'activate'])->name('campaigns.activate');
    Route::post('campaigns/{campaign}/pause',           [Admin\CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/duplicate',       [Admin\CampaignController::class, 'duplicate'])->name('campaigns.duplicate');
    Route::get('campaigns/import/template',                    [Admin\CampaignController::class, 'importTemplate'])->name('campaigns.import.template');
    Route::get('campaigns/{campaign}/import',                  [Admin\CampaignController::class, 'importForm'])->name('campaigns.import.form');
    Route::post('campaigns/{campaign}/import',                 [Admin\CampaignController::class, 'import'])->name('campaigns.import');
    Route::get('campaigns/{campaign}/assign',                  [Admin\CampaignController::class, 'assignForm'])->name('campaigns.assign');
    Route::post('campaigns/{campaign}/assign',                 [Admin\CampaignController::class, 'assign'])->name('campaigns.assign.store');
    Route::get('campaigns/{campaign}/assign/preview',          [Admin\CampaignController::class, 'assignPreview'])->name('campaigns.assign.preview');
    Route::get('campaigns/{campaign}/customers',               [Admin\CampaignController::class, 'customers'])->name('campaigns.customers');
    Route::delete('campaigns/{campaign}/customers/{customer}', [Admin\CampaignController::class, 'removeCustomer'])->name('campaigns.customers.remove');

    // Lotes de Cupones
    Route::get('coupon-batches', [Admin\CouponBatchController::class, 'index'])->name('coupon-batches.index');
    Route::get('coupon-batches/create', [Admin\CouponBatchController::class, 'create'])->name('coupon-batches.create');
    Route::post('coupon-batches', [Admin\CouponBatchController::class, 'store'])->name('coupon-batches.store');
    Route::get('coupon-batches/{couponBatch}', [Admin\CouponBatchController::class, 'show'])->name('coupon-batches.show');
    Route::get('coupon-batches/{couponBatch}/edit', [Admin\CouponBatchController::class, 'edit'])->name('coupon-batches.edit');
    Route::put('coupon-batches/{couponBatch}', [Admin\CouponBatchController::class, 'update'])->name('coupon-batches.update');
    Route::post('coupon-batches/{couponBatch}/activate', [Admin\CouponBatchController::class, 'activate'])->name('coupon-batches.activate');
    Route::post('coupon-batches/{couponBatch}/pause', [Admin\CouponBatchController::class, 'pause'])->name('coupon-batches.pause');

    // Redenciones
    Route::get('redemptions', [Admin\RedemptionController::class, 'index'])->name('redemptions.index');
    Route::get('redemptions/{redemption}', [Admin\RedemptionController::class, 'show'])->name('redemptions.show');
    Route::post('redemptions/{redemption}/reverse', [Admin\RedemptionController::class, 'reverse'])->name('redemptions.reverse');

    // Clientes — rutas fijas ANTES de {customer} para evitar colisiones
    Route::get('customers',                          [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create',                   [Admin\CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers',                         [Admin\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/import',                   [Admin\CustomerController::class, 'import'])->name('customers.import');
    Route::post('customers/import',                  [Admin\CustomerController::class, 'processImport'])->name('customers.import.process');
    Route::get('customers/template',                 [Admin\CustomerController::class, 'downloadTemplate'])->name('customers.template');
    Route::get('customers/{customer}',               [Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit',          [Admin\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}',               [Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::post('customers/{customer}/block',        [Admin\CustomerController::class, 'block'])->name('customers.block');
    Route::post('customers/{customer}/unblock',      [Admin\CustomerController::class, 'unblock'])->name('customers.unblock');

    // Documentos Legales
    Route::get('legal-documents',                              [Admin\LegalDocumentController::class, 'index'])->name('legal-documents.index');
    Route::get('legal-documents/create',                       [Admin\LegalDocumentController::class, 'create'])->name('legal-documents.create');
    Route::post('legal-documents',                             [Admin\LegalDocumentController::class, 'store'])->name('legal-documents.store');
    Route::get('legal-documents/{legalDocument}',              [Admin\LegalDocumentController::class, 'show'])->name('legal-documents.show');
    Route::post('legal-documents/{legalDocument}/publish',     [Admin\LegalDocumentController::class, 'publish'])->name('legal-documents.publish');
    Route::delete('legal-documents/{legalDocument}',           [Admin\LegalDocumentController::class, 'destroy'])->name('legal-documents.destroy');

    // Landing Pages personalizables
    Route::get('landing-configs',                                   [Admin\LandingConfigController::class, 'index'])->name('landing-configs.index');
    Route::get('landing-configs/create',                            [Admin\LandingConfigController::class, 'create'])->name('landing-configs.create');
    Route::post('landing-configs',                                  [Admin\LandingConfigController::class, 'store'])->name('landing-configs.store');
    Route::get('landing-configs/{landingConfig}/edit',              [Admin\LandingConfigController::class, 'edit'])->name('landing-configs.edit');
    Route::put('landing-configs/{landingConfig}',                   [Admin\LandingConfigController::class, 'update'])->name('landing-configs.update');
    Route::delete('landing-configs/{landingConfig}',                [Admin\LandingConfigController::class, 'destroy'])->name('landing-configs.destroy');
    Route::get('landing-configs/{landingConfig}/preview',           [Admin\LandingConfigController::class, 'preview'])->name('landing-configs.preview');

    // Campañas SMS
    Route::get('sms-campaigns', [Admin\SmsCampaignController::class, 'index'])->name('sms-campaigns.index');
    Route::get('sms-campaigns/create', [Admin\SmsCampaignController::class, 'create'])->name('sms-campaigns.create');
    Route::post('sms-campaigns', [Admin\SmsCampaignController::class, 'store'])->name('sms-campaigns.store');
    Route::get('sms-campaigns/{smsCampaign}', [Admin\SmsCampaignController::class, 'show'])->name('sms-campaigns.show');
    Route::post('sms-campaigns/{smsCampaign}/send', [Admin\SmsCampaignController::class, 'send'])->name('sms-campaigns.send');
    Route::post('sms-campaigns/{smsCampaign}/cancel', [Admin\SmsCampaignController::class, 'cancel'])->name('sms-campaigns.cancel');
    Route::post('sms-campaigns/{smsCampaign}/retry',  [Admin\SmsCampaignController::class, 'retry'])->name('sms-campaigns.retry');
    Route::post('sms-campaigns/{smsCampaign}/recipients/{recipient}/retry', [Admin\SmsCampaignController::class, 'retryRecipient'])->name('sms-campaigns.recipients.retry');
    Route::post('sms-campaigns/{smsCampaign}/sync-recipients',             [Admin\SmsCampaignController::class, 'syncRecipients'])->name('sms-campaigns.sync-recipients');

    // API Clients — rutas fijas ANTES de {apiClient}
    Route::get('api-clients',                               [Admin\ApiClientController::class, 'index'])->name('api-clients.index');
    Route::get('api-clients/create',                        [Admin\ApiClientController::class, 'create'])->name('api-clients.create');
    Route::post('api-clients',                              [Admin\ApiClientController::class, 'store'])->name('api-clients.store');
    Route::get('api-clients/docs',                          [Admin\ApiClientController::class, 'docs'])->name('api-clients.docs');
    Route::get('api-clients/tester',                        [Admin\ApiClientController::class, 'tester'])->name('api-clients.tester');
    Route::get('api-clients/{apiClient}',                   [Admin\ApiClientController::class, 'show'])->name('api-clients.show');
    Route::get('api-clients/{apiClient}/edit',              [Admin\ApiClientController::class, 'edit'])->name('api-clients.edit');
    Route::put('api-clients/{apiClient}',                   [Admin\ApiClientController::class, 'update'])->name('api-clients.update');
    Route::post('api-clients/{apiClient}/rotate',           [Admin\ApiClientController::class, 'rotate'])->name('api-clients.rotate');
    Route::post('api-clients/{apiClient}/activate',         [Admin\ApiClientController::class, 'activate'])->name('api-clients.activate');
    Route::post('api-clients/{apiClient}/deactivate',       [Admin\ApiClientController::class, 'deactivate'])->name('api-clients.deactivate');
    Route::post('api-clients/{apiClient}/revoke',           [Admin\ApiClientController::class, 'revoke'])->name('api-clients.revoke');

    // Geografía
    Route::get('geography',                              [Admin\GeographyController::class, 'index'])->name('geography.index');
    Route::post('geography/cities',                      [Admin\GeographyController::class, 'storeCity'])->name('geography.cities.store');
    Route::post('geography/zones',                       [Admin\GeographyController::class, 'storeZone'])->name('geography.zones.store');
    Route::post('geography/pos',                         [Admin\GeographyController::class, 'storePOS'])->name('geography.pos.store');
    Route::patch('geography/pos/{pos}/toggle',           [Admin\GeographyController::class, 'togglePOS'])->name('geography.pos.toggle');
    Route::delete('geography/pos/{pos}',                 [Admin\GeographyController::class, 'destroyPOS'])->name('geography.pos.destroy');

    // Auditoría
    Route::get('audit',                                           [Admin\AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/{auditLog}',                                [Admin\AuditController::class, 'show'])->name('audit.show');
    Route::post('audit/alerts/{alert}/resolve',                   [Admin\AuditController::class, 'resolveAlert'])->name('audit.alerts.resolve');

    // Manual de usuario
    Route::get('manual', [Admin\ManualController::class, 'index'])->name('manual');
});

// ─── Páginas Públicas ────────────────────────────────────────────────────────
Route::prefix('')->name('public.')->group(function () {
    Route::get('/terminos-y-condiciones', [LegalPageController::class, 'terms'])->name('legal.terms');
    Route::get('/politica-de-privacidad', [LegalPageController::class, 'privacy'])->name('legal.privacy');
    Route::get('/consentimiento-sms', [LegalPageController::class, 'smsConsent'])->name('legal.sms');
    Route::get('/aceptar/{type}', [LegalPageController::class, 'accept'])->name('legal.accept');
    Route::post('/aceptar/{type}', [LegalPageController::class, 'storeAcceptance'])->name('legal.accept.store');
    Route::get('/cupon/{code}', [CouponLandingController::class, 'show'])->name('coupon.landing');
    Route::post('/cupon/check', [CouponLandingController::class, 'check'])->name('coupon.check');
    // Consentimiento SMS
    Route::get('/autorizar/{token}', [ConsentLandingController::class, 'show'])->name('consent.show');
    Route::post('/autorizar/{token}', [ConsentLandingController::class, 'accept'])->name('consent.accept');
});
