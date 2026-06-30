<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.candidates.index');
    }

    public function create()
    {
        return view('backend.candidates.create');
    }

    public function edit()
    {
        return view('backend.candidates.edit');
    }
}
