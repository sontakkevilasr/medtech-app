<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans()                       { return back()->with('info', 'Subscription feature coming soon.'); }
    public function checkout(Request $request, $plan) { return back()->with('info', 'Subscription feature coming soon.'); }
    public function success()                     { return back()->with('info', 'Subscription feature coming soon.'); }
    public function history()                     { return back()->with('info', 'Subscription feature coming soon.'); }
}
