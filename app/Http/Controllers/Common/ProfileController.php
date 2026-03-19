<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()                        { return back()->with('info', 'Profile feature coming soon.'); }
    public function edit()                        { return back()->with('info', 'Profile feature coming soon.'); }
    public function update(Request $request)      { return back()->with('info', 'Profile feature coming soon.'); }
    public function updatePhoto(Request $request) { return back()->with('info', 'Profile feature coming soon.'); }
    public function updateLanguage(Request $request) { return back()->with('info', 'Profile feature coming soon.'); }
    public function updatePassword(Request $request) { return back()->with('info', 'Profile feature coming soon.'); }
    public function destroy()                     { return back()->with('info', 'Profile feature coming soon.'); }
}
