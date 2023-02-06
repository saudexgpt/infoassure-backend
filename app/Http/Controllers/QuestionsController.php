<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = Question::with('standard', 'clause')->orderBy('id', 'DESC')->paginate(10);
        return response()->json(compact('questions'), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $questions_string = $request->questions;
        $questions_array = explode('|', $questions_string);
        foreach ($questions_array as $question) {
            Question::firstOrCreate([
                'standard_id' => $request->standard_id,
                'clause_id' => $request->clause_id,
                'question' => $question,
                // 'question_type' => $request->question_type,
                'answer_type' => $request->answer_type
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function show(Question $question)
    {
        //
        $question = $question->with('standard', 'clause')->find($question->id);
        return response()->json(compact('question'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Question $question)
    {
        //
        $question->standard_id = $request->standard_id;
        $question->clause_id = $request->clause_id;
        $question->question = $request->question;
        $question->question_type = $request->question_type;
        $question->answer_type = $request->answer_type;
        $question->save();
        return response()->json(['message' => 'Successful'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return response()->json([], 204);
    }
}
