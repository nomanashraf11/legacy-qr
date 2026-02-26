<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\ResellerApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\CheckUserBanned;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/signin', [AuthController::class, 'loginUser']);
Route::post('/signup', [AuthController::class, 'register']);

/** Reseller application (for Shopify or other external forms) */
Route::post('/reseller-application', [ResellerApplicationController::class, 'store']);

//Google login
Route::post('/signin/google', [AuthController::class, "handleGoogleLogin"]);

Route::post('/forgot-password-api', [ResetPasswordController::class, 'sendOTPRequest']);
Route::post('/reset-password-api', [ResetPasswordController::class, 'resetPassword']);

Route::post('/email/resend-api', [VerificationController::class, 'sendOTPRequest']);
Route::post('/email/verify-api', [VerificationController::class, 'verify']);

Route::middleware('auth:sanctum', 'apiBanned')->group(function () {
    Route::post('{uuid}/add_bio', [ProfileController::class, 'addBio']);

    Route::post('{uuid}/add_photo', [ProfileController::class, 'addPhotos']);
    Route::post('edit/photo/{uuid}', [ProfileController::class, 'addCaption']);
    Route::post('delete_photo/{uuid}', [ProfileController::class, 'deletePhoto']);

    Route::post('upload-relation-photo', [ProfileController::class, 'uploadRelationPhoto']);
    Route::post('delete-relation-photo', [ProfileController::class, 'deleteRelationPhoto']);

    Route::post('{uuid}/add_timeline', [ProfileController::class, 'addTimeline']);
    Route::post('{uuid}/update_timeline', [ProfileController::class, 'updateTimeline']);
    Route::post('{uuid}/delete_timeline', [ProfileController::class, 'deleteTimeline']);

    Route::post('{uuid}/update_tribute', [ProfileController::class, 'editTribute']);
    Route::post('{uuid}/delete_tribute', [ProfileController::class, 'deleteTribute']);

    Route::get('my_qr_codes', [ProfileController::class, 'myQrCodes']);

    Route::get('profile_user', [ProfileController::class, 'myProfile']);
    Route::post('update_profile', [ProfileController::class, 'updateMyProfile']);
    Route::post('changePassword', [ProfileController::class, 'changePassword']);

    Route::post('remove-relation/{uuid}', [ProfileController::class, 'removeRelation']);
});

Route::post('{uuid}/add_tribute', [ProfileController::class, 'addTribute']);
Route::get('{uuid}', [ProfileController::class, 'scanCode']);
Route::post('link/{uuid}', [ProfileController::class, 'linkCode']);
Route::get('share/{uuid}', [ProfileController::class, 'shareProfile']);
