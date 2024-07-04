<?php

namespace App\Http\Controllers;

use App\Models\GeneralRiskLibrary;
use Illuminate\Http\Request;

class GeneralRiskLibrariesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $risk_libraries = GeneralRiskLibrary::orderBy('threats')->paginate(10);
        return response()->json(compact('risk_libraries'), 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        GeneralRiskLibrary::firstOrCreate([
            'threats' => $request->threats
        ], [
            'vulnerabilities' => $request->vulnerabilities,
            'solutions' => $request->solutions
        ]);
    }

    public function storeBulk(Request $request)
    {
        $bulk_data = json_decode(json_encode($request->bulk_data));
        foreach ($bulk_data as $csvRow) {
            //try {
            $threats = trim($csvRow->THREATS);
            $vulnerabilities = isset($csvRow->VULNERABILITIES) ? trim($csvRow->VULNERABILITIES) : NULL;
            $solutions = isset($csvRow->SOLUTIONS) ? trim($csvRow->SOLUTIONS) : NULL;

            $risk_library = GeneralRiskLibrary::where('threats', $threats)->first();
            if ($risk_library) {
                $risk_library->vulnerabilities .= ' ' . $vulnerabilities;
                $risk_library->solutions .= ' ' . $solutions;
                $risk_library->save();
            } else {
                GeneralRiskLibrary::create([
                    'threats' => $threats,
                    'vulnerabilities' => $vulnerabilities,
                    'solutions' => $solutions
                ]);
            }
        }
        return 'success';

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GeneralRiskLibrary  $generalRiskLibrary
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GeneralRiskLibrary $generalRiskLibrary)
    {
        //
        $field = $request->field;
        $generalRiskLibrary->$field = $request->value;
        $generalRiskLibrary->save();
        return response()->json([], 204);
    }
}
