<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetType;
use OpenAI\Laravel\Facades\OpenAI;

class AssetsController extends Controller
{
    public function __construct(Request $httpRequest)
    {
        parent::__construct($httpRequest);
        $this->middleware(function ($request, $next) {

            $this->autoGenerateAndSaveAssetTypes();
            return $next($request);
        });


    }
    private function autoGenerateAndSaveAssetTypes()
    {
        // if (isset($request->client_id)) {
        //     $client_id = $request->client_id;
        // } else {
        //     $client_id = $this->getClient()->id;
        // }
        $asset_type_count = AssetType::count();
        if ($asset_type_count < 1) {
            $asset_types = $this->generateAssetTypes(); // $request->names;

            foreach ($asset_types as $asset_type) {
                AssetType::updateOrCreate([
                    // 'client_id' => $client_id,
                    'name' => trim(ucwords($asset_type->category)),
                ], ['asset_samples' => $asset_type->assets]);
            }
        }

    }
    private function generateAssetTypes()
    {
        //
        // $message = "As an ISMS manager list all possible 'ASSET TYPES' a company can have. ";
        // $instruction = "Provide the response in a string array format";

        // $content = $message . $instruction;

        // $result = OpenAI::chat()->create([
        //     'model' => 'gpt-3.5-turbo',
        //     'messages' => [
        //         ['role' => 'user', 'content' => $content],
        //     ],
        // ]);

        // // response is score and justification
        // $ai_response = json_decode($result->choices[0]->message->content);
        // return $ai_response;
        $filename = portalPulicPath('asset_types.json');
        $file_content = file_get_contents($filename);
        return json_decode($file_content);
        // print_r($result);
    }
    //
    public function fetchAssetTypes(Request $request)
    {
        // if (isset($request->client_id)) {
        //     $client_id = $request->client_id;
        // } else {
        //     $client_id = $this->getClient()->id;
        // }
        $asset_types = AssetType::orderBy('name')->get();
        return response()->json(compact('asset_types'), 200);
    }
    public function fetchAssets(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $query = Asset::query()->join('asset_types', 'asset_types.id', '=', 'assets.asset_type_id')
            ->where('assets.client_id', $client_id);
        if (isset($request->name) && $request->name != '') {

            $query = $query->where('assets.name', 'LIKE', "%$request->name%")
                ->orWhere('asset_types.name', 'LIKE', "%$request->name%");
        }
        $assets = $query->select('*', 'assets.id as id', 'assets.name as name', 'asset_types.name as asset_type_name')
            ->get()
            ->groupBy('asset_type_name');
        return response()->json(compact('assets'), 200);
    }

    public function saveAssetTypes(Request $request)
    {
        // if (isset($request->client_id)) {
        //     $client_id = $request->client_id;
        // } else {
        //     $client_id = $this->getClient()->id;
        // }
        $names_array = $request->names;
        foreach ($names_array as $name) {
            AssetType::firstOrCreate([
                // 'client_id' => $client_id,
                'name' => trim(ucwords($name))
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function saveAssets(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'asset_type_id' => 'required|exists:asset_types,id',
            'location' => 'nullable|string',
            'classification' => 'nullable|string',
            'purpose' => 'nullable|string'
        ]);
        if (isset($request->client_id) && $request->client_id != null) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $name = $data['name'];
        Asset::firstOrCreate([
            'client_id' => $client_id,
            'name' => trim($name)
        ], $data);
        //add to asset type asset_samples with new record
        $this->updateAssetTypeAssetSamples($data['asset_type_id'], $name);
        return response()->json(['message' => 'Successful'], 200);
    }
    private function updateAssetTypeAssetSamples($asset_type_id, $new_asset)
    {
        $asset_type = AssetType::find($asset_type_id);
        $old_asset_samples = $asset_type->asset_samples;
        $old_asset_samples[] = $new_asset;
        $asset_type->asset_samples = array_unique($old_asset_samples);
        $asset_type->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function updateAssetType(Request $request, AssetType $asset_type)
    {
        $asset_type->name = $request->name;
        $asset_type->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function updateAsset(Request $request, Asset $asset)
    {
        $data = $request->toArray();
        $asset->update($data);
        return response()->json(['message' => 'Successful'], 200);
    }

    public function setAssetOwner(Request $request, Asset $asset)
    {
        $asset->owner_id = $request->owner_id;
        $asset->save();
        return response()->json(compact('asset'), 200);
    }
    public function deleteAssetType(AssetType $value)
    {
        $value->delete();
        return response()->json([], 204);
    }
}
