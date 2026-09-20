<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\BusinessIntelligence;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BusinessIntelligenceController extends Controller
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
                    $buttons .= '<a href="'.route('admin.business-intelligences.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.business-intelligences.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.business-intelligences.index');
    }

    public function create()
    {
        return view('backend.business-intelligences.create');
    }

    public function store(Request $request)
    {
        $model::create($this->validatedData($request));
        return redirect()->route('admin.business-intelligences.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.business-intelligences.edit', [
            'model' => $model::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $model::findOrFail($id)->update($this->validatedData($request));
        return redirect()->route('admin.business-intelligences.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $model::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

        public function export()
    {
        $data = \App\Models\BusinessIntelligence::all()->map(function ($row) {
            return collect($row->toArray())->only(['id', 'status', 'created_at'])->merge([
                'status' => $row->status ? 'Active' : 'Inactive'
            ])->values()->toArray();
        });
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Category',
  1 => 'Metric Name',
  2 => 'Description',
  3 => 'Value Type',
  4 => 'Status',
), $data->toArray()), 'BusinessIntelligence-export.xlsx');
    }

    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Category',
  1 => 'Metric Name',
  2 => 'Description',
  3 => 'Value Type',
  4 => 'Status',
), []), 'BusinessIntelligence-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $rows = \App\Support\MasterDataSpreadsheet::rows($request->file('import_file'));
        
        if ($rows->isEmpty()) return back()->with('error', 'Empty file');
        
        $validRows = [];
        foreach ($rows as $row) {
            $validRows[] = [
                'category' => $row['category'] ?? null,
                'metric_name' => $row['metric_name'] ?? '',
                'description' => $row['description'] ?? null,
                'value_type' => $row['value_type'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0,
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\BusinessIntelligence::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'category' => 'nullable|string|max:255',
            'metric_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value_type' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);
    }
}