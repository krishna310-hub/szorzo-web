<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ModeController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.modes.index');
    }

    public function create()
    {
        return view('backend.modes.create');
    }

    public function edit()
    {
        return view('backend.modes.edit');
    }
}
