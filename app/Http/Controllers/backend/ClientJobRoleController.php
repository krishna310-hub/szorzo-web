<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientJobRoleController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.client-job-roles.index');
    }

    public function create()
    {
        return view('backend.client-job-roles.create');
    }

    public function edit()
    {
        return view('backend.client-job-roles.edit');
    }
}
