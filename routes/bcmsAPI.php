<?php

use App\Http\Controllers\BCMS\BIAController;
use App\Http\Controllers\BCMS\CalendarController;
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

        Route::group(['prefix' => 'calendar'], function () {
            // Incident Types

            Route::get('fetch-all-tasks', [CalendarController::class, 'fetchAllTasks']);
            Route::get('show-task/{task}', [CalendarController::class, 'showTask']);

            Route::get('fetch-task-by-clause', [CalendarController::class, 'fetchModuleTaskByClause']);
            Route::get('fetch-client-assigned-tasks', [CalendarController::class, 'fetchClientAssignedTasks']);

            Route::post('store-clause-activities', [CalendarController::class, 'storeClauseActivities']);
            Route::put('update-clause-activity/{moduleActivity}', [CalendarController::class, 'updateClauseActivity']);
            Route::post('store-clause-activity-tasks', [CalendarController::class, 'storeClauseActivityTasks']);
            Route::put('update-clause-activity-task/{moduleActivityTask}', [CalendarController::class, 'updateClauseActivityTask']);
            Route::post('assign-task-to-user', [CalendarController::class, 'assignTaskToUser']);
            Route::get('fetch-my-calendar-data', [CalendarController::class, 'fetchMyCalendarData']);
            Route::get('fetch-project-calendar-data', [CalendarController::class, 'fetchProjectCalendarData']);

            Route::put('mark-task-as-done/{task}', [CalendarController::class, 'markTaskAsDone']);
            Route::put('mark-task-as-completed/{task}', [CalendarController::class, 'markTaskAsCompleted']);

            Route::get('set-expected-uploads', [CalendarController::class, 'setExpectedUploadsFromAssignedTasks']);
            Route::put('save-assigned-task-note/{task}', [CalendarController::class, 'saveAssignedTaskNote']);


            Route::group(['prefix' => 'comments'], function () {
                // Incident Types

                Route::get('fetch-task-comments', [CalendarController::class, 'fetchTaskComments']);
                Route::post('post-task-comment', [CalendarController::class, 'postTaskcomment']);
                Route::put('update-task-comment/{comment}', [CalendarController::class, 'updateTaskComment']);
                Route::delete('delete-comment/{comment}', [CalendarController::class, 'deleteComment']);


            });
        });
    });
});