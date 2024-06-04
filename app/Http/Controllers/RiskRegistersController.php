<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\RiskImpact;
use App\Models\RiskImpactArea;
use App\Models\RiskImpactOnArea;
use App\Models\RiskLikelihood;
use App\Models\RiskMatrix;
use App\Models\RiskRegister;
use Illuminate\Http\Request;

class RiskRegistersController extends Controller
{
    public function setupRiskMatrices(Request $request)
    {
        $client_id = $request->client_id;
        $impact_matrices = riskImpactMatrix();
        $likelihood_matrices = riskLikelihoodMatrix();
        foreach ($impact_matrices as $matrix => $matrix_array) {
            foreach ($matrix_array as $content) {
                RiskImpact::firstOrCreate(
                    [
                        'client_id' => $client_id,
                        'matrix' => $matrix,
                        'value' => $content['value'],
                    ],
                    [
                        'name' => $content['name'],
                    ]
                );
            }

        }
        foreach ($likelihood_matrices as $matrix => $matrix_array) {
            foreach ($matrix_array as $content) {
                RiskLikelihood::firstOrCreate(
                    [
                        'client_id' => $client_id,
                        'matrix' => $matrix,
                        'value' => $content['value'],
                    ],
                    [
                        'name' => $content['name'],
                    ]
                );
            }

        }
        $risk_matrix = RiskMatrix::where('client_id', $client_id)->first();
        if (!$risk_matrix) {
            $risk_matrix = new RiskMatrix();
            $risk_matrix->client_id = $client_id;
            $risk_matrix->current_matrix = '3x3';
            $risk_matrix->save();
        }
        $this->setupImpactOnAreas($client_id);
        $risk_matrix = RiskMatrix::with('creator', 'approver')->where('client_id', $client_id)->first();
        $active_matrix = $risk_matrix->current_matrix;

        $impact_matrices = RiskImpact::with('impactOnAreas.impactArea')->where(['client_id' => $client_id, 'matrix' => $active_matrix])->orderBy('value')->get()->groupBy('matrix');

        $likelihood_matrices = RiskLikelihood::where(['client_id' => $client_id, 'matrix' => $active_matrix])->orderBy('value')->get()->groupBy('matrix');
        $matrices = ['3x3', '5x5'];
        return response()->json(compact('impact_matrices', 'likelihood_matrices', 'risk_matrix', 'matrices'), 200);
    }

    private function setupImpactOnAreas($client_id)
    {
        $risk_impacts = RiskImpact::where('client_id', $client_id)->get();
        $impact_area_ids = RiskImpactArea::where('client_id', $client_id)->pluck('id');
        foreach ($risk_impacts as $risk_impact) {
            foreach ($impact_area_ids as $impact_area_id) {
                RiskImpactOnArea::firstOrCreate(
                    [
                        'client_id' => $client_id,
                        'risk_impact_id' => $risk_impact->id,
                        'impact_value' => $risk_impact->value,
                        'risk_impact_area_id' => $impact_area_id,
                        'matrix' => $risk_impact->matrix,
                    ]
                );
            }

        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchRiskMatrix(Request $request)
    {
        $risk_matrix = RiskMatrix::with('creator', 'approver')
            ->where('client_id', $request->client_id)
            ->first();
        return response()->json(compact($risk_matrix), 200);
    }


    /**
     * Display the specified resource in storage.
     *
     * @param  \App\Models\RiskMatrix  $riskMatrix
     * @return \Illuminate\Http\Response
     */
    public function show(RiskMatrix $riskMatrix)
    {
        $risk_matrix = $riskMatrix->with('creator', 'approver')->find($riskMatrix->id);
        return response()->json(compact('risk_matrix'), 200);
    }
    /**
     * Propose a specific matrix
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function proposeMatrix(Request $request)
    {
        $client_id = $request->client_id;
        $user_id = $this->getUser()->id;
        $risk_matrix = RiskMatrix::where('client_id', $client_id)->first();
        if (!$risk_matrix) {
            $risk_matrix = new RiskMatrix();
        }
        $risk_matrix->client_id = $client_id;
        $risk_matrix->proposed_matrix = $request->proposed_matrix;
        $risk_matrix->created_by = $user_id;
        $risk_matrix->save();
        return $this->show($risk_matrix);
    }

    /**
     * Approve a proposed matrix
     *
     * @param  \App\Models\RiskMatrix  $riskMatrix
     * @return \Illuminate\Http\Response
     */
    public function approveMatrix(RiskMatrix $riskMatrix)
    {
        $user_id = $this->getUser()->id;
        $riskMatrix->current_matrix = $riskMatrix->proposed_matrix;
        $riskMatrix->proposed_matrix = NULL;
        $riskMatrix->approved_by = $user_id;
        $riskMatrix->save();
        return $this->show($riskMatrix);
    }

    public function customizeRiskMatrixDescription(Request $request)
    {
        $id = $request->id;
        $table = $request->table;
        if ($table == 'impact') {
            $matrix = RiskImpact::find($id);
        } else {
            $matrix = RiskLikelihood::find($id);
        }
        $matrix->name = $request->name;
        $matrix->save();
        return response()->json([]);
    }
    /////////////////// RIKS REGISTERS ///////////////
    public function fetchRiskRegisters(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $business_unit_id = $request->business_unit_id;
        $risk_registers = RiskRegister::with('businessUnit', 'businessProcess')->where(['client_id' => $client_id, 'business_unit_id' => $business_unit_id])->get();
        return response()->json(compact('risk_registers'), 200);
        // $business_unit_id = $request->business_unit_id;
        // if (isset($request->client_id)) {
        //     $client_id = $request->client_id;
        // } else {
        //     $client_id = $this->getClient()->id;
        // }
        // $risk_registers = RiskRegister::join('business_units', 'risk_registers.business_unit_id', 'business_units.id')
        //     ->where(['risk_registers.client_id' => $client_id, 'business_unit_id' => $business_unit_id])
        //     ->select('risk_registers.*', 'business_units.unit_name', \DB::raw('CONCAT(prepend_risk_no_value,risk_id) as risk_id'))
        //     ->get();
        // return response()->json(compact('risk_registers'), 200);
    }
    public function storeRiskRegister(Request $request)
    {
        if ($request->file('link_to_evidence') == null) {
            return response()->json(['message' => 'Please uplaod a document as evidence'], 500);
        }

        $business_unit = BusinessUnit::find($request->business_unit_id);
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        // $business_process_id = $request->business_process_id;
        $riskRegister = RiskRegister::where([
            'client_id' => $request->client_id,
            'business_unit_id' => $request->business_unit_id,
            'business_process_id' => $request->business_process_id,
            'risk_id' => $business_unit->next_risk_id,
            // 'risk_type' => $risk_register->risk_type,
            'vunerability_description' => $request->risk_description
        ])->first();
        if (!$riskRegister) {
            RiskRegister::firstOrCreate(
                [
                    'client_id' => $request->client_id,
                    'business_unit_id' => $request->business_unit_id,
                    'business_process_id' => $request->business_process_id,
                    'type' => $request->type,
                    'vunerability_description' => $request->risk_description
                ],
                [
                    'risk_id' => $business_unit->next_risk_id,
                    'outcome' => $request->outcome,
                    'risk_owner' => $request->risk_owner,
                    'control_no' => 'CTRL' . $business_unit->next_risk_id,
                    'control_location' => $request->control_location,
                    'control_description' => $request->control_description,
                    'control_frequency' => $request->control_frequency,
                    'control_owner' => $request->control_owner,
                    'control_type' => $request->control_type,
                    'nature_of_control' => $request->nature_of_control,
                    'application_used_for_control' => $request->application_used_for_control,
                    'compensating_control' => $request->compensating_control,
                    'test_procedures' => $request->test_procedures,
                    'sample_size' => $request->sample_size,
                    'data_required' => $request->data_required,
                    'link_to_evidence' => $this->uploadRiskEvidenceDocument($request),
                    'test_conclusion' => $request->test_conclusion,
                    'gap_description' => $request->gap_description,
                    'tod_improvement_opportunity' => $request->tod_improvement_opportunity,
                    'recommendation' => $request->recommendation,
                    'responsibility' => $request->responsibility,
                    'timeline' => $request->timeline,
                    'tod_gap_status' => $request->tod_gap_status
                ]
            );
            $business_unit->next_risk_id += 1;
            $business_unit->save();
        }
        $business_unit->next_risk_id += 1;
        $business_unit->save();
        return response()->json('success');
    }
    private function uploadRiskEvidenceDocument(Request $request)
    {
        $folder_key = $request->client_id;
        $file = $request->file('link_to_evidence');
        if ($file != null && $file->isValid()) {

            $name = $file->getClientOriginalName();
            // $name = $request->file('file_uploaded')->hashName();
            // $file_name = $name . "." . $request->file('file_uploaded')->extension();
            $link = $file->storeAs('clients/' . $folder_key . '/risk-evidence', $name, 'public');

            return $link;
        }
        return NULL;
    }
    public function updateRiskRegister(Request $request, RiskRegister $riskRegister)
    {
        $field = $request->field;
        $value = $request->value;
        $riskRegister->$field = $value;
        $riskRegister->save();
        // $riskRegister->risk_type = $request->risk_type;
        // $riskRegister->vunerability_description = $request->vunerability_description;
        // $riskRegister->threat_impact_description = $request->threat_impact_description;
        // $riskRegister->existing_controls = $request->existing_controls;
        // $riskRegister->risk_owner = $request->risk_owner;
        // $riskRegister->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function deleteRiskRegister(Request $request, RiskRegister $riskRegister)
    {
        $riskRegister->delete();
        return response()->json(['message' => 'Successful'], 200);
    }
    /////////////////// END OF RIKS REGISTERS ///////////////
    /////////////////// RIKS IMPACT AREAS ///////////////
    public function fetchRiskImpactArea(Request $request)
    {
        // $business_unit_id = $request->business_unit_id;
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $risk_impact_areas = RiskImpactArea::where([
            'client_id' => $client_id,
            // 'business_unit_id' => $business_unit_id
        ])
            ->orderBy('area')
            ->get();
        return response()->json(compact('risk_impact_areas'), 200);
    }
    public function storeRiskImpactArea(Request $request)
    {
        $client_id = $request->client_id;
        // $business_unit_id = $request->business_unit_id;
        $impact_areas = $request->areas;
        foreach ($impact_areas as $area) {
            RiskImpactArea::firstOrCreate([
                'client_id' => $client_id,
                // 'business_unit_id' => $business_unit_id,
                'area' => trim($area)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function updateRiskImpactArea(Request $request, RiskImpactArea $riskImpactArea)
    {
        $riskImpactArea->area = $request->area;
        $riskImpactArea->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function deleteRiskImpactArea(Request $request, RiskImpactArea $riskImpactArea)
    {
        $riskImpactArea->delete();
        return response()->json(['message' => 'Successful'], 200);
    }
    /////////////////// END OF RIKS IMPACT AREAS///////////////
    /////////////////// RIKS IMPACT ON AREAS///////////////
    // public function fetchRiskImpactOnArea(Request $request)
    // {
    //     if (isset($request->client_id)) {
    //         $client_id = $request->client_id;
    //     } else {
    //         $client_id = $this->getClient()->id;
    //     }
    //     $risk_impact_area = RiskImpactOnArea::where(['client_id' => $client_id])->get();
    //     return response()->json(compact('risk_impact_area'), 200);
    // }
    // public function storeRiskImpactOnArea(Request $request)
    // {
    //     $impact_areas = $request->areas;
    //     foreach ($impact_areas as $area) {
    //         RiskImpactOnArea::firstOrCreate(
    //             [
    //                 'client_id' => $request->client_id,
    //                 'risk_impact_id' => $area->risk_impact_id,
    //                 'risk_impact_area_id' => $area->risk_impact_area_id,
    //                 'matrix' => $area->matrix,
    //             ],
    //             ['impact_level' => $area->impact_level]
    //         );
    //     }
    //     return response()->json(['message' => 'Successful'], 200);
    // }
    public function updateRiskImpactOnArea(Request $request, RiskImpactOnArea $riskImpactOnArea)
    {
        $riskImpactOnArea->impact_level = $request->impact_level;
        $riskImpactOnArea->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function deleteRiskImpactOnArea(Request $request, RiskImpactOnArea $riskImpactOnArea)
    {
        $riskImpactOnArea->delete();
        return response()->json(['message' => 'Successful'], 200);
    }
    /////////////////// END OF RIKS IMPACT ON AREAS///////////////
}
