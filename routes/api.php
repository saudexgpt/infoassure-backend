<?php

use App\Http\Controllers\AnswersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClausesController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\ConsultingsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\FormFieldsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StandardsController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('set-admin-role', [Controller::class, 'setAdminRole']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('confirm-registration/{hash}', [AuthController::class, 'confirmRegistration']);
    Route::post('recover-password', [AuthController::class, 'recoverPassword']);
    Route::get('confirm-password-reset-token/{token}', [AuthController::class, 'confirmPasswordResetToken']);

    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::put('sent-2fa-code/{user}', [AuthController::class, 'send2FACode']);
    Route::put('confirm-2fa-code/{user}', [AuthController::class, 'confirm2FACode']);
    // Route::post('register', [AuthController::class, 'register'])->middleware('permission:create-users');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('user', [AuthController::class, 'user']); //->middleware('permission:read-users');
    });
});


//////////////////////////////// APP APIS //////////////////////////////////////////////
Route::group(['middleware' => 'auth:sanctum'], function () {


    Route::get('user-notifications', [UsersController::class, 'userNotifications']);
    Route::get('notification/mark-as-read', [UsersController::class, 'markNotificationAsRead']);

    Route::group(['prefix' => 'users'], function () {
        Route::get('fetch-staff', [UsersController::class, 'fetchStaff']);
        Route::post('register', [UsersController::class, 'store']);
        Route::put('update-profile/{user}', [UsersController::class, 'updateProfile']);
        Route::post('upload-photo', [UsersController::class, 'updatePhoto']);
    });

    Route::group(['prefix' => 'custom-fields'], function () {
        Route::get('/', [FormFieldsController::class, 'index']);
        Route::post('store', [FormFieldsController::class, 'store']);
        Route::put('update/{form_field}', [FormFieldsController::class, 'update']);
        Route::delete('destroy/{form_field}', [FormFieldsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'clients'], function () {
        Route::get('/', [ClientsController::class, 'index']);

        Route::post('register', [ClientsController::class, 'store']);
        Route::put('update/{client}', [ClientsController::class, 'update']);
        Route::put('send-login-credentials/{user}', [ClientsController::class, 'sendLoginCredentials']);
        Route::put('toggle-client-suspension/{client}', [ClientsController::class, 'toggleClientSuspension']);
    });

    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectsController::class, 'index']);
        Route::get('client-projects', [ProjectsController::class, 'clientProjects']);

        Route::get('show/{project}', [ProjectsController::class, 'show']);

        Route::post('save', [ProjectsController::class, 'store']);
        Route::put('set-dates/{project}', [ProjectsController::class, 'setDates']);
        Route::put('toggle-completion/{project}', [ProjectsController::class, 'toggleCompletion']);

        Route::delete('destroy/{project}', [ProjectsController::class, 'destroy']);
        Route::post('upload-certificate', [ProjectsController::class, 'uploadProjectCertificate']);
        Route::get('client-project-certificates', [ProjectsController::class, 'clientProjectCertificates']);
        Route::get('client-project-feedback', [ProjectsController::class, 'clientProjectFeedback']);
        Route::post('save-client-feedback', [ProjectsController::class, 'saveClientFeedback']);
    });
    Route::group(['prefix' => 'consultings'], function () {
        Route::get('/', [ConsultingsController::class, 'index']);
        Route::get('with-clauses', [ConsultingsController::class, 'fetchWithClauses']);

        Route::post('save', [ConsultingsController::class, 'store']);
        Route::put('update/{consulting}', [ConsultingsController::class, 'update']);
        Route::delete('destroy/{consulting}', [ConsultingsController::class, 'destroy']);
    });
    Route::group(['prefix' => 'standards'], function () {
        Route::get('/', [StandardsController::class, 'index']);
        Route::get('with-clauses', [StandardsController::class, 'fetchWithClauses']);

        Route::post('save', [StandardsController::class, 'store']);
        Route::put('update/{standard}', [StandardsController::class, 'update']);
        Route::delete('destroy/{standard}', [StandardsController::class, 'destroy']);
    });
    Route::group(['prefix' => 'clauses'], function () {
        Route::get('/', [ClausesController::class, 'index']);
        Route::get('fetch-clauses-with-questions', [ClausesController::class, 'fetchClausesWithQuestions']);

        Route::get('fetch-clauses-with-documents', [ClausesController::class, 'fetchClausesWithDocuments']);
        Route::post('save', [ClausesController::class, 'store']);
        Route::put('update/{clause}', [ClausesController::class, 'update']);
        Route::delete('destroy/{clause}', [ClausesController::class, 'destroy']);
        Route::post('uploads/save', [ClausesController::class, 'createUploads']);
        Route::post('upload-file', [ClausesController::class, 'uploadClauseFile']);
        Route::post('upload-document-template', [ClausesController::class, 'uploadDocumentTemplate']);
        Route::delete('destroy-template/{template}', [ClausesController::class, 'destroyTemplate']);
        Route::put('remark-on-upload/{upload}', [ClausesController::class, 'remarkOnUpload']);
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
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::get('client-data-analysis-dashboard', [ReportsController::class, 'clientDataAnalysisDashbord']);
        Route::get('client-project-data-analysis', [ReportsController::class, 'clientProjectDataAnalysis']);
        Route::get('admin-data-analysis-dashboard', [ReportsController::class, 'adminDataAnalysisDashbord']);
    });

    Route::group(['prefix' => 'exceptions'], function () {

        Route::get('fetch', [ClausesController::class, 'fetchExceptions']);
        Route::post('create', [ClausesController::class, 'createException']);
        Route::delete('reverse/{exception}', [ClausesController::class, 'reverseException']);
    });

    Route::group(['prefix' => 'evidence'], function () {

        Route::get('/', [EvidenceController::class, 'index']);
        Route::get('fetch-client-evidence', [EvidenceController::class, 'fetchClientEvidence']);

        Route::post('store', [EvidenceController::class, 'store']);
        Route::put('update/{evidence}', [EvidenceController::class, 'update']);
        Route::post('create-client-evidence', [EvidenceController::class, 'createClientEvidence']);
        Route::delete('destroy/{evidence}', [EvidenceController::class, 'destroy']);
        Route::delete('destroy-client-evidence/{client_evidence}', [EvidenceController::class, 'destroyClientEvidence']);
    });


    // Access Control Roles & Permission
    Route::group(['prefix' => 'acl'], function () {
        Route::get('roles/index', [RolesController::class, 'index']);
        Route::post('roles/save', [RolesController::class, 'store']);
        Route::put('roles/update/{role}', [RolesController::class, 'update']);
        Route::post('roles/assign', [RolesController::class, 'assignRoles']);


        Route::get('permissions/index', [PermissionsController::class, 'index']);
        Route::post('permissions/assign-user', [PermissionsController::class, 'assignUserPermissions']);
        Route::post('permissions/assign-role', [PermissionsController::class, 'assignRolePermissions']);
    });
});
