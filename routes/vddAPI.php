<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\VendorDueDiligence\AppMailingsController;
use App\Http\Controllers\VendorDueDiligence\AuthController;
use App\Http\Controllers\VendorDueDiligence\ContractsAndSLAController;
use App\Http\Controllers\VendorDueDiligence\DueDiligenceQuestionsController;
use App\Http\Controllers\VendorDueDiligence\DueDiligenceReportsController;
use App\Http\Controllers\VendorDueDiligence\DueDiligenceResponsesController;
use App\Http\Controllers\VendorDueDiligence\InvoicesController;
use App\Http\Controllers\VendorDueDiligence\ReportsController;
use App\Http\Controllers\VendorDueDiligence\VendorsController;

Route::group(['prefix' => 'vdd/auth'], function () {
    Route::post('login', [AuthController::class, 'login']);

});



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'vdd'], function () {



        Route::get('vendor-invoices', [InvoicesController::class, 'index']);
        Route::put('invoices/make-payment/{invoice}', [InvoicesController::class, 'makePayment']);
        Route::post('invoices/upload-payment-evidence', [InvoicesController::class, 'uploadPaymentEvidence']);
        Route::put('approve-invoice/{invoice}', [InvoicesController::class, 'approvalAction']);

        Route::get('fetch-vendors', [VendorsController::class, 'index']);
        Route::get('fetch-vendor-categories', [VendorsController::class, 'fetchVendorCategories']);

        Route::get('fetch-approved-vendors', [VendorsController::class, 'fetchApprovedVendors']);

        Route::post('register-vendor', [VendorsController::class, 'store']);
        Route::post('register-vendor-user', [VendorsController::class, 'registerVendorUser']);
        Route::put('update-vendor-user/{user}', [VendorsController::class, 'updateVendorUser']);
        Route::put('send-login-credentials/{user}', [VendorsController::class, 'sendLoginCredentials']);
        Route::put('approve-vendor/{vendor}', [VendorsController::class, 'approvalAction']);
        Route::put('categorize-vendor/{vendor}', [VendorsController::class, 'categorizeVendor']);


        Route::group(['prefix' => 'questions'], function () {
            Route::get('/', [DueDiligenceQuestionsController::class, 'index']);
            Route::get('fetch-default-questions', [DueDiligenceQuestionsController::class, 'fetchDefaultQuestions']);
            Route::post('upload-default-questions', [DueDiligenceQuestionsController::class, 'uploadBulkDefaultQuestions']);
            Route::post('save-default-question', [DueDiligenceQuestionsController::class, 'saveDefaultQuestion']);
            Route::put('update-default-question/{question}', [DueDiligenceQuestionsController::class, 'updateDefaultQuestion']);
            Route::delete('destroy-default-question/{question}', [DueDiligenceQuestionsController::class, 'destroyDefaultQuestion']);


            Route::post('save-imported-questions', [DueDiligenceQuestionsController::class, 'saveImportedQuestions']);

            Route::post('save', [DueDiligenceQuestionsController::class, 'saveQuestions']);
            Route::put('update/{question}', [DueDiligenceQuestionsController::class, 'update']);
            Route::delete('destroy/{question}', [DueDiligenceQuestionsController::class, 'destroy']);

        });
        Route::group(['prefix' => 'responses'], function () {
            Route::get('fetch', [DueDiligenceResponsesController::class, 'fetchResponses']);

            Route::post('store', [DueDiligenceResponsesController::class, 'store']);
            Route::put('update/{answer}', [DueDiligenceResponsesController::class, 'update']);

            Route::post('enable-modification', [DueDiligenceResponsesController::class, 'enableModification']);
            Route::post('change-status', [DueDiligenceResponsesController::class, 'changeStatus']);

        });
        Route::group(['prefix' => 'reports'], function () {
            Route::get('fetch', [DueDiligenceReportsController::class, 'index']);
            Route::get('vendor-onboarding-count', [ReportsController::class, 'vendorOnboardingCount']);
            Route::get('vendor-onboarding-report', [ReportsController::class, 'vendorOnboardingReport']);
            Route::get('vendor-invoices-analysis', [ReportsController::class, 'vendorInvoicesAnalysis']);


        });

        Route::group(['prefix' => 'client-contracts'], function () {

            Route::get('fetch', [ContractsAndSLAController::class, 'fetchContracts']);
            Route::post('save-performance-score', [ContractsAndSLAController::class, 'saveVendorPerformanceScore']);
            Route::post('upload-contract', [ContractsAndSLAController::class, 'uploadContract']);
            Route::post('save-sla', [ContractsAndSLAController::class, 'saveSLAConfig']);

        });

    });

});
Route::group(['middleware' => 'vendor'], function () {
    Route::group(['prefix' => 'vdd'], function () {
        Route::get('search-email-list', [Controller::class, 'searchEmailList']);
        Route::get('show-vendor/{vendor}', [VendorsController::class, 'showVendor']);
        Route::post('update-vendor', [VendorsController::class, 'updateVendor']);
        Route::delete('delete-uploaded-document/{document}', [VendorsController::class, 'deleteUploadedDocument']);

        Route::group(['prefix' => 'answers'], function () {
            Route::get('fetch', [DueDiligenceResponsesController::class, 'fetchResponses']);
            Route::put('update/{answer}', [DueDiligenceResponsesController::class, 'update']);
            // Route::delete('destroy/{answer}', [DueDiligenceResponsesController::class, 'destroy']);

            Route::post('submit', [DueDiligenceResponsesController::class, 'submitDueDiligenceResponses']);

            Route::post('upload-due-diligence-evidence', [DueDiligenceResponsesController::class, 'uploadDueDiligenceEvidence']);
            Route::delete('destroy-evidence/{evidence}', [DueDiligenceResponsesController::class, 'destroyDueDiligenceEvidence']);
        });

        Route::group(['prefix' => 'messages'], function () {

            Route::get('/', [AppMailingsController::class, 'inbox']);
            Route::get('/inbox', [AppMailingsController::class, 'inbox']);
            Route::get('/sent', [AppMailingsController::class, 'sent']);
            Route::post('send-message', [AppMailingsController::class, 'compose']);
            Route::delete('delete/{message}', [AppMailingsController::class, 'delete']);
            Route::put('reply/{message}', [AppMailingsController::class, 'reply']);
            Route::get('/details/{message}', [AppMailingsController::class, 'messageDetails']);
        });

        Route::group(['prefix' => 'invoices'], function () {

            Route::get('/', [InvoicesController::class, 'index']);
            Route::post('store', [InvoicesController::class, 'store']);
            Route::post('upload-invoice', [InvoicesController::class, 'uploadInvoice']);

            Route::put('update/{invoice}', [InvoicesController::class, 'update']);
            Route::delete('destroy/{invoice}', [InvoicesController::class, 'destroy']);
            Route::delete('destroy-invoice-item/{invoice_item}', [InvoicesController::class, 'destroyInvoiceItem']);

            Route::put('confirm-payment/{invoice}', [InvoicesController::class, 'confirmPayment']);

        });
        Route::group(['prefix' => 'vendor-reports'], function () {
            Route::get('vendor-invoices-analysis', [ReportsController::class, 'vendorInvoicesAnalysis']);


        });
        Route::group(['prefix' => 'vendor-contracts'], function () {

            Route::get('fetch', [ContractsAndSLAController::class, 'fetchContracts']);
            Route::post('upload', [ContractsAndSLAController::class, 'uploadContract']);
            Route::post('save-sla', [ContractsAndSLAController::class, 'saveSLAConfig']);


        });

    });
});