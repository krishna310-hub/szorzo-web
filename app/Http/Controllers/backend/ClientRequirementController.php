<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientRequirementController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.client-requirements.index');
    }

    public function create()
    {
        return view('backend.client-requirements.create');
    }

    public function edit()
    {
        return view('backend.client-requirements.edit');
    }
}
