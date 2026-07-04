<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', Role::class);
        $roles = Role::where('id', '!=', 1)->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::eloquent($roles)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-success-subtle text-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function ($row) {

                    $buttons = '';

                    if (auth()->user()->can('edit', Role::class)) {
                        $editUrl = route('admin.role.edit', $row->id);
                        $buttons .= '
                            <a href="' . $editUrl . '" class="text-info fs-4 me-1" title="Edit">
                                <i class="bx bxs-edit"></i>
                            </a>
                            
                        ';
                    }

                    if (auth()->user()->can('delete', Role::class)) {
                        $buttons .= '
                            <a href="javascript:void(0);" class="text-danger fs-4 ms-1 destroy-ajax" title="Delete"
                            data-id="'.$row->id.'" data-table-id="role-table"
                                data-route="'.route('admin.role.destroy', $row->id).'"
                                >
                                <i class="bx bxs-trash"></i>
                            </a>
                        ';
                    }

                    return $buttons ?: '-';
                })
                
                ->rawColumns(['action','status'])
                ->make(true);
        }

        return view('backend.roles.index',compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);
        $permissions = Permission::get()->groupBy('page');
        return view('backend.roles.create',compact('permissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);
        $request->validate([
            'role_name' => 'required|string|max:100',
            'status' => 'required|in:0,1',
        ]);
        $data['name']           = $request->role_name;
        $data['status']         = $request->status;
        $data['access_level']   = Str::slug($request->role_name) ?? '';

        $role_exists = Role::where('access_level', $data['access_level'])->count();

        if ($role_exists) {
            return redirect()->route('admin.role.create')->with('error' , "" . $request->role_name . 'Role already exists.');
        }

        $role = Role::create($data);

        $role->permissions()->sync($request->permissions);

        return redirect()->route('admin.role.index')->with('success', ''.$request->role_name.'Role created successfully.');
    }
    public function edit($id)
    {
        $this->authorize('edit', Role::class);

        $role        = Role::find($id); 
        if($role){
            $permissions = Permission::get()->groupBy('page');
    
            return view('backend.roles.edit', compact('permissions', 'role'));
        }else{
            return redirect()->route('admin.role.index')->with('error', 'Role not found.');
        }
    }
    public function update(Request $request, $id)
    {
        $this->authorize('edit', Role::class);
        $request->validate([
            'role_name' => 'required|string|max:100',
            'status' => 'required|in:0,1',
        ]);
        $role = Role::where('id', $id)->first();

        $role->update([
            'name' => $request->role_name,
            'status' => $request->status,
            'access_level' => Str::slug($request->role_name) ?? ''
        ]);

        if (array_key_exists('permissions', $request->all())) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->sync([]);
        }

        return redirect()->route('admin.role.index')->with(["success" => "".$request->role_name." Role updated successfully", "alert-type" => 'success']);
    }
    public function destroy($id)
    {
        $this->authorize('delete', Role::class);
        $role = Role::findOrFail($id);
        if ($id == 1) {
            return response()->json(['status' => false, 'message' => 'Role cannot be deleted due to dependency',]);
        } else {
            $role->permissions()->detach();
            $role->delete();
            return response()->json(['status' => true, 'message' => 'Role deleted successfully',]);
        }
    }
}
