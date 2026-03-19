<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()                    { return back()->with('info', 'Notifications feature coming soon.'); }
    public function markRead($id)              { return response()->json(['ok' => true]); }
    public function markAllRead()              { return response()->json(['ok' => true]); }
    public function destroy($id)               { return response()->json(['ok' => true]); }
    public function unreadCount()              { return response()->json(['count' => 0]); }
}
