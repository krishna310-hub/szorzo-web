<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', User::class);
        $roles = User::with('role')->where('id', '!=', 1)->orderBy('id', 'desc');
        if ($request->ajax()) {
            return DataTables::eloquent($roles)
                ->addIndexColumn()
                ->addColumn('access_level', function ($row) {
                    return $row->role && $row->role->access_level ? $row->role->access_level : '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_active == 1) {
                        return '<span class="badge bg-success-subtle text-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    if (auth()->user()->can('edit', User::class)) {
                        $editUrl = route('admin.user.edit', $row->id);
                        $buttons .= '
                            <a href="' . $editUrl . '" class="text-info fs-4 me-1" title="Edit">
                                <i class="bx bxs-edit"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('delete', User::class)) {
                        $buttons .= '
                            <a href="javascript:void(0)" class="text-danger fs-4 ms-1 destroy-ajax" title="Delete"
                            data-id="'.$row->id.'" data-table-id="user-table"
                                data-route="'.route('admin.user.destroy', $row->id).'">
                                <i class="bx bxs-trash"></i>
                            </a>
                        ';
                    }
                    return $buttons ?: '-';
                })
                
                ->rawColumns(['action','access_level','status'])
                ->make(true);
        }
        return view('backend.user.index');
    }
    public function create()
    {
        $this->authorize('create', User::class);
        $roles = Role::where('access_level', '!=', 'super_admin')
             ->where('status', 1)
             ->get();
        return view('backend.user.create',compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $request->validate([
            'role_id'  => 'required|integer',
            'user_name'=> 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'mobile'   => 'required|digits:10'
        ]);
        $role = Role::find($request->role_id);
        if($role){
            $data['resource_type'] = $role->access_level;
        }
        $data['role_id'] = $request['role_id'];
        $data['name'] = $request['user_name'];
        $data['email'] = $request['email'];
        $data['password'] = bcrypt($request['password']);
        $data['phone_number'] = $request['mobile'];
        $data['is_active'] = $request['status'];
        User::create($data);

        return redirect()->route('admin.user.index')->with('success', 'User created successfully.');
    }
    public function edit($id)
    {
        $this->authorize('edit', User::class);
        $user = User::findOrFail($id);
        $roles = Role::where('access_level', '!=', 'super_admin')
             ->where('status', 1)
             ->get();
        return view('backend.user.edit', compact('user','roles'));
    }
    public function update(Request $request, $id)
    {
        $this->authorize('edit', User::class);
        $user = User::findOrFail($id);

        $request->validate([
            'role_id'  => 'required|integer',
            'user_name'=> 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $role = Role::find($request->role_id);
        if($role){
            $data['resource_type'] = $role->access_level;
        }
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        
        $data['role_id'] = $request['role_id'];
        $data['name'] = $request['user_name'];
        $data['email'] = $request['email'];
        $data['phone_number'] = $request['mobile'];
        $data['is_active'] = $request['status'];

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'User updated successfully.');
    }
    public function destroy($id)
    {
        $this->authorize('delete', User::class);
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User deleted successfully.');
    }
    public function show($id)
    {
        $this->authorize('read', User::class);
        $user = User::findOrFail($id);
        return view('backend.user.show', compact('user'));
    }
}
