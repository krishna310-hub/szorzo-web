<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffered;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ServiceOfferedController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ServiceOffered::latest())
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => $row->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Inactive</span>')
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    $buttons .= '<a href="'.route('admin.services-offered.edit', $row->id).'" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>';
                    $buttons .= '<button type="button" data-route="'.route('admin.services-offered.delete', $row->id).'" class="btn btn-link text-danger fs-4 p-0 ms-1 delete-record" title="Delete"><i class="bx bxs-trash"></i></button>';
                    return $buttons ?: '-';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.services-offered.index');
    }

    public function create()
    {
        return view('backend.services-offered.create');
    }

    public function store(Request $request)
    {
        ServiceOffered::create($this->validatedData($request));

        return redirect()->route('admin.services-offered.index')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        return view('backend.services-offered.edit', [
            'model' => ServiceOffered::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        ServiceOffered::findOrFail($id)->update($this->validatedData($request));

        return redirect()->route('admin.services-offered.index')->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        ServiceOffered::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Record deleted successfully.']);
    }

    public function export()
    {
        $data = \App\Models\ServiceOffered::all()->map(function ($row) {
            return collect($row->toArray())->only(['id', 'status', 'created_at'])->merge([
                'status' => $row->status ? 'Active' : 'Inactive'
            ])->values()->toArray();
        });
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Service Name',
  1 => 'Service Code',
  2 => 'Category',
  3 => 'Description',
  4 => 'Status',
), $data->toArray()), 'ServiceOffered-export.xlsx');
    }

    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(array (
  0 => 'Service Name',
  1 => 'Service Code',
  2 => 'Category',
  3 => 'Description',
  4 => 'Status',
), []), 'ServiceOffered-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        $rows = \App\Support\MasterDataSpreadsheet::rows($request->file('import_file'));
        
        if ($rows->isEmpty()) return back()->with('error', 'Empty file');
        
        $validRows = [];
        foreach ($rows as $row) {
            $validRows[] = [
                'service_name' => $row['service_name'] ?? '',
                'service_code' => $row['service_code'] ?? null,
                'category' => $row['category'] ?? null,
                'description' => $row['description'] ?? null,
                'status' => strtolower($row['status'] ?? '') === 'active' ? 1 : 0,
            ];
        }
        
        foreach ($validRows as $data) {
            \App\Models\ServiceOffered::create($data);
        }
        
        return back()->with('success', 'Imported successfully');
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'service_name' => 'required|string|max:255',
            'service_code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);
    }
}
