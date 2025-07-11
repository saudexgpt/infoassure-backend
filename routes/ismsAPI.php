<?php

use App\Http\Controllers\ISMS\ComplianceController;
use App\Http\Controllers\ISMS\IncidentController;
use App\Http\Controllers\ISMS\IncidentTypeController;
use App\Http\Controllers\ISMS\ReportsController;
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'isms'], function () {

        Route::group(['prefix' => 'compliance'], function () {

            Route::post('save', [ComplianceController::class, 'store']);
            Route::post('create-compliance-monitor', [ComplianceController::class, 'createComplianceMonitor']);
            Route::put('update-compliance-response/{answer}', [ComplianceController::class, 'updateComplianceResponse']);
            // Route::delete('destroy/{answer}', [ComplianceController::class, 'destroy']);

            Route::put('submit-responses/{monitor}', [ComplianceController::class, 'submitResponses']);
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
        Route::group(['prefix' => 'incident-types'], function () {
            // Incident Types
            Route::get('/', [IncidentTypeController::class, 'index']);
            Route::post('store', [IncidentTypeController::class, 'store']);
            Route::get('show', [IncidentTypeController::class, 'show']);
            Route::put('update/{incidentType}', [IncidentTypeController::class, 'update']);
            Route::delete('destroy/{incidentType}', [IncidentTypeController::class, 'destroy']);
        });
        Route::group(['prefix' => 'incidents'], function () {
            // Incident Types
            Route::get('/', [IncidentController::class, 'index']);
            Route::post('store', [IncidentController::class, 'store']);
            Route::get('show/{incident}', [IncidentController::class, 'show']);
            Route::put('update/{incident}', [IncidentController::class, 'update']);
            Route::put('update-fields/{incident}', [IncidentController::class, 'updateFields']);
            Route::put('close-incident/{incident}', [IncidentController::class, 'closeIncident']);

            Route::delete('destroy/{incident}', [IncidentController::class, 'destroy']);

            Route::get('fetch-resolution-actions/{incident}', [IncidentController::class, 'fetchResolutionActions']);
            Route::post('store-resolution-action', [IncidentController::class, 'storeResolutionAction']);

            Route::post('store-incident-task/{incident}', [IncidentController::class, 'storeIncidentTask']);
            Route::put('assign-user/{incident}', [IncidentController::class, 'assignUser']);

            Route::get('fetch-tasks', [IncidentController::class, 'fetchTasks']);
            Route::post('store-tasks', [IncidentController::class, 'storeTask']);
            Route::get('show-task/{task}', [IncidentController::class, 'showTask']);
            Route::put('assign-user-to-task/{task}', [IncidentController::class, 'assignUserToTask']);
            Route::put('update-task-fields/{task}', [IncidentController::class, 'updateTaskFields']);
            Route::post('upload-task-evidence', [IncidentController::class, 'uploadTaskEvidence']);


            Route::get('fetch-rca', [IncidentController::class, 'fetchRCA']);
            Route::post('store-rca', [IncidentController::class, 'storeRCA']);

        });



        // Route::post('incidents/{incident}/comments', [CommentController::class, 'storeForIncident']);
        // Route::post('incidents/{incident}/attachments', [AttachmentController::class, 'storeForIncident']);
        // Route::get('incidents/{incident}/activity', [ActivityLogController::class, 'indexForIncident']);

        // // Cases
        // Route::apiResource('cases', CaseController::class);
        // Route::post('cases/{case}/comments', [CommentController::class, 'storeForCase']);
        // Route::post('cases/{case}/attachments', [AttachmentController::class, 'storeForCase']);
        // Route::get('cases/{case}/activity', [ActivityLogController::class, 'indexForCase']);

        // // Comments
        // Route::put('comments/{comment}', [CommentController::class, 'update']);
        // Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        // // Attachments
        // Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);
        // Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

        // Dashboard Statistics
        Route::get('/dashboard/stats', 'App\Http\Controllers\API\DashboardController@getStats');
    });


});