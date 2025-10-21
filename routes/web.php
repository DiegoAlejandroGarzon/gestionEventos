<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\LayoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\EventAssistantController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TicketFeatureController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\Chats\createManagementController;

Route::get('theme-switcher/{activeTheme}', [ThemeController::class, 'switch'])->name('theme-switcher');
Route::get('layout-switcher/{activeLayout}', [LayoutController::class, 'switch'])->name('layout-switcher');
Route::post('/login', [LoginController::class, 'login']);
Route::get('login', [PageController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/RegisterUsers', [UserController::class, 'RegisterUsers'])->name('users.register');
Route::post('/RegisterUsers/create', [UserController::class, 'RegisterUsersStore'])->name('users.registerStore');
Route::get('/resetPassword', [UserController::class, 'resetPasswordIndex'])->name('users.resetPasswordIndex');
Route::post('/resetPassword', [UserController::class, 'resetPassword'])->name('users.resetPassword');
// Mostrar formulario para solicitar enlace de restablecimiento de contraseña
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Enviar enlace de restablecimiento de contraseña
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// Mostrar formulario de restablecimiento de contraseña
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
// Procesar el restablecimiento de contraseña
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
//inscripcion de asistente
Route::get('/event/register/{public_link}', [EventController::class, 'showPublicRegistrationForm'])->name('event.register');
Route::post('/event/register/{public_link}', [EventController::class, 'submitPublicRegistration'])->name('event.register.submit');
Route::get('/event-assistant/infoQr/{id}/{guid}', [EventAssistantController::class, 'infoQr'])->name('eventAssistant.infoQr');
Route::get('/event-assistant/infoQrCoupon/{id}/{guid}', [CouponController::class, 'infoQrCoupon'])->name('coupon.infoQr');
Route::post('/cuopon/register/{public_link}', [CouponController::class, 'submitPublicRegistration'])->name('coupon.register.submit');
Route::get('/evento/{public_link}/download-pdf/{id}', [PDFController::class, 'getPDFEvento'])->name('event.download.pdf');
Route::get('/check-courtesy-code/{eventId}/{code}', [CouponController::class, 'checkCourtesyCode'])->name('check.courtesy.code');
Route::get('/get-seats-by-ticket-type/{ticketTypeId}', [SeatController::class, 'getSeatsByTicketType']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    //CRUD USUARIOS
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users/create', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/update/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/check-record', [UserController::class, 'checkRecord'])->name('checkRecord');

    //CRUD CONFIGURACIONES
    //CRUD CONFIGURACION DEPARTAMENTO
    Route::get('/department',[DepartmentController::class,'list'])->name('department.index');
    Route::get('/department/create',[DepartmentController::class,'create'])->name('department.create');
    Route::post('/department/create',[DepartmentController::class,'store'])->name('department.store');
    Route::get('/department/update/{id}',[DepartmentController::class,'edit'])->name('department.edit');
    Route::post('/department/update/',[DepartmentController::class,'update'])->name('department.update');
    Route::get('/department/delete/{id}',[DepartmentController::class,'delete'])->name('department.delete');

    //CRUD CONFIGURACION CIUDAD
    Route::get('/city',[CityController::class,'list'])->name('city.index');
    Route::get('/city/create',[CityController::class,'create'])->name('city.create');
    Route::post('/city/create',[CityController::class,'store'])->name('city.store');
    Route::get('/city/update/{id}',[CityController::class,'edit'])->name('city.edit');
    Route::post('/city/update/',[CityController::class,'update'])->name('city.update');
    Route::get('/city/delete/{id}',[CityController::class,'delete'])->name('city.delete');

    //CRUD  CONFIGURACION TICKETFREATURE
    Route::get('/ticketFeatures',[TicketFeatureController::class,'index'])->name('ticketFeatures.index');
    Route::get('/ticketFeaturess/create',[TicketFeatureController::class,'create'])->name('ticketFeatures.create');
    Route::post('/ticketFeatures/create',[TicketFeatureController::class,'store'])->name('ticketFeatures.store');
    Route::get('/ticketFeatures/update/{id}',[TicketFeatureController::class,'edit'])->name('ticketFeatures.edit');
    Route::post('/ticketFeatures/update/',[TicketFeatureController::class,'update'])->name('ticketFeatures.update');
    Route::get('/ticketFeatures/delete/{id}',[TicketFeatureController::class,'delete'])->name('ticketFeatures.delete');


    //CRUD PDF
    Route::get('/pdf/{id}',[PDFController::class,'buildPDF'])->name('pdf');
    Route::get('/checkout/',[PaypalController::class,'Paypal'])->name('checkout.paypal');



    //CRUD AJAX
    Route::get('input-form', [AjaxController::class, 'index']);
    Route::get('search-Autocomplete', [AjaxController::class, 'searchAutocomplete']);


    Route::get('input-form-Layout', [AjaxController::class, 'index']);
    Route::get('search-AutocompleteLayout', [AjaxController::class, 'searchAutocompleteLayout']);


    Route::prefix('auth-complete-search')->group(function(){
            Route::view('/','autocompletesearch.index');
            Route::get('search/{query}',[AjaxController::class,'index']);

    });

      //EDITAR PERFIL
    Route::get('/profile/update/{id}', [UserController::class, 'profileEdit'])->name('profile.edit');
    Route::post('/profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
    Route::get('/profile/changeProfilePhoto', [UserController::class, 'changeProfilePhoto'])->name('profile.changeProfilePhoto');
    Route::post('/profile/changeProfilePhoto', [UserController::class, 'changeProfilePhotoUpdate'])->name('profile.changeProfilePhotoUpdate');
    //REESTABLECER CONTRASEÑA
    Route::get('/profile/changePassword', [UserController::class, 'changePassword'])->name('profile.changePassword');
    Route::patch('/profile/changePassword', [UserController::class, 'changePasswordUpdate'])->name('profile.changePasswordUpdate');
    //CRUD EVENTOS
    Route::get('/event', [EventController::class, 'index'])->name('event.index');
    Route::get('/event/create', [EventController::class, 'create'])->name('event.create');
    Route::post('/event/create', [EventController::class, 'store'])->name('event.store');
    Route::get('/event/update/{id}', [EventController::class, 'edit'])->name('event.edit');
    Route::post('/event/update', [EventController::class, 'update'])->name('event.update');
    Route::post('/event/generatePublicLink/{id}', [EventController::class, 'generatePublicLink'])->name('event.generatePublicLink');
    Route::get('/events/{id}/set-registration-parameters', [EventController::class, 'setRegistrationParameters'])->name('events.setRegistrationParameters');
    Route::post('/events/{id}/store-registration-parameters', [EventController::class, 'storeRegistrationParameters'])->name('events.storeRegistrationParameters');
    Route::get('/event/findByDocument', [EventController::class, 'findByDocument'])->name('event.findByDocument');
    Route::post('/event/findByDocumentStore', [EventController::class, 'findByDocumentStore'])->name('event.findByDocumentStore');
    Route::get('/buscar-cedulas', [EventController::class, 'buscarCedulas'])->name('event.buscarCedulas');

    //ASISTENTS TO EVENT
    Route::get('/assistants/{idEvent}', [EventAssistantController::class, 'index'])->name('eventAssistant.index');
    Route::get('/assistants/{idEvent}/massAssign', [EventAssistantController::class, 'showMassAssign'])->name('eventAssistant.massAssign');
    Route::post('/assistants/{idEvent}/massAssign', [EventAssistantController::class, 'uploadMassAssign'])->name('eventAssistant.massAssign.upload');
    Route::get('/assistants/{idEvent}/singleAssignForm', [EventAssistantController::class, 'singleAssignForm'])->name('eventAssistant.singleAssignForm');
    Route::post('/assistants/{idEvent}/singleAssignForm', [EventAssistantController::class, 'uploadSingleAssign'])->name('eventAssistant.singleAssign.upload');
    Route::get('/assistants/{idEvent}/singleCreateForm', [EventAssistantController::class, 'singleCreateForm'])->name('eventAssistant.singleCreateForm');
    Route::post('/assistants/{idEvent}/singleCreate', [EventAssistantController::class, 'singleCreateUpload'])->name('eventAssistant.singleCreate.upload');
    Route::get('/assistants/update/{idEventAssistant}', [EventAssistantController::class, 'edit'])->name('eventAssistant.singleUpdateForm');
    Route::put('/assistants/update/{idEventAssistant}', [EventAssistantController::class, 'singleUpdateUpload'])->name('eventAssistant.update');
    Route::delete('/assistants/delete/{idEventAssistant}', [EventAssistantController::class, 'singleDelete'])->name('eventAssistant.singleDelete');
    Route::get('/event-assistant/{id}/qr', [EventAssistantController::class, 'showQr'])->name('eventAssistant.qr');
    Route::patch('/event-assistant/{id}/register-entry', [EventAssistantController::class, 'registerEntry'])->name('eventAssistant.registerEntry');
    Route::post('/event-assistant-2/{id}/register-entry', [EventAssistantController::class, 'registerEntry2'])->name('eventAssistant.registerEntry2');
    Route::patch('/event-assistant/{id}/reject-entry', [EventAssistantController::class, 'rejectEntry'])->name('eventAssistant.rejectEntry');
    Route::get('/event-assistant/{id}/pdf', [EventAssistantController::class, 'generatePDF'])->name('eventAssistant.pdf');
    Route::patch('event-assistants/{eventAssistant}/features/{feature}/consume', [EventAssistantController::class, 'consumeFeature'])->name('eventAssistant.consumeFeature');
    Route::get('events/{id}/download-template', [EventAssistantController::class, 'downloadTemplate'])->name('eventAssistant.downloadTemplate');
    Route::get('events/{idEvent}/specificSearch', [EventAssistantController::class, 'specificSearch'])->name('eventAssistant.specificSearch');
    Route::get('events/{idEvent}/specificSearchUploead', [EventAssistantController::class, 'specificSearchUploead'])->name('eventAssistant.specificSearch.upload');
    Route::get('/event-assistant/{idEvent}/export-excel', [EventAssistantController::class, 'exportExcel'])->name('eventAssistant.exportExcel');
    Route::get('/event-assistant/{idEvent}/send-msg', [EventAssistantController::class, 'sendMsg'])->name('eventAssistant.sendMsg');
    Route::get('/event-assistant/{id}/sendEmail', [EventAssistantController::class, 'sendEmail'])->name('eventAssistant.sendEmail');
    Route::get('/event-assistant/{id}/payment', [EventAssistantController::class, 'payment'])->name('eventAssistant.payment');
    Route::post('/event-assistant/payment', [EventAssistantController::class, 'paymentStore'])->name('eventAssistant.payment.store');
    Route::get('/event-assistant/{id}/sendEmailInfoPago', [EventAssistantController::class, 'sendEmailInfoPago'])->name('eventAssistant.sendEmailInfoPago');
    Route::get('/assistants/{idEvent}/massPayload', [EventAssistantController::class, 'showMassPayload'])->name('eventAssistant.massPayload');
    Route::get('/assistants/{idEvent}/courtesyCode', [EventAssistantController::class, 'courtesyCode'])->name('eventAssistant.courtesyCode');
    Route::get('/event-assistant/{id}/pdfTicket', [PDFController::class, 'getPDFEvento'])->name('eventAssistant.getPDFTicket');
    Route::post('/generate-coupons', [CouponController::class, 'createCoupons'])->name('generateCoupons');
    Route::get('/check-job-status/{eventId}', [CouponController::class, 'checkJobStatus']);
    Route::get('/job-progress/{eventId}', [CouponController::class, 'getJobProgress'])->name('job-progress');
    Route::get('/get-coupons/{eventId}', [EventAssistantController::class, 'getCoupons']);

    //Payloads
    Route::get('/paymentList/{idEvent}', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payment/{id}', [PaymentController::class, 'generatePDF'])->name('payments.generatePDF');
    Route::get('/event-assistant/payment/{idEvent}/export-excel', [PaymentController::class, 'exportExcel'])->name('payment.exportExcel');
    Route::get('/event-assistant/paymentStatus/{idEvent}/export-excel', [PaymentController::class, 'exportExcelPaymentStatus'])->name('paymentStatus.exportExcel');
    Route::get('payment/{id}/download-template', [PaymentController::class, 'downloadTemplate'])->name('payments.downloadTemplate');
    Route::post('/assistants/{idEvent}/massPayload', [PaymentController::class, 'uploadMassPayload'])->name('eventAssistant.massPayload.upload');

    //COUPON
    Route::get('/coupon/{idEvent}', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupon/{id}/pdf', [CouponController::class, 'generatePDF'])->name('coupon.pdf');
    Route::get('/api/coupons/count-available/{idEvent}', [CouponController::class, 'countAvailableCoupons']);
    // Route::get('/coupons/{idEvent}/pdf', [CouponController::class, 'generatePDFMasivo'])->name('coupons.pdf');
    Route::get('/generate-massive-pdf/{idEvent}', [CouponController::class, 'generatePDFMasivo'])->name('coupons.pdf');
    Route::get('/generate-pdf-job/{idEvent}', [CouponController::class, 'generateZipsPdfs']);
    Route::get('/generated-zips/{idEvent}', [CouponController::class, 'getGeneratedZips']);
    Route::get('/api/check-job-status/{idEvent}', [CouponController::class, 'checkJobStatusjob']);
    Route::get('/coupons/{idEvent}/xls', [CouponController::class, 'generateExcel'])->name('coupons.excel');
    Route::delete('/coupons/{id}', [CouponController::class, 'destroy'])->name('coupon.delete');

    //SELECTS


    //SEATS
    Route::get('ticket-types/{idEvent}/seats', [SeatController::class, 'index'])->name('seats.index');
    Route::post('seats/assign/{seat}', [SeatController::class, 'assignSeat'])->name('seats.assign');
    Route::post('seats/unassign/{seat}', [SeatController::class, 'unassignSeat'])->name('seats.unassign');
    Route::get('/seats/upload-form/{idEvent}', [SeatController::class, 'showUploadForm'])->name('seats.uploadForm');
    Route::post('/seats/upload/{idEvent}', [SeatController::class, 'uploadExcel'])->name('seats.upload');
    Route::get('/get-event-assistants/{ticketTypeId}', [SeatController::class, 'getEventAssistants']);
});
Route::get('/cities/{department}', [CityController::class, 'getCitiesByDepartment']);

Route::controller(PageController::class)->group(function () {
    Route::get('/dashboard', 'dashboardOverview1')->name('dashboard-overview-1');
    Route::get('dashboard-overview-2-page', 'dashboardOverview2')->name('dashboard-overview-2');
    Route::get('dashboard-overview-3-page', 'dashboardOverview3')->name('dashboard-overview-3');
    Route::get('dashboard-overview-4-page', 'dashboardOverview4')->name('dashboard-overview-4');
    Route::get('categories-page', 'categories')->name('categories');
    Route::get('add-product-page', 'addProduct')->name('add-product');
    Route::get('product-list-page', 'productList')->name('product-list');
    Route::get('product-grid-page', 'productGrid')->name('product-grid');
    Route::get('transaction-list-page', 'transactionList')->name('transaction-list');
    Route::get('transaction-detail-page', 'transactionDetail')->name('transaction-detail');
    Route::get('seller-list-page', 'sellerList')->name('seller-list');
    Route::get('seller-detail-page', 'sellerDetail')->name('seller-detail');
    Route::get('reviews-page', 'reviews')->name('reviews');
    Route::get('inbox-page', 'inbox')->name('inbox');
    Route::get('file-manager-page', 'fileManager')->name('file-manager');
    Route::get('point-of-sale-page', 'pointOfSale')->name('point-of-sale');
    Route::get('chat-page', 'chat')->name('chat');
    Route::get('post-page', 'post')->name('post');
    Route::get('calendar-page', 'calendar')->name('calendar');
    Route::get('crud-data-list-page', 'crudDataList')->name('crud-data-list');
    Route::get('crud-form-page', 'crudForm')->name('crud-form');
    Route::get('users-layout-1-page', 'usersLayout1')->name('users-layout-1');
    Route::get('users-layout-2-page', 'usersLayout2')->name('users-layout-2');
    Route::get('users-layout-3-page', 'usersLayout3')->name('users-layout-3');
    Route::get('profile-overview-1-page', 'profileOverview1')->name('profile-overview-1');
    Route::get('profile-overview-2-page', 'profileOverview2')->name('profile-overview-2');
    Route::get('profile-overview-3-page', 'profileOverview3')->name('profile-overview-3');
    Route::get('wizard-layout-1-page', 'wizardLayout1')->name('wizard-layout-1');
    Route::get('wizard-layout-2-page', 'wizardLayout2')->name('wizard-layout-2');
    Route::get('wizard-layout-3-page', 'wizardLayout3')->name('wizard-layout-3');
    Route::get('blog-layout-1-page', 'blogLayout1')->name('blog-layout-1');
    Route::get('blog-layout-2-page', 'blogLayout2')->name('blog-layout-2');
    Route::get('blog-layout-3-page', 'blogLayout3')->name('blog-layout-3');
    Route::get('pricing-layout-1-page', 'pricingLayout1')->name('pricing-layout-1');
    Route::get('pricing-layout-2-page', 'pricingLayout2')->name('pricing-layout-2');
    Route::get('invoice-layout-1-page', 'invoiceLayout1')->name('invoice-layout-1');
    Route::get('invoice-layout-2-page', 'invoiceLayout2')->name('invoice-layout-2');
    Route::get('faq-layout-1-page', 'faqLayout1')->name('faq-layout-1');
    Route::get('faq-layout-2-page', 'faqLayout2')->name('faq-layout-2');
    Route::get('faq-layout-3-page', 'faqLayout3')->name('faq-layout-3');
    Route::get('register-page', 'register')->name('register');
    Route::get('error-page-page', 'errorPage')->name('error-page');
    Route::get('update-profile-page', 'updateProfile')->name('update-profile');
    Route::get('change-password-page', 'changePassword')->name('change-password');
    Route::get('regular-table-page', 'regularTable')->name('regular-table');
    Route::get('tabulator-page', 'tabulator')->name('tabulator');
    Route::get('modal-page', 'modal')->name('modal');
    Route::get('slide-over-page', 'slideOver')->name('slide-over');
    Route::get('notification-page', 'notification')->name('notification');
    Route::get('tab-page', 'tab')->name('tab');
    Route::get('accordion-page', 'accordion')->name('accordion');
    Route::get('button-page', 'button')->name('button');
    Route::get('alert-page', 'alert')->name('alert');
    Route::get('progress-bar-page', 'progressBar')->name('progress-bar');
    Route::get('tooltip-page', 'tooltip')->name('tooltip');
    Route::get('dropdown-page', 'dropdown')->name('dropdown');
    Route::get('typography-page', 'typography')->name('typography');
    Route::get('icon-page', 'icon')->name('icon');
    Route::get('loading-icon-page', 'loadingIcon')->name('loading-icon');
    Route::get('regular-form-page', 'regularForm')->name('regular-form');
    Route::get('datepicker-page', 'datepicker')->name('datepicker');
    Route::get('tom-select-page', 'tomSelect')->name('tom-select');
    Route::get('file-upload-page', 'fileUpload')->name('file-upload');
    Route::get('wysiwyg-editor-classic-page', 'wysiwygEditorClassic')->name('wysiwyg-editor-classic');
    Route::get('wysiwyg-editor-inline-page', 'wysiwygEditorInline')->name('wysiwyg-editor-inline');
    Route::get('wysiwyg-editor-balloon-page', 'wysiwygEditorBalloon')->name('wysiwyg-editor-balloon');
    Route::get('wysiwyg-editor-balloon-block-page', 'wysiwygEditorBalloonBlock')->name('wysiwyg-editor-balloon-block');
    Route::get('wysiwyg-editor-document-page', 'wysiwygEditorDocument')->name('wysiwyg-editor-document');
    Route::get('validation-page', 'validation')->name('validation');
    Route::get('chart-page', 'chart')->name('chart');
    Route::get('slider-page', 'slider')->name('slider');
    Route::get('image-zoom-page', 'imageZoom')->name('image-zoom');
});

// webhook whatsapp
Route::any('app/chats/management/create/webhook', [createManagementController::class, 'handleWeBHook']);
