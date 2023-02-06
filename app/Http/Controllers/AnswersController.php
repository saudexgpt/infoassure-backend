<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Clause;
use App\Models\Client;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->getUser();
        $project_id = $request->project_id;
        $consulting_id = $request->consulting_id;
        $client_id = $request->client_id;
        $standard_id = $request->standard_id;
        $fetch_questions = Question::where('standard_id', $standard_id)->get();
        foreach ($fetch_questions as $fetch_question) {
            // create answer
            $data = [
                'client_id' => $client_id,
                'standard_id' => $standard_id,
                'project_id' => $project_id,
                'consulting_id' => $consulting_id,
                'question_id' => $fetch_question->id,
                'clause_id' => $fetch_question->clause_id,
                // 'created_by' => $user->id,
            ];
            $answer_obj = new Answer();
            $answer_obj->createProjectAnswer($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function show(Answer $answer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function submitAnswers(Request $request)
    {

        $user = $this->getUser();
        $value = $request->value;
        $answer_ids = json_decode(json_encode($request->answer_ids));
        Answer::whereIn('id', $answer_ids)->update(['is_submitted' => $value]);

        //send notification
        $answer = Answer::with('clause')->find($answer_ids[0]);
        $clause = $answer->clause;
        $name = $user->name . ' (' . $user->email . ')';
        if ($value === 1) {

            $title = "Answers Submitted";
            //log this event
            $description = "$name submitted response on gap assessment for clause: $clause->name";
        } else {

            $title = "Response modification enabled";
            //log this event
            $description = "$name enabled response modification on gap assessment for clause: $clause->name";
        }
        $this->auditTrailEvent($title, $description, $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Answer $answer)
    {
        $field = $request->field;
        $answer->$field = $request->answer;
        $answer->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function remarkOnAnswer(Request $request,  Answer $answer)
    {
        $user = $this->getUser();
        $remark = $request->remark;
        $answer->remark = $remark;
        $answer->save();

        $clause = Clause::find($answer->clause_id);
        $client = Client::find($answer->client_id);
        $title = "Remark on gap assessment";
        //log this event
        $description = "$user->name made a remark on gap assessment for clause: $clause->name by $client->name";
        $this->auditTrailEvent($title, $description);
    }
}
