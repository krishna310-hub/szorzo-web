<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class JobRoleController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.job-roles.index');
    }

    public function create()
    {
        return view('backend.job-roles.create');
    }

    public function edit()
    {
        return view('backend.job-roles.edit');
    }
}
