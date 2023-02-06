<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FeedBack;
use App\Models\Project;
use App\Models\ProjectCertificate;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $client = $this->getClient();
        // $consulting_id = $request->consulting_id;
        $projects = Project::with('certificate', 'standard')->where(['client_id' => $client->id, 'year' => $this->getYear()])->orderBy('id', 'DESC')->get(); //->paginate(10);
        return response()->json(compact('projects'), 200);
    }

    public function clientProjects(Request $request)
    {
        $client_id = $request->client_id;
        // $consulting_id = $request->consulting_id;
        $projects = Project::with('certificate', 'standard')->where(['client_id' => $client_id, 'year' => $this->getYear()])->orderBy('id', 'DESC')->get(); //->paginate(10);
        return response()->json(compact('projects'), 200);
    }
    public function clientProjectCertificates(Request $request)
    {
        $client_id = $request->client_id;
        // $consulting_id = $request->consulting_id;
        $certificates = ProjectCertificate::with('project.standard')->where(['client_id' => $client_id])->orderBy('id', 'DESC')->get(); //->paginate(10);
        return response()->json(compact('certificates'), 200);
    }

    public function clientProjectFeedback(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        $feedbacks = FeedBack::where(['client_id' => $client_id, 'project_id' => $project_id])->get(); //->paginate(10);
        return response()->json(compact('feedbacks'), 200);
    }

    public function uploadProjectCertificate(Request $request)
    {
        $projectCert = ProjectCertificate::find($request->project_certificate_id);
        $cert_type = $request->cert_type;
        if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
            $file_name = $cert_type . '_project_' . $projectCert->project_id . "." . $request->file('file_uploaded')->guessClientExtension();
            $link = $request->file('file_uploaded')->storeAs('clients/' . $projectCert->client_id . '/certificate', $file_name, 'public');
            // $media = $request->file('file_uploaded');
            // $file_name = 'file_for_clause_' . $upload->id . '_' . time() . "." . $request->file('file_uploaded')->guessClientExtension();
            // $link = $this->uploadFile($media, $file_name, $folder_key);
            $projectCert->$cert_type = $link;
            $projectCert->save();
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $client_id = $request->client_id;
        $client = Client::find($client_id);
        // $user = $this->getUser();
        $consulting_id = $request->consulting_id;
        $standards = json_decode(json_encode($request->standards));
        foreach ($standards as $standard_id) {
            $project = Project::firstOrCreate([
                'client_id' => $client_id,
                'consulting_id' => $consulting_id,
                'standard_id' => $standard_id,
                'year' => $this->getYear(),
            ]);

            $cert_obj = new ProjectCertificate();

            $cert_obj->create($project);
        }
        $actor = $this->getUser();
        $title = "New Project Created for Client";
        //log this event
        $description = "New project was created for $client->name by $actor->name";
        $this->auditTrailEvent($title, $description);
        return response()->json(['message' => 'Successful'], 200);
    }

    public function saveClientFeedback(Request $request)
    {
        $client_id = $request->client_id;
        $project_id = $request->project_id;
        if (isset($request->extra_details)) {
            $extra_details = json_decode(json_encode($request->extra_details));
            foreach ($extra_details as $detail) {
                $label =  $detail->label;
                $value =  $detail->value;
                $feedback = FeedBack::where(['client_id' => $client_id, 'project_id' => $project_id, 'label' => $label])->first();
                if (!$feedback) {

                    $feedback = new FeedBack();
                }
                $feedback->client_id = $client_id;
                $feedback->project_id = $project_id;
                $feedback->label = $label;
                $feedback->value = $value;
                $feedback->save();
            }
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        //
        $project = $project->with('standard.clauses')->find($project->id);
        return response()->json(compact('project'), 200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function setDates(Request $request, Project $project)
    {
        //
        $field = $request->field;
        $value = $request->date;
        $project->$field = date('Y-m-d', strtotime($value));
        $project->save();
        return response()->json(['message' => 'Successful'], 200);
    }

    public function toggleCompletion(Request $request, Project $project)
    {
        $is_completed = $request->value;
        $project->is_completed = $is_completed;
        $project->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $client_id = $project->client_id;
        $client = Client::find($client_id);
        $actor = $this->getUser();
        $title = "Project deleted";
        //log this event
        $description = "Project with id $project->id was deleted for $client->name by $actor->name";
        $this->auditTrailEvent($title, $description);

        $project->delete();
        return response()->json([], 204);
    }
}
