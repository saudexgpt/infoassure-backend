<?php

namespace App\Http\Controllers\VendorDueDiligence;

use App\Http\Controllers\Controller;
use App\Models\VendorDueDiligence\Contract;
use App\Models\VendorDueDiligence\SlaConfig;
use App\Models\VendorDueDiligence\VendorPerformanceScorecard;
use Illuminate\Http\Request;

class ContractsAndSLAController extends Controller
{
    //
    public function fetchContracts(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $client_id = $request->client_id;
        $contracts = Contract::with('sla', 'score', 'vendor', 'client')
            ->where([
                'vendor_id' => $vendor_id,
                'client_id' => $client_id,
            ])
            ->get();
        return response()->json(compact('contracts'), 200);
    }

    public function fetchSLA(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $client_id = $request->client_id;
        $contract_id = $request->contract_id;
        $slas = SlaConfig::where([
            'vendor_id' => $vendor_id,
            'client_id' => $client_id,
            'contract_id' => $contract_id
        ])->get();
        return response()->json(compact('slas'), 200);
    }

    public function uploadContract(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $client_id = $request->client_id;
        $title = $request->title;
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $expiry_data = date('Y-m-d', strtotime($request->expiry_data));
        $file = $request->file('file_uploaded');
        if ($file != null && $file->isValid()) {

            $formated_name = str_replace(' ', '_', ucwords($title));
            $file_name = $formated_name . '_' . $vendor_id . "." . $file->guessClientExtension();

            $link = $file->storeAs('vendors/' . $vendor_id . '/documents', $file_name, 'public');
            Contract::updateOrCreate([
                'vendor_id' => $vendor_id,
                'client_id' => $client_id,
                'title' => $title
            ], ['file_link' => $link, 'start_date' => $start_date, 'expiry_date' => $expiry_data]);

            // Log this action
        }

    }

    public function saveSLAConfig(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $client_id = $request->client_id;
        $contract_id = $request->contract_id;
        $data = $request->toArray();
        SlaConfig::updateOrCreate([
            'vendor_id' => $vendor_id,
            'client_id' => $client_id,
            'contract_id' => $contract_id
        ], $data);

        // Log this action


    }

    public function saveVendorPerformanceScore(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $client_id = $request->client_id;
        $contract_id = $request->contract_id;
        $sla_config_id = $request->sla_config_id;
        $data = $request->toArray();
        VendorPerformanceScorecard::updateOrCreate([
            'vendor_id' => $vendor_id,
            'client_id' => $client_id,
            'contract_id' => $contract_id,
            'sla_config_id' => $sla_config_id
        ], $data);

        // Log this action


    }
}
