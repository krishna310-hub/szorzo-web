<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadGeneration;
use App\Models\ClientProfile;
use App\Models\ServiceOffered;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesDashboardController extends Controller
{
    public function index(Request $request)
    {
        // KPIs
        $totalLeads = LeadGeneration::count();
        $activeTempClients = ClientProfile::where('status', 1)->where('is_converted_to_client', 0)->count();
        $convertedClients = ClientProfile::where('is_converted_to_client', 1)->count();
        $totalServices = ServiceOffered::where('status', 1)->count();

        // Monthly Leads Chart (Last 6 Months)
        $monthlyLeads = LeadGeneration::select(
            DB::raw('count(id) as count'),
            DB::raw("DATE_FORMAT(created_at, '%b') as month_name"),
            DB::raw("MONTH(created_at) as month")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month_name', 'month')
        ->orderBy('month')
        ->get();

        $chartMonths = [];
        $chartLeadCounts = [];
        foreach($monthlyLeads as $ml) {
            $chartMonths[] = $ml->month_name;
            $chartLeadCounts[] = $ml->count;
        }

        // Conversion Rate (Donut Chart)
        $conversionRate = [
            'Pending' => $activeTempClients,
            'Converted' => $convertedClients
        ];

        // Recent Leads Table
        $recentLeads = LeadGeneration::orderBy('created_at', 'desc')->take(5)->get();

        return view('backend.sales-dashboard.index', compact(
            'totalLeads', 
            'activeTempClients', 
            'convertedClients', 
            'totalServices',
            'chartMonths',
            'chartLeadCounts',
            'conversionRate',
            'recentLeads'
        ));
    }
}
