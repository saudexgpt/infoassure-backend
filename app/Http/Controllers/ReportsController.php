<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Upload;
use App\Models\Exception;
use App\Models\Project;
use App\Models\Standard;
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
        foreach ($all_projects as $project) {
            $project->watchProjectProgress($project);
        }
        return response()->json(compact('all_projects', 'all_projects_count', 'completed_projects', 'in_progress'), 200);
    }

    public function clientProjectDataAnalysis(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        $uploaded_documents = Upload::where(['client_id' => $client_id, 'project_id' => $project_id, 'is_exception' => 0])->where('link', '!=', NULL)->count();
        $expected_documents = Upload::where(['client_id' => $client_id, 'project_id' => $project_id])->count();
        $answered_questions = Answer::where(['client_id' => $client_id, 'project_id' => $project_id, 'is_exception' => 0])->where('is_submitted', 1)->count();
        $all_questions = Answer::where(['client_id' => $client_id, 'project_id' => $project_id])->count();
        $exceptions = Exception::where(['client_id' => $client_id, 'project_id' => $project_id])->count();
        return response()->json(compact('uploaded_documents', 'expected_documents', 'answered_questions', 'all_questions', 'exceptions'), 200);
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
}
