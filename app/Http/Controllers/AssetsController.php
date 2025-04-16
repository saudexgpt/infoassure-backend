<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetType;

class AssetsController extends Controller
{
    //
    public function fetchAssetTypes(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_types = AssetType::where('client_id', $client_id)->orderBy('name')->get();
        return response()->json(compact('asset_types'), 200);
    }
    public function fetchAssets(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_type_id = $request->asset_type_id;
        $assets = Asset::with('owner')->where(['client_id' => $client_id, 'asset_type_id' => $asset_type_id])->orderBy('name')->get();
        return response()->json(compact('assets'), 200);
    }

    public function saveAssetTypes(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $names_array = $request->names;
        foreach ($names_array as $name) {
            AssetType::firstOrCreate([
                'client_id' => $client_id,
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function saveAssets(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_type_id = $request->asset_type_id;
        $name = $request->name;
        $data = $request->toArray();
        Asset::firstOrCreate([
            'client_id' => $client_id,
            'asset_type_id' => $asset_type_id,
            'name' => trim($name)
        ], $data);
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
