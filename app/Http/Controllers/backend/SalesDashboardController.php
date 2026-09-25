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
        $salesUser = auth()->user()->isSales();
        $leadQuery = LeadGeneration::query();
        $profileQuery = ClientProfile::query();
        if ($salesUser) {
            $leadQuery->where('assigned_to', auth()->id());
            $profileQuery->where('assigned_to', auth()->id());
        }

        // KPIs
        $totalLeads = (clone $leadQuery)->count();
        $activeTempClients = (clone $profileQuery)->where('status', 1)->where('is_converted_to_client', 0)->count();
        $convertedClients = (clone $profileQuery)->where('is_converted_to_client', 1)->count();
        $totalServices = ServiceOffered::where('status', 1)->count();

        // Monthly Leads Chart (Last 6 Months)
        $monthlyLeads = (clone $leadQuery)->select(
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
        $recentLeads = (clone $leadQuery)->with('assignee')->orderBy('created_at', 'desc')->take(5)->get();

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
