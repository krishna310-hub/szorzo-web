<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\LeadGeneration;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeadGenerationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of($model::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    $buttons .= '<a href="'.route('admin.lead-generations.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.lead-generations.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.lead-generations.index');
    }

    public function create()
    {
        return view('backend.lead-generations.create');
    }

    public function store(Request $request)
    {
        $model::create($this->validatedData($request));
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.lead-generations.edit', [
            'model' => $model::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $model::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.lead-generations.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $model::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

        public function export()
    {
        $data = \App\Models\LeadGeneration::all()->map(function ($row) {
            return collect($row->toArray())->only(['id', 'status', 'created_at'])->merge([
                'status' => $row->status ? 'Active' : 'Inactive'
            ])->values()->toArray();
        });
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Title',
  1 => 'Lead Source',
  2 => 'Lead Owner',
  3 => 'Status',
), $data->toArray()), 'LeadGeneration-export.xlsx');
    }

    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Title',
  1 => 'Lead Source',
  2 => 'Lead Owner',
  3 => 'Status',
), []), 'LeadGeneration-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $rows = \App\Support\MasterDataSpreadsheet::rows($request->file('import_file'));
        
        if ($rows->isEmpty()) return back()->with('error', 'Empty file');
        
        $validRows = [];
        foreach ($rows as $row) {
            $validRows[] = [
                'title' => $row['title'] ?? '',
                'lead_source' => $row['lead_source'] ?? null,
                'lead_owner' => $row['lead_owner'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0,
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\LeadGeneration::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'lead_owner' => 'nullable|string|max:255',
            'relationship_manager' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string',
            'client_profile_id' => 'nullable|exists:client_profiles,id',
            'service_id' => 'nullable|exists:service_offereds,id',
            'lead_date' => 'nullable|date',
            'follow_up_date' => 'nullable|date|after_or_equal:lead_date',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);
    }
}