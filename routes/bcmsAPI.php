<?php

use App\Http\Controllers\BCMS\BIAController;
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'bcms'], function () {
        Route::group(['prefix' => 'bia'], function () {

            Route::get('fetch-bia', [BIAController::class, 'fetchBIA']);
            Route::put('update-bia/{bia}', [BIAController::class, 'updateBIA']);

            Route::post('store', [BIAController::class, 'store']);
            Route::put('update-disruption-impact/{impact}', [BIAController::class, 'updateDisruptionImpact']);

            Route::get('fetch-risk-assessment', [BIAController::class, 'fetchRiskAssessments']);
            Route::post('store-risk-assessment', [BIAController::class, 'storeRiskAssessment']);
            Route::put('update-risk-assessment-field/{assessment}', [BIAController::class, 'updateRiskAssessmentFields']);
            Route::get('risk-assessment-summary', [BIAController::class, 'riskAssessmentSummary']);


        });
    });
});