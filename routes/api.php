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
use App\Http\Controllers\PartnersController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProjectPlanController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RiskAssessmentsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SOAController;
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
// Route::get('clause-report', [ReportsController::class, 'clientProjectManagementClauseReport']);
// Route::get('completion-report', [ReportsController::class, 'clientProjectRequirementCompletionReport']);
// Route::get('summary-report', [ReportsController::class, 'clientProjectAssessmentSummaryReport']);
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('confirm-registration', [AuthController::class, 'confirmRegistration']);
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
        Route::post('register-client-user', [ClientsController::class, 'registerClientUser']);

        Route::put('update/{client}', [ClientsController::class, 'update']);
        Route::put('update-client-user/{user}', [ClientsController::class, 'updateClientUser']);
        Route::delete('delete-client-user/{user}', [ClientsController::class, 'deleteClientUser']);

        Route::put('send-login-credentials/{user}', [ClientsController::class, 'sendLoginCredentials']);
        Route::put('toggle-client-suspension/{client}', [ClientsController::class, 'toggleClientSuspension']);
    });
    Route::group(['prefix' => 'partners'], function () {
        Route::get('/', [PartnersController::class, 'index']);

        Route::post('register', [PartnersController::class, 'store']);
        Route::post('register-partner-user', [PartnersController::class, 'registerPartnerUser']);

        Route::put('update/{partner}', [PartnersController::class, 'update']);
        Route::put('update-partner-user/{user}', [PartnersController::class, 'updatePartnerUser']);
        Route::delete('delete-partner-user/{user}', [PartnersController::class, 'deletePartnerUser']);

        Route::put('send-login-credentials/{user}', [PartnersController::class, 'sendLoginCredentials']);
        Route::put('toggle-partner-suspension/{partner}', [PartnersController::class, 'togglePartnerSuspension']);
    });

    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectsController::class, 'index']);
        Route::get('client-projects', [ProjectsController::class, 'clientProjects']);

        Route::get('show/{project}', [ProjectsController::class, 'show']);

        Route::post('save', [ProjectsController::class, 'store']);
        Route::put('assign-to-user/{project}', [ProjectsController::class, 'assignProjectToClientStaff']);

        Route::put('set-dates/{project}', [ProjectsController::class, 'setDates']);
        Route::put('update-random-fields/{project}', [ProjectsController::class, 'updateRandomFields']);

        Route::delete('destroy/{project}', [ProjectsController::class, 'destroy']);
        Route::post('upload-certificate', [ProjectsController::class, 'uploadProjectCertificate']);
        Route::get('client-project-certificates', [ProjectsController::class, 'clientProjectCertificates']);
        Route::get('client-project-feedback', [ProjectsController::class, 'clientProjectFeedback']);
        Route::post('save-client-feedback', [ProjectsController::class, 'saveClientFeedback']);

        Route::post('assign-projects-to-consultant', [ProjectsController::class, 'assignProjectsToConsultant']);
        Route::post('unassign-project-from-consultant', [ProjectsController::class, 'unassignProjectFromConsultant']);
    });
    Route::group(['prefix' => 'project-plans'], function () {
        Route::get('/fetch-project-phases', [ProjectPlanController::class, 'fetchProjectPhases']);
        Route::get('/fetch-client-project-plan', [ProjectPlanController::class, 'fetchClientProjectPlan']);

        Route::post('/store-project-phases', [ProjectPlanController::class, 'storeProjectPhases']);
        Route::put('/update-project-phases/{project_phase}', [ProjectPlanController::class, 'updateProjectPhases']);
        Route::delete('/destroy-project-phases/{project_phase}', [ProjectPlanController::class, 'destroyProjectPhases']);

        Route::get('/fetch-gen-project-plans', [ProjectPlanController::class, 'fetchGeneralProjectPlan']);
        Route::post('/store-gen-project-plans', [ProjectPlanController::class, 'storeGeneralProjectPlans']);
        Route::put('/update-project-plan/{project_plan}', [ProjectPlanController::class, 'updateProjectPlan']);
        Route::delete('/destroy-project-plans/{project_plan}', [ProjectPlanController::class, 'destroyProjectPlan']);
        Route::put('/unlink-standard-from-project-plan/{project_plan}', [ProjectPlanController::class, 'unlinkStandardFromProjectPlan']);
        Route::put('/link-standards-to-project-plan/{project_plan}', [ProjectPlanController::class, 'linkStandardtoProjectPlan']);


        Route::post('/store-client-project-plan', [ProjectPlanController::class, 'storeClientProjectPlan']);
        Route::put('/update-client-project-plan-fields/{client_project_plan}', [ProjectPlanController::class, 'updateClientProjectPlanFields']);
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
        Route::put('set-sort-value/{clause}', [ClausesController::class, 'setSortValue']);
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
        Route::post('upload-gap-assessment-evidence', [AnswersController::class, 'uploadGapAssessmentEvidence']);
        Route::delete('destroy-gap-assessment-evidence/{gap_assessment_evidence}', [AnswersController::class, 'destroyGapAssessmentEvidenceEvidence']);
    });

    Route::group(['prefix' => 'reports'], function () {
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

    ///////////////////////////////////RISK ASSESSMENT////////////////////////////////////////////////
    Route::group(['prefix' => 'risk-assessment'], function () {
        Route::get('fetch-asset-types', [RiskAssessmentsController::class, 'fetchAssetTypes']);
        Route::get('fetch-impacts', [RiskAssessmentsController::class, 'fetchImpacts']);
        Route::get('fetch-categories', [RiskAssessmentsController::class, 'fetchCategories']);
        Route::get('fetch-likelihoods', [RiskAssessmentsController::class, 'fetchLikelihoods']);

        Route::post('save-impacts', [RiskAssessmentsController::class, 'saveImpacts']);
        Route::post('save-asset-types', [RiskAssessmentsController::class, 'saveAssetTypes']);
        Route::post('save-categories', [RiskAssessmentsController::class, 'saveCategories']);
        Route::post('save-likelihoods', [RiskAssessmentsController::class, 'saveLikelihoods']);

        Route::delete('delete-impact/{value}', [RiskAssessmentsController::class, 'deleteImpact']);
        Route::delete('delete-asset-type/{value}', [RiskAssessmentsController::class, 'deleteAssetType']);
        Route::delete('delete-category{value}', [RiskAssessmentsController::class, 'deleteCategory']);
        Route::delete('delete-likelihood/{value}', [RiskAssessmentsController::class, 'deleteLikelihood']);


        Route::get('fetch-risk_assessments', [RiskAssessmentsController::class, 'fetchRiskAssessments']);
        Route::post('store-risk-assessment', [RiskAssessmentsController::class, 'store']);

        Route::put('update-fields/{riskAssessment}', [RiskAssessmentsController::class, 'updateFields']);
    });
    ///////////////////////////////////RISK ASSESSMENT////////////////////////////////////////////////

    ///////////////////////////////////STATEMENT OF AVAILABILITY//////////////////////////////////////
    Route::group(['prefix' => 'soa'], function () {
        Route::get('fetch-areas', [SOAController::class, 'fetchAreas']);
        Route::get('fetch-controls', [SOAController::class, 'fetchControls']);

        Route::post('save-areas', [SOAController::class, 'saveAreas']);
        Route::post('save-controls', [SOAController::class, 'saveControl']);

        Route::delete('delete-area/{value}', [SOAController::class, 'deleteArea']);
        Route::delete('delete-control{value}', [SOAController::class, 'deleteControl']);


        Route::get('fetch-soa', [SOAController::class, 'fetchSOA']);
        Route::put('update-soa/{soa}', [SOAController::class, 'update']);

        Route::put('update-fields/{riskAssessment}', [SOAController::class, 'updateFields']);
    });
    ///////////////////////////////////STATEMENT OF AVAILABILITY////////////////////////////////////////////////

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
