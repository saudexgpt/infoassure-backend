<?php

namespace App\Http\Controllers;

use App\Models\Risk;
use App\Models\RiskControlSelfAssessment;
use Illuminate\Http\Request;

class RCSAController extends Controller
{


    public function fetchRCSA(Request $request)
    {
        $rcsas = RiskControlSelfAssessment::where([
            'client_id' => $request->client_id,
            'business_unit_id' => $request->business_unit_id
        ])->orderBy('created_at', 'DESC')->get()->groupBy('category');
        return response()->json(compact('rcsas'), 200);
    }
    public function createRCSAFromRCM(Request $request)
    {
        $risks = Risk::with('businessUnit', 'businessProcess')->where([
            'client_id' => $request->client_id,
            'business_unit_id' => $request->business_unit_id
        ])->get();

        foreach ($risks as $risk) {
            RiskControlSelfAssessment::firstOrCreate(
                ['rcm_id' => $risk->id],
                [
                    'client_id' => $risk->client_id,
                    'business_unit_id' => $risk->business_unit_id,
                    'business_process_id' => $risk->business_process_id,

                    'category' => strtoupper($risk->businessProcess->name),
                    'key_process' => ucwords($risk->businessProcess->name),
                    'control_owner' => $risk->control_owner,
                    'control_activities' => $risk->control_description,
                    'control_type' => $risk->control_type,
                    'risk_description' => $risk->description,
                ]
            );
        }

        // $rcsas = RiskControlSelfAssessment::where([
        //     'client_id' => $request->client_id,
        //     'business_unit_id' => $request->business_unit_id
        // ])->get()->groupBy('category');
        return $this->fetchRCSA($request);
    }
    /**
     * Save tnew record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        RiskControlSelfAssessment::firstOrCreate([
            'client_id' => $request->client_id,
            'business_unit_id' => $request->business_unit_id,
            'business_process_id' => $request->business_process_id,
            'rcm_id' => $request->rcm_id,
            'category' => $request->category,
            'key_process' => $request->key_process,
            'control_owner' => $request->control_owner,
            'source' => $request->source,
            'control_type' => $request->control_type,
            'risk_description' => $request->risk_description,
            'self_assessment_control' => $request->self_assessment_control,
            'self_assessment_score' => $request->self_assessment_score,
            'comment_on_status' => $request->comment_on_status,
            'rm_rating_of_control' => $request->rm_rating_of_control,
            'validation' => $request->validation,
            'basis_of_rm_rating' => $request->basis_of_rm_rating,
            'self_assessment_of_process_level_risk' => $request->self_assessment_of_process_level_risk,
            'rm_validated_process_level_risk' => $request->rm_validated_process_level_risk,
        ]);
        return response()->json('success');
    }
    public function createNewCategory(Request $request)
    {
        $category = $request->category;
        $control_data = json_decode(json_encode($request->control_data));
        foreach ($control_data as $control) {
            RiskControlSelfAssessment::firstOrCreate(
                [
                    'client_id' => $request->client_id,
                    'business_unit_id' => $request->business_unit_id,
                    'category' => strtoupper($category),
                    'key_process' => ucwords($control->key_process),
                    'control_activities' => $control->control_activities
                ],
                [

                    'control_owner' => $control->control_owner,
                    'control_type' => $control->control_type,
                    'risk_description' => $control->risk_description,
                ]
            );
        }
        return response()->json('success');
    }
    public function updateFields(Request $request, RiskControlSelfAssessment $rcsa)
    {
        //
        $field = $request->field;
        $value = $request->value;
        $rcsa->$field = $value;
        if ($field == 'self_assessment_control') {
            $rcsa->self_assessment_score = $this->calculateScore($value);
            $rcsa->self_assessment_of_process_level_risk = $this->calculateLevelRisk($rcsa->self_assessment_score);
        }
        if ($field == 'rm_rating_of_control') {
            $rcsa->validation = $this->calculateScore($value);
            $rcsa->rm_validated_process_level_risk = $this->calculateLevelRisk($rcsa->validation);
        }
        $rcsa->save();
        $rcsas = RiskControlSelfAssessment::where([
            'client_id' => $rcsa->client_id,
            'business_unit_id' => $rcsa->business_unit_id
        ])->get()->groupBy('category');
        return response()->json(compact('rcsas'), 200);
    }
    private function calculateScore($value)
    {
        $score = 0;
        switch ($value) {
            case 'Level 1':
                $score = 0;
                break;
            case 'Level 2':
                $score = 4;
                break;
            case 'Level 3':
                $score = 7;
                break;
            case 'Level 4':
                $score = 10;
                break;

            default:
                $score = 0;
                break;
        }
        return $score;
    }
    private function calculateLevelRisk($value)
    {
        $risk = 'High';
        if ($value <= 3 && $value >= 0) {
            $risk = 'High';
        } else if ($value <= 6 && $value >= 4) {
            $risk = 'Medium';
        } else {
            $risk = 'Low';
        }
        return $risk;
    }
}
