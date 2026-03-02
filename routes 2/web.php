<?php

use App\Exports\LinksExport;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Sellar\DashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\LandingPageController;



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

Route::get('/', [LandingPageController::class, 'landing'])->name('landing');

Route::get('/email/verify', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect('/home')->with('verified', true); // Redirect if already verified
    }
    auth()->user()->sendEmailVerificationNotification();
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');
Route::post('/email/verification-notification', [HomeController::class, 'resend'])
    ->middleware(['auth'])->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/home')->with('verified', true);
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [RedirectController::class, 'redirectAfterLogin'])->name('redirect');
});
Route::get('about', [LandingPageController::class, 'about'])->name('about');
Route::get('pricing', [LandingPageController::class, 'pricing'])->name('pricing');
Route::get('terms-and-conditions', [LandingPageController::class, 'terms'])->name('terms');
Route::get('contact', [LandingPageController::class, 'contact'])->name('contact');
Route::get('how-it-works', [LandingPageController::class, 'howItWorks'])->name('howWorks');
Route::post('send-contact', [LandingPageController::class, 'sendContactMail'])->name('sendContact');
Route::view('re-seller-page', 'landing.pages.reSeller')->name('reseller.view');
Route::post('create-re-seller-request', [UserManagementController::class, 're_seller_request'])->name('reSellerRequest');


Route::view('help-center', 'landing.pages.help')->name('help.center');
Route::view('privacy', 'landing.pages.privacy')->name('privacy');




Route::get('/home', [RedirectController::class, 'home'])->middleware('verified');

Route::get('gen_ce', [QrCodeController::class, 'generateQrCodeWithLogo']);


Route::middleware('auth', 'role:admin', 'verified', config('jetstream.auth_session'), 'auth:sanctum')->group(function () {
    // Route::get('gen_ce', [QrCodeController::class, 'generateQrCodeWithLogo']);
    Route::get('admin/dashboard', [BatchController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('batches', [BatchController::class, 'index'])->name('admin.batches');


    Route::get('download_qr_codes_by_batch/{uuid}', [BatchController::class, 'downloadQRCodes'])->name('batches.download.qrCode');
    Route::get('download_available_qr_codes_by_batch/{uuid}', [BatchController::class, 'downloadAvailableQRCodes'])->name('batches.available.download.qrCode');

    Route::get('/export/{links}', [QrCodeController::class, 'export'])->name('export');
    Route::get('/export-available/{links}', [QrCodeController::class, 'availableExport'])->name('available.export');


    Route::get('re_sellars', [UserManagementController::class, 'list'])->name('users.list');
    Route::get('local_users', [UserManagementController::class, 'localList'])->name('users.list.local');
    Route::post('ban_user/{id}', [UserManagementController::class, 'banUser'])->name('banUser');
    Route::post('delete-user/{id}', [UserManagementController::class, 'delete'])->name('delete.user');
    Route::post('create_re_sellars', [UserManagementController::class, 'create_reSeller'])->name('admin.create.reSeller');


    Route::post('generate_code', [QrCodeController::class, 'store'])->name('generateCode');
    Route::get('link_user/{uuid}', [QrCodeController::class, 'link_user'])->name('link_user');

    Route::get('available-qr-codes-batch', [QrCodeController::class, 'available'])->name('admin.batch.available');
    Route::get('available_qr-codes-batch-list', [QrCodeController::class, 'index'])->name('admin.links');
    Route::get('available-qr-codes/{uuid}', [QrCodeController::class, 'availableLinks'])->name('admin.available.links');
    Route::get('available-qr-codes-list/{uuid}', [QrCodeController::class, 'availableLinksList'])->name('admin.available.links.list');


    Route::get('downlaod-svg/{image}', [QrCodeController::class, 'downloadSvg'])->name('downloadSvg');
    Route::get('qr_codes_linked', [QrCodeController::class, 'linkedCodes'])->name('admin.links.linked');
    Route::get('view_qr_profile/{uuid}', [QrCodeController::class, 'show'])->name('admin.qr.view');
    Route::post('delete_profile/{uuid}', [QrCodeController::class, 'deleteProfile'])->name('admin.delete.profile');
    Route::get('qr_codes_by_user/{uuid}', [QrCodeController::class, 'linkByUser'])->name('link.by.user');
    Route::post('delete_photo_from_profile/{uuid}', [QrCodeController::class, 'deletePhotosFromProfile'])->name('deletePhotoFromProfile');
    Route::post('delete_tribute/{uuid}', [QrCodeController::class, 'deleteTribute'])->name('admin.delete.tribute');
    Route::view('transfer_qr_code', 'admin.pages.transfer')->name('admin.transfer.page');
    Route::post('transfer_qr_code_data', [QrCodeController::class, 'transferData'])->name('admin.transfer.data');

    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders');
    Route::post('mark_as_delivered/{uuid}', [OrderController::class, 'markAsDelivered'])->name('delivered');
    Route::get('get_tracking_details/{uuid}', [OrderController::class, 'getTrackingDetails'])->name('get.tracking.details');
    Route::post('update_tracking_details/{uuid}', [OrderController::class, 'updateTrackingDetails'])->name('update.tracking.details');
    Route::get('ordersByResellers/{uuid}', [OrderController::class, 'orderByResellers'])->name('order.resellars');

    Route::get('reviews', [ReviewController::class, 'list'])->name('admin.reviews');
    Route::post('store_review', [ReviewController::class, 'store'])->name('admin.review.store');
    Route::post('review/delete/{uuid}', [ReviewController::class, 'delete'])->name('admin.review.delete');
    Route::get('re-sellers-request', [ReviewController::class, 'contact_mails'])->name('admin.contact.mail');
    Route::get('inquiries', [ReviewController::class, 'inquries'])->name('admin.inquries.mail');
    Route::post('reply_mail', [ReviewController::class, 'reply'])->name('admin.mail.reply');

    Route::get('settings', [SettingController::class, 'setting'])->name('admin.settings');
    Route::post('update_details', [SettingController::class, 'updateDetails'])->name('update.details');
    Route::post('update_qr_data', [SettingController::class, 'changeQrData'])->name('updateQrData');
    Route::post('change-status-tawk', [SettingController::class, 'changeStatusOfTawkto'])->name('change.tawkto');
    Route::post('off_purchase', [SettingController::class, 'togglePurchase'])->name('togglePurchase');
});
Route::middleware(['auth', 'verified', 'role:admin|re-sellers', 'isBanned'])->group(function () {
    Route::get('order_details/{uuid}', [DashboardController::class, 'orderDetails'])->name('orderDetails');
    Route::post('update_password', [SettingController::class, 'changePassword'])->name('updatePassword');
});
Route::prefix('re_sellers')->middleware('auth', 'verified', 'role:re-sellers', 'isBanned')->group(function () {
    Route::get('settings', [SettingController::class, 'setting'])->name('settings');
    Route::post('update_details', [SettingController::class, 'sellerUpdateDetails']);

    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('sellar.dashboard');
        Route::get('buy_qr_codes_page', 'buy_qr_codes_page')->name('buyQrCodesPage');
        Route::get('my_orders', 'myOrders')->name('myOrders');
        Route::get('stripe', 'stripe')->name('stripe.index');
        Route::post('stripe/checkout', 'stripeCheckout')->name('stripe.checkout');
        Route::get('stripe/checkout/success', 'stripeCheckoutSuccess')->name('stripe.checkout.success');
    });
});


