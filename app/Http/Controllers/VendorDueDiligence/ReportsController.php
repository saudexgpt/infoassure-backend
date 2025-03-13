<?php

namespace App\Http\Controllers\VendorDueDiligence;

use App\Http\Controllers\Controller;
use App\Models\VendorDueDiligence\Category;
use App\Models\VendorDueDiligence\DueDiligenceResponse;
use App\Models\VendorDueDiligence\Invoice;
use App\Models\VendorDueDiligence\Vendor;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function vendorOnboardingCount()
    {
        $client_id = $this->getClient()->id;
        $vendor_count = Vendor::where('client_id', $client_id)->count();
        $approved_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%"Approve"%')
            ->count();
        $rejected_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%"Reject"%')
            ->count();
        $pending_approval = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '=', NULL)
            ->count();
        // }
        return response()->json(compact('vendor_count', 'approved_vendors', 'rejected_vendors', 'pending_approval'), 200);
    }
    public function vendorOnboardingReport(Request $request)
    {
        $client_id = $this->getClient()->id;
        $chart_type = $request->chart_type;
        switch ($chart_type) {
            case 'ratings':
                $this->fetchVendorByIRR($client_id);
                break;

            default:
                # code...
                break;
        }
        $vendor_count = Vendor::where('client_id', $client_id)->count();
        $approved_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%"Approve"%')
            ->count();
        $rejected_vendors = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '!=', NULL)
            ->where('second_approval', 'LIKE', '%"Reject"%')
            ->count();
        $pending_approval = Vendor::where(['client_id' => $client_id])
            ->where('second_approval', '=', NULL)
            ->count();

        $ratings_data = Vendor::where(['client_id' => $client_id])
            ->select(\DB::raw('COUNT(CASE WHEN inherent_risk_rating = 1 THEN vendors.id END ) as lows'), \DB::raw('COUNT(CASE WHEN inherent_risk_rating = 2 THEN vendors.id END ) as mediums'), \DB::raw('COUNT(CASE WHEN inherent_risk_rating = 3 THEN vendors.id END ) as highs'))
            ->get();
        $vendor_ratings_series = collect($ratings_data->toArray())->flatten()->all();

        $categories = Category::orderBy('slug')->get();
        $vendor_categories = [];
        $data = [];
        $vendor_grouped_categories = Vendor::leftJoin('categories', 'categories.id', '=', 'vendors.category_id')
            ->where(['client_id' => $client_id])
            ->select('vendors.id', 'category_id', 'slug')
            ->get()
            ->groupBy('slug');

        foreach ($vendor_grouped_categories as $key => $value) {
            $vendor_categories[] = ($key !== '') ? $key : 'Uncategorized';
            $data[] = count($value);
        }
        // foreach ($categories as $category) {
        //     $vendor_categories[] = $category->slug;
        //     $data[] = Vendor::where(['client_id' => $client_id, 'category_id' => $category->id])->count();
        // }
        $vendor_categories_series = [
            [
                'name' => 'Count',
                'data' => $data
            ]
        ];
        // $uncategorized_vendors = Vendor::where(['client_id' => $client_id, 'category_id' => NULL])->count();
        // if ($uncategorized_vendors > 0) {
        //     $vendor_categories[] = 'Uncategorized';
        //     $vendor_categories_series[] = $uncategorized_vendors;
        // }
        return response()->json(compact('vendor_count', 'approved_vendors', 'rejected_vendors', 'pending_approval', 'vendor_categories_series', 'vendor_ratings_series', 'vendor_categories', 'categories'), 200);
    }
    private function fetchVendorByIRR($client_id)
    {

        $ratings_data = Vendor::where(['client_id' => $client_id])
            ->select(\DB::raw('COUNT(CASE WHEN inherent_risk_rating = 1 THEN vendors.id END ) as lows'), \DB::raw('COUNT(CASE WHEN inherent_risk_rating = 2 THEN vendors.id END ) as mediums'), \DB::raw('COUNT(CASE WHEN inherent_risk_rating = 3 THEN vendors.id END ) as highs'))
            ->get();
        $vendor_ratings_series = collect($ratings_data->toArray())->flatten()->all();


        return response()->json(compact('vendor_ratings_series'), 200);
    }
    public function vendorInvoicesAnalysis(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::find($vendor_id);
        $client_id = $vendor->client_id;
        $total_invoices = Invoice::where(['client_id' => $client_id, 'vendor_id' => $vendor_id])->count();
        $pending_invoices = Invoice::where(['client_id' => $client_id, 'vendor_id' => $vendor_id, 'status' => 'Pending'])->count();
        $overdue_invoices = Invoice::where(['client_id' => $client_id, 'vendor_id' => $vendor_id, 'status' => 'Overdue'])->count();
        $paid_invoices = Invoice::where(['client_id' => $client_id, 'vendor_id' => $vendor_id, 'status' => 'Paid'])->count();
        return response()->json(compact('total_invoices', 'pending_invoices', 'overdue_invoices', 'paid_invoices'), 200);
    }

    public function vendorRiskAssessmentAnalysis(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::find($vendor_id);
        $client_id = $vendor->client_id;
        // $responses = DueDiligenceResponse::
    }
}
