<?php

namespace App\Http\Controllers;

use App\Models\RiskImpact;
use App\Models\RiskLikelihood;
use App\Models\RiskMatrix;
use Illuminate\Http\Request;

class RiskMatricesController extends Controller
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
                        'matrix' => $matrix
                    ],
                    [
                        'name' => $content['name'],
                        'value' => $content['value'],
                    ]
                );
            }

        }
        foreach ($likelihood_matrices as $matrix => $matrix_array) {
            foreach ($matrix_array as $content) {
                RiskLikelihood::firstOrCreate(
                    [
                        'client_id' => $client_id,
                        'matrix' => $matrix
                    ],
                    [
                        'name' => $content['name'],
                        'value' => $content['value'],
                    ]
                );
            }

        }
        $impact_matrices = RiskImpact::where('client_id', $client_id)->orderBy('value')->get()->groupBy('matrix');
        $likelihood_matrices = RiskLikelihood::where('client_id', $client_id)->orderBy('value')->get()->groupBy('matrix');
        $risk_matrix = RiskMatrix::with('creator', 'approver')->where('client_id', $client_id)->first();
        $matrices = ['3x3', '5x5'];
        return response()->json(compact('impact_matrices', 'likelihood_matrices', 'risk_matrix', 'matrices'), 200);
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
}
