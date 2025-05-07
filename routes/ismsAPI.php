<?php

use App\Http\Controllers\ISMS\ComplianceController;
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
    });
});