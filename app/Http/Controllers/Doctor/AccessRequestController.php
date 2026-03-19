<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index()                             { return back()->with('info', 'Access requests feature coming soon.'); }
    public function store(Request $request)             { return back()->with('info', 'Access requests feature coming soon.'); }
    public function approve($request)                   { return back()->with('info', 'Access requests feature coming soon.'); }
    public function deny($request)                      { return back()->with('info', 'Access requests feature coming soon.'); }
}
