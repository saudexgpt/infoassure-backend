<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Upload;
use App\Models\Exception;
use App\Models\Project;
use App\Models\RiskAssessment;
use App\Models\SOAArea;
use App\Models\Standard;
use App\Models\StatementOfApplicability;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function clientDataAnalysisDashbord(Request $request)
    {
        // $year = date('Y', strtotime('now'));
        $client = $this->getClient();
        $my_projects = $this->getMyProjects();
        // $uploaded_documents = Upload::where(['client_id' => $client->id, 'is_exception' => 0])->where('link', '!=', NULL)->count();
        // $expected_documents = Upload::where(['client_id' => $client->id])->count();
        // $answered_questions = Answer::where(['client_id' => $client->id, 'is_exception' => 0])->where('is_submitted', 1)->count();
        // $all_questions = Answer::where(['client_id' => $client->id])->count();
        // $exceptions = Exception::where('client_id', $client->id)->count();
        $all_projects_count = $my_projects->count();
        $completed_projects = $my_projects->where('is_completed', 1)
            // ->where('created_at', 'LIKE', '%' . $year . '%')
            ->count();

        $in_progress = $all_projects_count - $completed_projects;
        $all_projects = $my_projects;
        // foreach ($all_projects as $project) {
        //     $project->watchProjectProgress($project);
        // }
        return response()->json(compact('all_projects', 'all_projects_count', 'completed_projects', 'in_progress'), 200);
    }

    public function clientProjectDataAnalysis(Request $request)
    {
        $client_id = $this->getClient()->id;
        $project_id = $request->project_id;
        if ($project_id === 'all') {
            $projectIds = $this->getMyProjects()->pluck('id');
            $condition = ['client_id' => $client_id];
            $uploaded_documents = Upload::where($condition)->whereIn('project_id', $projectIds)->where('is_exception', 0)->where('link', '!=', NULL)->count();

            $expected_documents = Upload::where($condition)->whereIn('project_id', $projectIds)->count();
            $answered_questions = Answer::where($condition)
                ->whereIn('project_id', $projectIds)
                ->where('is_exception', 0)
                ->where('status', 'Closed')
                ->count();
            $all_questions = Answer::where($condition)->whereIn('project_id', $projectIds)->count();
            $exceptions = Exception::where($condition)->whereIn('project_id', $projectIds)->count();

            $my_projects = $this->getUser()->projects()->groupBy('client_id')->select(\DB::raw('AVG(progress) as project_progress'))->first();
            $project_progress = $my_projects->project_progress;
        } else {
            $project_progress = Project::find($project_id)->progress;
            $condition = ['project_id' => $project_id];
            $uploaded_documents = Upload::where($condition)->where('is_exception', 0)->where('link', '!=', NULL)->count();

            $expected_documents = Upload::where($condition)->count();
            $answered_questions = Answer::where($condition)
                ->where('is_exception', 0)
                ->where('status', 'Closed')
                ->count();
            $all_questions = Answer::where($condition)->count();
            $exceptions = Exception::where($condition)->count();
        }


        return response()->json(compact('uploaded_documents', 'expected_documents', 'answered_questions', 'all_questions', 'exceptions', 'project_progress'), 200);
    }

    public function clientProjectAssessmentSummaryReport(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        $reports = Answer::where(['client_id' => $client_id, 'project_id' => $project_id])
            ->where('is_submitted', 1)
            ->select(\DB::raw('COUNT(CASE WHEN yes_or_no = "YES" THEN answers.id END ) as conformity'), \DB::raw('COUNT(CASE WHEN yes_or_no = "NO" THEN answers.id END ) as non_conformity'), \DB::raw('COUNT(CASE WHEN is_exception = 1 OR yes_or_no IS NULL THEN answers.id END ) as not_applicable'))
            ->first();
        $project = Project::with('standard')->find($project_id);
        $subtitle = $project->standard->name;
        return response()->json(compact('reports', 'subtitle'), 200);
    }
    public function clientProjectManagementClauseReport(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        $reports = Answer::join('clauses', 'clauses.id', '=', 'answers.clause_id')
            ->groupBy('clause_id')
            ->where(['client_id' => $client_id, 'project_id' => $project_id])
            ->where('is_submitted', 1)
            ->select('clauses.name', \DB::raw('COUNT(*) as total'), \DB::raw('COUNT(CASE WHEN yes_or_no = "YES" THEN answers.id END ) as conformity'), \DB::raw('COUNT(CASE WHEN yes_or_no = "NO" THEN answers.id END ) as non_conformity'), \DB::raw('COUNT(CASE WHEN is_exception = 1 OR yes_or_no IS NULL THEN answers.id END ) as not_applicable'))
            ->get();
        $categories = [];
        $conformity = [];
        $non_conformity = [];
        $not_applicable = [];
        foreach ($reports as $report) {
            $total = $report->total;
            $categories[] = $report->name;
            $conformity[] = [
                'name' => $report->name,
                'y' => (float) (($report->conformity > 0) ? sprintf('%0.1f', $report->conformity * 100 / $total) : 0),
            ];
            $non_conformity[] = [
                'name' => $report->name,
                'y' => (float) (($report->non_conformity > 0) ? sprintf('%0.1f', $report->non_conformity * 100 / $total) : 0),
            ];

            $not_applicable[] = [
                'name' => $report->name,
                'y' => (float) (($report->not_applicable > 0) ? sprintf('%0.1f', $report->not_applicable * 100 / $total) : 0),
            ];
        }
        $series = [
            [
                'name' => 'Conformity',
                'data' => $conformity, //array format
                'color' => '#00a65a',
                'stack' => 'Management Clause'
            ],
            [
                'name' => 'Non Conformity',
                'data' => $non_conformity, //array format
                'color' => '#f00c12',
                'stack' => 'Management Clause'
            ],
            [
                'name' => 'N/A',
                'data' => $not_applicable, //array format
                'color' => '#666666',
                'stack' => 'Management Clause'
            ],
        ];
        $project = Project::with('standard')->find($project_id);
        $subtitle = $project->standard->name;
        return response()->json(compact('categories', 'series', 'subtitle'), 200);
    }
    public function clientProjectRequirementCompletionReport(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        $clauses = Answer::groupBy('clause_id')
            ->join('clauses', 'clauses.id', '=', 'answers.clause_id')
            ->where(['client_id' => $client_id, 'project_id' => $project_id])
            ->select('clauses.name', 'clauses.id')
            ->get();

        $data = [];
        foreach ($clauses as $clause) {
            $clause_id = $clause->id;
            $uploaded_documents = Upload::where(['client_id' => $client_id, 'project_id' => $project_id, 'clause_id' => $clause_id, 'is_exception' => 0])
                ->where('link', '!=', NULL)
                ->count();
            $expected_documents = Upload::where(['client_id' => $client_id, 'project_id' => $project_id, 'clause_id' => $clause_id])->count();

            $answered_questions = Answer::where(['client_id' => $client_id, 'project_id' => $project_id, 'clause_id' => $clause_id, 'is_exception' => 0])
                ->where('is_submitted', 1)
                ->count();
            $all_questions = Answer::where(['client_id' => $client_id, 'project_id' => $project_id, 'clause_id' => $clause_id])->count();

            $total_task = $expected_documents + $all_questions;
            $total_response = $uploaded_documents + $answered_questions;
            $percentage_progress = 0;
            if ($total_task > 0) {
                $percentage_progress = $total_response / $total_task * 100;
            }
            $data[] = [
                $clause->name,
                (float) sprintf('%0.1f', $percentage_progress)
            ];
            // $clause->progress = (float) sprintf('%0.1f', $percentage_progress);
        }

        $project = Project::with('standard')->find($project_id);
        $subtitle = $project->standard->name;
        return response()->json(compact('data', 'subtitle'), 200);
    }

    public function partnerDataAnalysisDashbord()
    {
        $partner_id = $this->getPartner()->id;
        $clients = Client::where('partner_id', $partner_id)->count();
        $projects = Project::where('partner_id', $partner_id)->count();
        // $uploads = Upload::where('is_exception', 0)->where('link', '!=', NULL)->count();
        return response()->json(compact('clients', 'projects'), 200);
    }
    public function adminDataAnalysisDashbord()
    {
        // $uploaded_documents = Upload::where('is_exception', 0)->where('link', '!=', NULL)->count();
        // $expected_documents = Upload::count();
        // $answered_questions = Answer::where('is_exception', 0)->where('is_submitted', 1)->count();
        // $all_questions = Answer::count();
        // $exceptions = Exception::count();
        // return response()->json(compact('uploaded_documents', 'expected_documents', 'answered_questions', 'all_questions', 'exceptions', 'clients', 'projects', 'standards'), 200);
        $clients = Client::count();
        $projects = Project::count();
        $standards = Standard::count();
        $uploads = Upload::where('is_exception', 0)->where('link', '!=', NULL)->count();
        return response()->json(compact('clients', 'projects', 'standards', 'uploads'), 200);
    }

    public function soaSummary(Request $request)
    {
        $client_id = $request->client_id;
        $reports = SOAArea::with('controls')->orderBy('name')->get();
        $controls = [];
        $categories = [];
        $applicable = [];
        $implemented = [];
        $tabular_presentations = [];
        foreach ($reports as $report) {
            $iso_controls = count($report->controls);
            $categories[] = $report->name;
            $applicable_control = StatementOfApplicability::where([
                'client_id' => $client_id,
                's_o_a_area_id' => $report->id
            ])->where('applicable', 'Yes')->count();
            $implemented_control = StatementOfApplicability::where([
                'client_id' => $client_id,
                's_o_a_area_id' => $report->id
            ])->where('applicable', 'Yes')->where('implemented', 'Yes')->count();

            $controls[] = [
                'name' => $report->name,
                'y' => $iso_controls,
            ];
            $applicable[] = [
                'name' => $report->name,
                'y' => $applicable_control,
            ];
            $implemented[] = [
                'name' => $report->name,
                'y' => $implemented_control,
            ];
            $percent_control_applicable = ($iso_controls > 0) ? $applicable_control / $iso_controls * 100 : 0;
            $percent_control_implemented = ($applicable_control > 0) ? $implemented_control / $applicable_control * 100 : 0;
            $report->no_of_controls = $iso_controls;
            $report->applicable_controls = $applicable_control;
            $report->implemented_controls = $implemented_control;
            $report->percent_control_applicable = $percent_control_applicable;
            $report->percent_control_implemented = $percent_control_implemented;
            $tabular_presentations[] = $report;
        }
        $series = [
            [
                'name' => 'Number of ISO/IEC 27001 Controls',
                'data' => $controls, //array format
                // 'color' => '#00a65a',
            ],
            [
                'name' => 'Number of Applicable Controls',
                'data' => $applicable, //array format
                // 'color' => '#00a65a',
            ],
            [
                'name' => 'Number of Applicable Controls Implemented',
                'data' => $implemented, //array format
                // 'color' => '#f00c12',
            ],
        ];
        $subtitle = '';
        return response()->json(compact('categories', 'series', 'subtitle', 'tabular_presentations'), 200);
    }

    public function riskAssessmentSummary(Request $request)
    {
        $client_id = $request->client_id;
        $summary = RiskAssessment::join('asset_types', 'asset_types.id', '=', 'risk_assessments.asset_type_id')
            ->groupBy('asset')
            ->where('client_id', $client_id)
            ->select('asset_types.name as asset_type', 'risk_owner', 'asset',  \DB::raw('COUNT(*) as no_of_threats'), \DB::raw('COUNT(CASE WHEN risk_category = "Low" THEN risk_assessments.id END ) as lows'), \DB::raw('COUNT(CASE WHEN risk_category = "Medium" THEN risk_assessments.id END ) as mediums'), \DB::raw('COUNT(CASE WHEN risk_category = "High" THEN risk_assessments.id END ) as highs'))
            ->get();
        return response()->json(compact('summary'), 200);
    }
}
