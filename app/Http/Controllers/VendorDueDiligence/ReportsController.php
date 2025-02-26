<?php

namespace App\Http\Controllers\VendorDueDiligence;

use App\Http\Controllers\Controller;
use App\Models\VendorDueDiligence\Vendor;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //

    public function vendorOnboardingReport(Request $request)
    {
        $client_id = $this->getClient()->id;
        $vendor_count = Vendor::where('client_id', $client_id)->count();
        $approved_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%Approve%')
            ->count();
        $rejected_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%Reject%')
            ->count();
        $pending_approval = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '=', NULL)
            ->count();
        return response()->json(compact('vendor_count', 'approved_vendors', 'rejected_vendors', 'pending_approval'), 200);
    }
}
