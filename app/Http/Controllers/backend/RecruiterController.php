<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class RecruiterController extends Controller
{
    public function index(Request $request)
    {
        return view('backend.recruiters.index');
    }

    public function create()
    {
        return view('backend.recruiters.create');
    }

    public function edit()
    {
        return view('backend.recruiters.edit');
    }
}
