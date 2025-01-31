<?php

use App\Http\Controllers\NDPA\ClausesController;
use App\Http\Controllers\NDPA\QuestionsController;
use App\Http\Controllers\NDPA\AnswersController;
use App\Http\Controllers\NDPA\ReportsController;
use App\Http\Controllers\VendorDueDiligence\AuthController;

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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'vdd'], function () {
        Route::group(['prefix' => 'clauses'], function () {
            Route::get('/', [ClausesController::class, 'index']);
            Route::get('fetch-clauses-with-questions', [ClausesController::class, 'fetchClausesWithQuestions']);

            Route::get('fetch-clauses-with-documents', [ClausesController::class, 'fetchClausesWithDocuments']);
            Route::post('save', [ClausesController::class, 'store']);

            Route::put('update/{clause}', [ClausesController::class, 'update']);
            Route::put('set-sort-value/{clause}', [ClausesController::class, 'setSortValue']);
            Route::delete('destroy/{clause}', [ClausesController::class, 'destroy']);

            // Route::post('uploads/save', [ClausesController::class, 'createUploads']);
            // Route::post('upload-file', [ClausesController::class, 'uploadClauseFile']);
            // Route::post('upload-document-template', [ClausesController::class, 'uploadDocumentTemplate']);
            // Route::delete('destroy-template/{template}', [ClausesController::class, 'destroyTemplate']);
            // Route::put('remark-on-upload/{upload}', [ClausesController::class, 'remarkOnUpload']);

            Route::get('fetch-clauses-sections', [ClausesController::class, 'viewClauseSections']);
            Route::post('save-section', [ClausesController::class, 'saveSection']);
            Route::put('update-section/{section}', [ClausesController::class, 'updateSection']);
            Route::delete('destroy-section/{section}', [ClausesController::class, 'destroySection']);
        });
        Route::group(['prefix' => 'questions'], function () {
            Route::get('/', [QuestionsController::class, 'index']);
            Route::post('save', [QuestionsController::class, 'store']);
            Route::post('upload-bulk', [QuestionsController::class, 'uploadBulk']);

            Route::put('update/{question}', [QuestionsController::class, 'update']);
            Route::delete('destroy/{question}', [QuestionsController::class, 'destroy']);
        });
        Route::group(['prefix' => 'answers'], function () {
            Route::get('/', [AnswersController::class, 'index']);
            Route::post('save', [AnswersController::class, 'store']);
            Route::put('update/{answer}', [AnswersController::class, 'update']);
            Route::delete('destroy/{answer}', [AnswersController::class, 'destroy']);

            Route::post('submit', [AnswersController::class, 'submitAnswers']);
            Route::put('remark-on-answer/{answer}', [AnswersController::class, 'remarkOnAnswer']);
            Route::post('assign-user-to-respond', [AnswersController::class, 'assignUserToRespond']);

            // Route::post('upload-gap-assessment-evidence', [AnswersController::class, 'uploadGapAssessmentEvidence']);
            // Route::delete('destroy-gap-assessment-evidence/{gap_assessment_evidence}', [AnswersController::class, 'destroyGapAssessmentEvidenceEvidence']);
        });

        Route::group(['prefix' => 'reports'], function () {
            Route::get('client-dashboard-statistics', [ReportsController::class, 'clientDashboardStatistics']);
            Route::get('client-data-analysis-dashboard', [ReportsController::class, 'clientDataAnalysisDashbord']);
            Route::get('client-project-data-analysis', [ReportsController::class, 'clientProjectDataAnalysis']);
            Route::get('admin-data-analysis-dashboard', [ReportsController::class, 'adminDataAnalysisDashbord']);
            Route::get('partner-data-analysis-dashboard', [ReportsController::class, 'partnerDataAnalysisDashbord']);

            Route::get('clause-report', [ReportsController::class, 'clientProjectManagementClauseReport']);
            Route::get('completion-report', [ReportsController::class, 'clientProjectRequirementCompletionReport']);
            Route::get('summary-report', [ReportsController::class, 'clientProjectAssessmentSummaryReport']);
            Route::get('soa-summary', [ReportsController::class, 'soaSummary']);
            Route::get('risk-assessment-summary', [ReportsController::class, 'riskAssessmentSummary']);
            Route::get('fetch-project-answers', [ReportsController::class, 'fetchProjectAnswers']);
            Route::get('asset-risk-analysis', [ReportsController::class, 'assetRiskAnalysis']);
            Route::get('process-risk-analysis', [ReportsController::class, 'processRiskAnalysis']);
            Route::get('bia-data-analysis', [ReportsController::class, 'dataAnalysisBIA']);



        });

        Route::group(['prefix' => 'exceptions'], function () {

            Route::get('fetch', [ClausesController::class, 'fetchExceptions']);
            Route::post('create', [ClausesController::class, 'createException']);
            Route::delete('reverse/{exception}', [ClausesController::class, 'reverseException']);
        });
    });
});