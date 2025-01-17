<?php

namespace App\Http\Controllers;

use App\Models\Clause;
use App\Models\Client;
use App\Models\DocumentTemplate;
use App\Models\Upload;
use Illuminate\Http\Request;

class UploadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createUploads(Request $request)
    {
        $last_year = $this->getYear() - 1;
        $client_id = $request->client_id;
        $templates = DocumentTemplate::get();
        // create answer
        if (!empty($templates)) {
            foreach ($templates as $template) {
                $upload = Upload::where([
                    'client_id' => $client_id,
                    'is_exception' => 0,
                ])->where('created_at', 'LIKE', '%' . $last_year . '%')->first();
                if (!$upload) {
                    $this->createNewUploads($request, $template);
                } else {
                    $this->createUploadsForOldClients($request, $upload);
                }
            }
        }
    }
    private function createUploadsForOldClients($request, $old_upload)
    {
        $user = $this->getUser();
        $client_id = $request->client_id;
        $upload = Upload::where([
            'client_id' => $client_id,
            'template_id' => $old_upload->template_id,
        ])->first();
        if (!$upload) {
            $upload = new Upload();
        }
        $upload->client_id = $client_id;
        $upload->created_by = $user->id;
        $upload->template_id = $old_upload->template_id;
        $upload->template_title = $old_upload->template_title;
        $upload->template_link = $old_upload->template_link;
        $upload->save();
    }
    private function createNewUploads($request, $template)
    {
        $user = $this->getUser();
        $client_id = $request->client_id;
        $upload = Upload::where([
            'client_id' => $client_id,
            'template_id' => $template->id,
        ])->first();
        if (!$upload) {
            $upload = new Upload();
        }
        $upload->client_id = $client_id;
        $upload->created_by = $user->id;
        $upload->template_id = $template->id;
        $upload->template_title = $template->title;
        $upload->template_link = $template->link;
        $upload->save();
    }
    public function uploadEvidenceFile(Request $request)
    {
        $client = $this->getClient();
        $upload = Upload::find($request->upload_id);
        $folder_key = $client->id;
        if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
            $formated_name = str_replace(' ', '_', ucwords($upload->template_title));
            $file_name = 'evidence_for_' . $formated_name . '_' . $client->id . '_template' . $upload->template_id . "." . $request->file('file_uploaded')->guessClientExtension();
            $link = $request->file('file_uploaded')->storeAs('clients/' . $folder_key . '/document', $file_name, 'public');
            $upload->link = $link;
            $upload->save();

            $user = $this->getUser();
            $users = $client->users;
            $userIds = $client->users()->pluck('id');
            $userIds = $userIds->toArray();

            $name = $user->name;// . ' (' . $user->email . ')';
            $title = "Document Uploaded";
            //log this event
            $description = "$name uploaded a document titled: $upload->template_title";
            $this->sendNotification($title, $description, $userIds);
            // $this->auditTrailEvent($title, $description, $users);

            return $link;
        }
    }


    public function remarkOnUpload(Request $request, Upload $upload)
    {
        $value = $request->value;
        $field = $request->field;
        $upload->$field = $value;
        $upload->save();

        $clause = Clause::find($upload->clause_id);
        $client = Client::find($upload->client_id);
        $title = "Remark on uploaded document";
        //log this event
        $description = "Remark was made on document title: $clause->template_title, clause: $clause->name uploaded by $client->name";
        $this->auditTrailEvent($title, $description);
    }
}
