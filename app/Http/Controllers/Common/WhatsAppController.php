<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function webhook(Request $request) { return response()->json(['status' => 'ok']); }
}
