<?php

use App\Http\Controllers\NDPA\ClausesController;
use App\Http\Controllers\NDPA\QuestionsController;
use App\Http\Controllers\NDPA\AnswersController;
use App\Http\Controllers\NDPA\ReportsController;
use App\Http\Controllers\VendorDueDiligence\AuthController;
use App\Http\Controllers\VendorDueDiligence\VendorsController;

Route::group(['prefix' => 'vdd/auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    // Route::get('confirm-registration', [AuthController::class, 'confirmRegistration']);
    // Route::post('recover-password', [AuthController::class, 'recoverPassword']);
    // Route::get('confirm-password-reset-token/{token}', [AuthController::class, 'confirmPasswordResetToken']);

    // Route::post('reset-password', [AuthController::class, 'resetPassword']);
    // Route::post('other-user-login', [AuthController::class, 'otherUserLogin']);
    // Route::put('sent-2fa-code/{user}', [AuthController::class, 'send2FACode']);
    // Route::put('confirm-2fa-code/{user}', [AuthController::class, 'confirm2FACode']);

    // Route::post('register', [AuthController::class, 'register'])->middleware('permission:create-users');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        // Route::post('logout', [AuthController::class, 'logout']);
        // Route::post('login-as', [AuthController::class, 'loginAs']);
        Route::get('user', [AuthController::class, 'fetchUser']); //->middleware('permission:read-users');
    });
});

Route::group(['middleware' => 'vendor'], function () {
    Route::group(['prefix' => 'vdd'], function () {
        Route::get('show-vendor/{vendor}', [VendorsController::class, 'showVendor']);
        Route::post('update-vendor', [VendorsController::class, 'updateVendor']);
        Route::delete('delete-uploaded-document/{document}', [VendorsController::class, 'deleteUploadedDocument']);



    });
});