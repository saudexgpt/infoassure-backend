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
        // $uploaded_documents = Upload::where(['client_id' => $client->id, 'is_exception' => 0])->where('link', '!=', NULL)->count();
        // $expected_documents = Upload::where(['client_id' => $client->id])->count();
        // $answered_questions = Answer::where(['client_id' => $client->id, 'is_exception' => 0])->where('is_submitted', 1)->count();
        // $all_questions = Answer::where(['client_id' => $client->id])->count();
        // $exceptions = Exception::where('client_id', $client->id)->count();
        $all_projects_count = Project::where(['client_id' => $client->id, 'year' => $this->getYear()])
            // ->where('created_at', 'LIKE', '%' . $year . '%')
            ->count();
        $completed_projects = Project::where(['client_id' => $client->id, 'year' => $this->getYear()])
            ->where('is_completed', 1)
            // ->where('created_at', 'LIKE', '%' . $year . '%')
            ->count();

        $in_progress = $all_projects_count - $completed_projects;
        $all_projects = Project::with('certificate', 'standard')->where(['client_id' => $client->id, 'year' => $this->getYear()])
            // ->where('created_at', 'LIKE', '%' . $year . '%')
            ->get();
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
